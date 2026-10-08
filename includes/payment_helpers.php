<?php
/**
 * Logique de paiement RCR (adhésion, cotisation, don) — compatible avec les
 * fichiers existants de adhere/ (mêmes noms de fonctions publiques).
 *
 * RÈGLES
 *  - Un paiement n'est "paid" QUE si FlexPay le confirme côté serveur
 *    (flexpay_check_order) avec la bonne référence, le bon montant et la bonne devise.
 *    Ni success.php, ni le contenu brut du callback ne suffisent.
 *  - Statuts : pending, processing, paid, failed, cancelled, expired.
 *    "expired" = délai d'attente du navigateur dépassé : reste OUVERT à une
 *    confirmation tardive (c'était le bug "débité mais refusé").
 *    "cancelled" = annulation explicite, définitive.
 *  - Toute transition est une mise à jour conditionnelle (anti-doublon/rejeu).
 *  - Types : payments.type_transaction = adhesion | cotisation ; dons = don.
 */

require_once __DIR__ . '/flexpay_client.php';
require_once __DIR__ . '/notifications.php';

const PAYMENT_OPEN_STATUSES = ['pending', 'processing', 'expired'];

if (!function_exists('payment_log')) {
    function payment_log(PDO $bdd, string $type, ?int $refId, string $reference, string $event, ?string $before = null, ?string $after = null, $payload = null): void
    {
        try {
            $s = $bdd->prepare('INSERT INTO payment_logs (type_transaction, ref_id, reference, evenement, statut_avant, statut_apres, payload, ip) VALUES (?,?,?,?,?,?,?,?)');
            $s->execute([
                in_array($type, ['adhesion', 'cotisation', 'don'], true) ? $type : 'adhesion',
                $refId, mb_substr($reference, 0, 100), $event, $before, $after,
                $payload === null ? null : mb_substr(is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_UNICODE), 0, 4000),
                $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
        } catch (Throwable $e) {
            error_log('[payment_log] ' . $e->getMessage());
        }
    }
}

if (!function_exists('payment_new_reference')) {
    /** Référence unique non devinable, ex. RCR-A-260930-9F3A1C2B7D. $kind : A adhésion, C cotisation, D don. */
    function payment_new_reference(string $kind): string
    {
        return sprintf('RCR-%s-%s-%s', strtoupper(substr($kind, 0, 1)), date('ymd'), strtoupper(bin2hex(random_bytes(5))));
    }
}

if (!function_exists('payment_transition_status')) {
    function payment_transition_status(PDO $bdd, string $table, string $idColumn, int $id, array $allowedFrom, string $newStatus): bool
    {
        if (empty($allowedFrom) || !in_array($table, ['payments', 'dons'], true)) {
            return false;
        }
        $in = implode(',', array_fill(0, count($allowedFrom), '?'));
        $stmt = $bdd->prepare("UPDATE `{$table}` SET status = ? WHERE `{$idColumn}` = ? AND status IN ({$in})");
        $stmt->execute(array_merge([$newStatus, $id], $allowedFrom));
        return $stmt->rowCount() > 0;
    }
}

if (!function_exists('payment_get_row')) {
    /** $type : 'don' pour la table dons, sinon payments. Retourne null si introuvable. */
    function payment_get_row(PDO $bdd, string $type, int $id): ?array
    {
        if ($type === 'don') {
            $stmt = $bdd->prepare("SELECT id_don AS id, 'don' AS type_transaction, reference, transaction_id, order_number, status, montant, devise, canal, created_at FROM dons WHERE id_don = ?");
        } else {
            $stmt = $bdd->prepare("SELECT id, id_ad, type_transaction, reference, transaction_id, order_number, status, montant, devise, canal, created_at FROM payments WHERE id = ?");
        }
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

if (!function_exists('payment_find_by_reference')) {
    /** @return array{table:string, type:string, row:array}|null */
    function payment_find_by_reference(PDO $bdd, string $reference): ?array
    {
        $s = $bdd->prepare('SELECT id, id_ad, type_transaction, reference, order_number, transaction_id, status, montant, devise, created_at FROM payments WHERE reference = ? LIMIT 1');
        $s->execute([$reference]);
        if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
            return ['table' => 'payments', 'type' => 'payment', 'row' => $r];
        }
        $s = $bdd->prepare("SELECT id_don AS id, 'don' AS type_transaction, reference, order_number, transaction_id, status, montant, devise, created_at FROM dons WHERE reference = ? LIMIT 1");
        $s->execute([$reference]);
        if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
            return ['table' => 'dons', 'type' => 'don', 'row' => $r];
        }
        return null;
    }
}

if (!function_exists('payment_find_by_order')) {
    /** Retrouve une transaction par son numéro de commande FlexPay (repli du callback si la référence est inconnue). */
    function payment_find_by_order(PDO $bdd, string $order): ?array
    {
        if (!preg_match('/^[A-Za-z0-9]{10,60}$/', $order)) {
            return null;
        }
        $s = $bdd->prepare('SELECT reference FROM payments WHERE order_number = ? LIMIT 1');
        $s->execute([$order]);
        $ref = $s->fetchColumn();
        if (!$ref) {
            $s = $bdd->prepare('SELECT reference FROM dons WHERE order_number = ? LIMIT 1');
            $s->execute([$order]);
            $ref = $s->fetchColumn();
        }
        return $ref ? payment_find_by_reference($bdd, (string) $ref) : null;
    }
}

if (!function_exists('payment_set_order_number')) {
    /** Enregistre le numéro de commande FlexPay (transaction_id conservé pour compatibilité). */
    function payment_set_order_number(PDO $bdd, string $type, int $id, string $orderNumber, string $newStatus = 'processing'): void
    {
        $table = $type === 'don' ? 'dons' : 'payments';
        $pk    = $type === 'don' ? 'id_don' : 'id';
        $s = $bdd->prepare("UPDATE `{$table}` SET order_number = ?, transaction_id = ?, status = IF(status = 'pending', ?, status) WHERE `{$pk}` = ?");
        $s->execute([$orderNumber, $orderNumber, $newStatus, $id]);
    }
}

if (!function_exists('payment_sync_trans_keys')) {
    function payment_sync_trans_keys(PDO $bdd, int $paymentId): void
    {
        $bdd->prepare('UPDATE payments SET trans_keys = 1 WHERE id = ? AND trans_keys = 0')->execute([$paymentId]);
    }
}

if (!function_exists('payment_activate_membership')) {
    /**
     * Après un paiement d'adhésion/cotisation CONFIRMÉ : active le membre et
     * calcule la période couverte. Idempotent (periode_fin IS NULL ⇒ une seule fois).
     */
    function payment_activate_membership(PDO $bdd, int $paymentId): void
    {
        $s = $bdd->prepare('SELECT p.id, p.id_ad, p.id_cot FROM payments p WHERE p.id = ? AND p.periode_fin IS NULL AND p.status = \'paid\'');
        $s->execute([$paymentId]);
        $p = $s->fetch(PDO::FETCH_ASSOC);
        if (!$p || empty($p['id_ad'])) {
            return;
        }
        $c = $bdd->prepare('SELECT mois FROM cotisation WHERE id_cot = ?');
        $c->execute([(int) $p['id_cot']]);
        $mois = (int) $c->fetchColumn();
        if ($mois < 1) {
            $mois = 1;
        }
        $m = $bdd->prepare('SELECT date_echeance FROM adhesion WHERE id_ad = ?');
        $m->execute([(int) $p['id_ad']]);
        $ech = $m->fetchColumn();

        $today = new DateTimeImmutable('today');
        $debut = ($ech && new DateTimeImmutable($ech) > $today) ? new DateTimeImmutable($ech) : $today; // renouvellement anticipé : on prolonge
        $fin   = $debut->modify("+{$mois} months");

        $own = !$bdd->inTransaction(); // appelée depuis payment_finalize (transaction déjà ouverte) ou seule
        if ($own) { $bdd->beginTransaction(); }
        try {
            $bdd->prepare('UPDATE payments SET periode_debut = ?, periode_fin = ? WHERE id = ? AND periode_fin IS NULL')
                ->execute([$debut->format('Y-m-d'), $fin->format('Y-m-d'), $paymentId]);
            $bdd->prepare("UPDATE adhesion SET statut = 'actif', date_echeance = ?, reglement = ? WHERE id_ad = ?")
                ->execute([$fin->format('Y-m-d'), (int) $p['id_cot'], (int) $p['id_ad']]);
            if ($own) { $bdd->commit(); }
        } catch (Throwable $e) {
            if ($own && $bdd->inTransaction()) { $bdd->rollBack(); }
            error_log('[payment_activate_membership] ' . $e->getMessage());
            if (!$own) { throw $e; } // la transaction englobante (finalize) doit être annulée : jamais « payé » sans membre activé
        }
    }
}

if (!function_exists('payment_finalize')) {
    /**
     * finalizePayment — IDEMPOTENT. Seule porte d'entrée vers le statut « paid ».
     * Dans UNE transaction MySQL, ligne verrouillée (SELECT … FOR UPDATE) :
     *   statut -> paid, membre activé + période calculée (adhésion/cotisation) ou échéance de don.
     * Deux callbacks / pollings simultanés : le second attend le verrou, voit « paid » et ne fait rien.
     * Toute erreur annule tout (jamais « paid » sans adhésion validée). Notifications APRÈS commit.
     * @return bool true si CET appel a finalisé le paiement, false s'il l'était déjà ou en cas d'échec.
     */
    function payment_finalize(PDO $bdd, array $found): bool
    {
        $table = $found['table'];
        $pk    = $table === 'dons' ? 'id_don' : 'id';
        $id    = (int) $found['row']['id'];
        $from  = '';
        try {
            $bdd->beginTransaction();
            $q = $bdd->prepare("SELECT status FROM `{$table}` WHERE `{$pk}` = ? FOR UPDATE");
            $q->execute([$id]);
            $from = (string) $q->fetchColumn();
            if ($from === '' || $from === 'paid') {
                $bdd->rollBack();
                return false;
            }
            // Un paiement confirmé par FlexPay l'emporte sur tout statut non payé (y compris failed/cancelled/expired).
            $bdd->prepare("UPDATE `{$table}` SET status = 'paid', verifie_le = NOW() WHERE `{$pk}` = ?")->execute([$id]);
            if ($table === 'payments') {
                $bdd->prepare('UPDATE payments SET trans_keys = 1 WHERE id = ? AND trans_keys = 0')->execute([$id]);
                payment_activate_membership($bdd, $id); // lève en cas d'erreur -> rollback ci-dessous
            }
            $bdd->commit();
        } catch (Throwable $e) {
            if ($bdd->inTransaction()) { $bdd->rollBack(); }
            error_log('[payment_finalize] ' . $e->getMessage());
            payment_log($bdd, $found['row']['type_transaction'] ?? 'don', $id, (string) $found['row']['reference'], 'finalize_error', $from ?: null, null, $e->getMessage());
            return false;
        }
        payment_log($bdd, $found['row']['type_transaction'] ?? 'don', $id, (string) $found['row']['reference'], 'status_change', $from, 'paid', ['verified' => true]);
        try {
            if ($table === 'payments' && !empty($found['row']['id_ad'])) {
                $ev = (($found['row']['type_transaction'] ?? 'adhesion') === 'cotisation') ? 'paiement_confirme' : 'adhesion_confirmee';
                notify_member($bdd, (int) $found['row']['id_ad'], $ev, ['reference' => $found['row']['reference'], 'montant' => $found['row']['montant'], 'devise' => $found['row']['devise']], 'pay:' . $found['row']['reference']);
            }
            if ($table === 'dons') {
                payment_don_planifier_suite($bdd, $id);
            }
        } catch (Throwable $e) {
            error_log('[payment_finalize/notify] ' . $e->getMessage()); // le paiement reste valide
        }
        return true;
    }
}

if (!function_exists('payment_apply_status')) {
    /**
     * Applique un statut. 'paid' n'est accepté que si $verified = true (confirmé par FlexPay) et passe par payment_finalize().
     * Retourne true si une ligne a changé.
     */
    function payment_apply_status(PDO $bdd, array $found, string $newStatus, bool $verified): bool
    {
        $table = $found['table'];
        $pk    = $table === 'dons' ? 'id_don' : 'id';
        $id    = (int) $found['row']['id'];
        $from  = $found['row']['status'];

        if ($newStatus === 'paid') {
            return $verified ? payment_finalize($bdd, $found) : false;
        }
        $allowed = $newStatus === 'failed' ? ['pending', 'processing', 'expired'] : PAYMENT_OPEN_STATUSES;
        $changed = payment_transition_status($bdd, $table, $pk, $id, $allowed, $newStatus);
        if ($changed) {
            $bdd->prepare("UPDATE `{$table}` SET verifie_le = NOW() WHERE `{$pk}` = ?")->execute([$id]);
            payment_log($bdd, $found['row']['type_transaction'] ?? 'don', $id, $found['row']['reference'], 'status_change', $from, $newStatus, ['verified' => $verified]);
        }
        return $changed;
    }
}

if (!function_exists('payment_track')) {
    /** Suivi de diagnostic (dernière vérif, statut FlexPay brut, nb de vérifs, dernier callback, dernière erreur). Tolère l'absence des colonnes (migration non passée). */
    function payment_track(PDO $bdd, array $found, array $set): void
    {
        $t  = $found['table'];
        $pk = $t === 'dons' ? 'id_don' : 'id';
        $cols = [];
        $par  = [];
        if (isset($set['check'])) {
            $cols[] = 'derniere_verif = NOW()'; $cols[] = 'nb_verifs = nb_verifs + 1';
            $cols[] = 'flexpay_status = ?'; $par[] = $set['flexpay_status'] === null ? null : mb_substr((string) $set['flexpay_status'], 0, 20);
            $cols[] = 'derniere_erreur = ?'; $par[] = ($set['error'] ?? '') === '' ? null : mb_substr((string) $set['error'], 0, 250);
        }
        if (isset($set['callback'])) { $cols[] = 'dernier_callback = NOW()'; }
        if (!$cols) { return; }
        try {
            $par[] = (int) $found['row']['id'];
            $bdd->prepare("UPDATE `{$t}` SET " . implode(', ', $cols) . " WHERE `{$pk}` = ?")->execute($par);
        } catch (Throwable $e) {
            error_log('[payment_track] ' . $e->getMessage() . ' (migration phase7 appliquée ?)');
        }
    }
}

if (!function_exists('verifyFlexPayTransaction')) {
    /**
     * Vérification serveur → serveur d'une transaction (jeton Bearer côté serveur uniquement).
     * Ne modifie AUCUN statut. state :
     *   PAID      status FlexPay "0"
     *   FAILED    status FlexPay "1" (n'a pas abouti — l'appelant applique un délai de grâce)
     *   PENDING   autre valeur (ex. "4" observé) : non concluant, on continue d'attendre
     *   NOT_FOUND FlexPay ne connaît pas ce numéro de commande
     *   ERROR     réseau / HTTP / JSON illisible / non configuré : on ne décide RIEN
     * @return array{state:string, flexpay_status:?string, order:?string, reference:?string, amount:?float, currency:?string, http:int, message:string, error:string}
     */
    function verifyFlexPayTransaction(string $orderNumber): array
    {
        $out = ['state' => 'ERROR', 'flexpay_status' => null, 'order' => null, 'reference' => null, 'amount' => null, 'currency' => null, 'http' => 0, 'message' => '', 'error' => ''];
        try {
            $c = flexpay_check_order($orderNumber);
        } catch (Throwable $e) {
            $out['error'] = 'exception: ' . $e->getMessage();
            return $out;
        }
        if ($c === null) {
            $out['error'] = 'FlexPay injoignable ou réponse non JSON';
            return $out;
        }
        $out = array_merge($out, ['order' => $c['order'] ?? null, 'reference' => $c['reference'] ?? null, 'amount' => $c['amount'] ?? null, 'currency' => $c['currency'] ?? null, 'http' => (int) ($c['http'] ?? 0), 'message' => (string) ($c['message'] ?? ''), 'flexpay_status' => $c['status'] ?? null]);
        if (empty($c['found'])) {
            $out['state'] = 'NOT_FOUND';
            $out['error'] = (string) ($c['error'] ?? '');
            return $out;
        }
        $out['state'] = ($c['status'] ?? null) === '0' ? 'PAID' : (($c['status'] ?? null) === '1' ? 'FAILED' : 'PENDING');
        return $out;
    }
}

if (!function_exists('payment_verify_and_confirm')) {
    /**
     * Interroge FlexPay et met à jour le statut local.
     * @return string|null 'paid' | 'failed' | null (inchangé : pas de numéro de commande, FlexPay injoignable,
     *                      transaction encore en cours, ou données incohérentes → jamais de validation par défaut)
     */
    function payment_verify_and_confirm(PDO $bdd, array $found): ?string
    {
        $row   = $found['row'];
        $type  = $row['type_transaction'] ?? 'don';
        $order = (string) ($row['order_number'] ?: $row['transaction_id']);
        if ($order === '') {
            return null;
        }
        $v = verifyFlexPayTransaction($order);
        payment_track($bdd, $found, ['check' => true, 'flexpay_status' => $v['flexpay_status'], 'error' => $v['state'] === 'ERROR' ? $v['error'] : '']);
        payment_log($bdd, $type, (int) $row['id'], $row['reference'], 'check', $row['status'], null, ['state' => $v['state'], 'flexpay_status' => $v['flexpay_status'], 'http' => $v['http'], 'error' => $v['error']]);

        if ($v['state'] === 'ERROR' || $v['state'] === 'NOT_FOUND') {
            return null;
        }
        // Cohérence : FlexPay renvoie dans transaction.reference le numéro de commande (et non la référence RCR).
        // On accepte les deux, on refuse tout ce qui ne correspond à NI l'un NI l'autre.
        if ($v['reference'] !== null && $v['reference'] !== $row['reference'] && $v['reference'] !== $order) {
            payment_log($bdd, $type, (int) $row['id'], $row['reference'], 'check_reference_mismatch', $row['status'], null, $v);
            return null;
        }
        if ($v['order'] !== null && $v['order'] !== $order) {
            payment_log($bdd, $type, (int) $row['id'], $row['reference'], 'check_order_mismatch', $row['status'], null, $v);
            return null;
        }
        if ($v['state'] === 'PAID') {
            $okAmount   = $v['amount'] !== null && abs($v['amount'] - (float) $row['montant']) <= 0.01;
            $okCurrency = $v['currency'] === null || $v['currency'] === strtoupper((string) $row['devise']);
            if (!$okAmount || !$okCurrency) {
                payment_log($bdd, $type, (int) $row['id'], $row['reference'], 'check_amount_mismatch', $row['status'], null, ['attendu' => $row['montant'] . ' ' . $row['devise'], 'flexpay' => $v]);
                return null; // argent encaissé mais montant différent : décision humaine (page admin), jamais auto-validé
            }
            payment_apply_status($bdd, $found, 'paid', true);
            return 'paid';
        }
        if ($v['state'] === 'FAILED') {
            // "1" = pas abouti. On n'échoue qu'après 3 min (le client peut encore valider le push).
            // Âge calculé PAR MySQL (même horloge que created_at).
            $t = $found['table'];
            $pk = $t === 'dons' ? 'id_don' : 'id';
            $a = $bdd->prepare("SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) FROM `{$t}` WHERE `{$pk}` = ?");
            $a->execute([(int) $row['id']]);
            if ((int) $a->fetchColumn() >= 180) {
                payment_apply_status($bdd, $found, 'failed', true);
                return 'failed';
            }
        }
        return null;
    }
}

/* ---------- Compatibilité avec l'existant (adhere/*.php) ---------- */

if (!function_exists('payment_mark_status_by_reference')) {
    /** Conservé pour compatibilité : ne valide JAMAIS 'paid' sans vérification FlexPay. */
    function payment_mark_status_by_reference(PDO $bdd, string $reference, string $newStatus, ?int $expectedId = null): ?string
    {
        $found = payment_find_by_reference($bdd, $reference);
        if (!$found || ($expectedId !== null && (int) $found['row']['id'] !== $expectedId)) {
            return null;
        }
        if ($newStatus === 'paid') {
            return payment_verify_and_confirm($bdd, $found) === 'paid' ? $found['table'] : null;
        }
        return payment_apply_status($bdd, $found, $newStatus, false) ? $found['table'] : null;
    }
}

if (!function_exists('payment_expire_pending')) {
    /** Fin du délai d'attente navigateur : passe à 'expired' (reste ouvert à une confirmation tardive). */
    function payment_expire_pending(PDO $bdd, string $type, int $id): bool
    {
        $table = $type === 'don' ? 'dons' : 'payments';
        $pk    = $type === 'don' ? 'id_don' : 'id';
        return payment_transition_status($bdd, $table, $pk, $id, ['pending', 'processing'], 'expired');
    }
}

if (!function_exists('payment_flexpay_check')) {
    /** Vérification active (polling / expiration). Retourne 'paid' | 'failed' | null. */
    function payment_flexpay_check(PDO $bdd, string $type, int $id): ?string
    {
        $row = payment_get_row($bdd, $type, $id);
        if (!$row || !in_array($row['status'], PAYMENT_OPEN_STATUSES, true)) {
            return null;
        }
        $found = payment_find_by_reference($bdd, (string) $row['reference']);
        return $found ? payment_verify_and_confirm($bdd, $found) : null;
    }
}

/* ---------- Création des transactions (Phase 2) ---------- */

if (!function_exists('payment_create')) {
    /**
     * Crée une transaction d'adhésion/cotisation (table payments). Le montant doit venir de tarifs_calculer().
     * Retourne [id, reference].
     */
    function payment_create(PDO $bdd, array $membre, array $tarif, string $type, string $canal, ?string $telephone): array
    {
        $type      = $type === 'cotisation' ? 'cotisation' : 'adhesion';
        $canal     = $canal === 'carte' ? 'carte' : 'mobile_money';
        $reference = payment_new_reference($type === 'cotisation' ? 'C' : 'A');
        $s = $bdd->prepare("INSERT INTO payments (adhesion_id, id_ad, codes_ad, reference, reference_payment, telephone_py, montant, devise, currency, provider, status, type_transaction, id_cot, canal)
                            VALUES (?,?,?,?,?,?,?,?,?, 'FLEXPAY', 'pending', ?, ?, ?)");
        $s->execute([(int) $membre['id_ad'], (int) $membre['id_ad'], $membre['codes'], $reference, $reference, $telephone,
            $tarif['montant'], $tarif['devise'], $tarif['devise'], $type, $tarif['id_cot'], $canal]);
        $id = (int) $bdd->lastInsertId();
        payment_log($bdd, $type, $id, $reference, 'created', null, 'pending', ['montant' => $tarif['montant'], 'canal' => $canal]);
        return [$id, $reference];
    }
}

if (!function_exists('don_create')) {
    /** Crée un don (table dons). $d : montant, devise, type_don, frequence, canal, telephone + identité. Retourne [id, reference]. */
    function don_create(PDO $bdd, array $d): array
    {
        $reference = payment_new_reference('D');
        $s = $bdd->prepare("INSERT INTO dons (nom_donateur, postnom, prenom, sexe, nationalite, province, territoire, ville, adresse, telephone, email, montant, devise, provider, reference, status, ip_donateur, type_don, frequence, id_ad, canal)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?, 'FLEXPAY', ?, 'pending', ?, ?, ?, ?, ?)");
        $s->execute([$d['nom'], $d['postnom'], $d['prenom'], $d['sexe'] ?: null, $d['nationalite'], $d['province'] ?: null, $d['territoire'] ?: null,
            $d['ville'], $d['adresse'], $d['telephone'], $d['email'], $d['montant'], $d['devise'], $reference, $_SERVER['REMOTE_ADDR'] ?? '',
            $d['type_don'], $d['type_don'] === 'regulier' ? $d['frequence'] : null, $d['id_ad'] ?: null, $d['canal']]);
        $id = (int) $bdd->lastInsertId();
        payment_log($bdd, 'don', $id, $reference, 'created', null, 'pending', ['montant' => $d['montant'], 'type' => $d['type_don']]);
        return [$id, $reference];
    }
}

if (!function_exists('payment_start_flexpay')) {
    /**
     * Lance le paiement chez FlexPay pour une transaction déjà créée.
     * @return array{ok:bool, redirect:?string, message:string}  redirect = URL carte, ou null pour Mobile Money (attendre le push).
     */
    function payment_start_flexpay(PDO $bdd, string $type, int $id, string $reference, float $montant, string $devise, string $canal, ?string $telephone, string $baseUrl): array
    {
        $kind = $type === 'don' ? 'don' : 'payment';
        $q    = 'id=' . $id . '&type=' . ($type === 'don' ? 'don' : 'adhesion');
        if ($canal === 'carte') {
            $r = flexpay_request_card($reference, $montant, $devise, 'RCR ' . $type . ' ' . $reference,
                $baseUrl . '/adhere/success.php?' . $q, $baseUrl . '/adhere/failed.php?' . $q . '&c=1', $baseUrl . '/adhere/failed.php?' . $q);
        } else {
            $r = flexpay_request_mobile($reference, (string) $telephone, $montant, $devise);
        }
        if (!$r['ok']) {
            payment_log($bdd, $type, $id, $reference, 'flexpay_request_failed', 'pending', null, $r['message']);
            return ['ok' => false, 'redirect' => null, 'message' => $r['message']];
        }
        payment_set_order_number($bdd, $kind, $id, (string) $r['orderNumber']);
        payment_log($bdd, $type, $id, $reference, 'flexpay_request_ok', 'pending', 'processing', ['order' => $r['orderNumber']]);
        return ['ok' => true, 'redirect' => $canal === 'carte' ? $r['url'] : null, 'message' => $r['message']];
    }
}


if (!function_exists('payment_don_planifier_suite')) {
    /** Don confirmé : calcule la prochaine échéance d'un don régulier et met en file le reçu (si un compte membre est lié). */
    function payment_don_planifier_suite(PDO $bdd, int $idDon): void
    {
        try {
            $s = $bdd->prepare('SELECT type_don, frequence, id_ad, reference, montant, devise FROM dons WHERE id_don = ?');
            $s->execute([$idDon]);
            $d = $s->fetch(PDO::FETCH_ASSOC);
            if (!$d) {
                return;
            }
            if ($d['type_don'] === 'regulier' && $d['frequence']) {
                $mois = ['mensuel' => 1, 'trimestriel' => 3, 'semestriel' => 6, 'annuel' => 12][$d['frequence']] ?? 1;
                $bdd->prepare('UPDATE dons SET prochaine_echeance = DATE_ADD(CURDATE(), INTERVAL ? MONTH) WHERE id_don = ?')->execute([$mois, $idDon]);
            }
        } catch (Throwable $e) {
            error_log('[payment_don_planifier_suite] ' . $e->getMessage()); // le paiement reste valide
            return;
        }
        if (!empty($d['id_ad'])) {
            notify_member($bdd, (int) $d['id_ad'], 'don_recu', ['reference' => $d['reference'], 'montant' => $d['montant'], 'devise' => $d['devise']], 'don:' . $d['reference']);
        }
    }
}
