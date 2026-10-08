<?php
/**
 * API mobile RCR — version 1 (JSON). Même base de données et mêmes identifiants que le site.
 * Routage : /api/v1/<ressource> (réécriture Apache, voir api/v1/.htaccess) ou /api/v1/index.php/<ressource>.
 * Documentation : api/v1/README.md
 */
require_once __DIR__ . '/../../includes/api.php';
require_once __DIR__ . '/../../includes/flexpay_client.php';

$methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$chemin = $_SERVER['PATH_INFO'] ?? '';
// Mode de secours sans réécriture d'URL ni PATH_INFO : index.php?r=/adhesion (fonctionne sur tout hébergement)
if (isset($_GET['r']) && is_string($_GET['r'])) {
    $chemin = $_GET['r'];
    unset($_GET['r']);
}
if ($chemin === '') {
    $uri = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
    $pos = strpos($uri, '/api/v1');
    $chemin = $pos !== false ? substr($uri, $pos + 7) : '';
    $chemin = preg_replace('#^/index\.php#', '', $chemin);
}
$chemin = '/' . trim((string) $chemin, '/');
$seg = array_values(array_filter(explode('/', $chemin), 'strlen'));
$r0 = $seg[0] ?? '';
$r1 = $seg[1] ?? '';
$r2 = $seg[2] ?? '';

if (!api_table_ok($bdd) && $r0 !== 'ping') {
    api_error('maintenance', "Service mobile non initialisé (migration phase6 à lancer).", 503);
}

/* ======================================================================== lecture publique */
if ($methode === 'GET' && $r0 === 'ping') {
    api_ok(['service' => 'rcr-api', 'version' => 1, 'heure' => date('c')]);
}

if ($methode === 'GET' && $r0 === 'config') {
    api_ok([
        'nom'          => 'Rassemblement des Chrétiens Républicains',
        'devise'       => TARIF_DEVISE,
        'site'         => api_base_url(),
        'contact'      => ['telephone' => reglage('contact_telephone'), 'email' => reglage('contact_email'), 'adresse' => reglage('contact_adresse')],
        'liens'        => ['mot_de_passe_oublie' => api_base_url() . '/index.php?pages=mdp_oublie', 'mentions' => api_base_url() . '/index.php?pages=mention',
                           'confidentialite' => api_base_url() . '/index.php?pages=politique-confidentialite'],
        'don_max'      => 100000,
        'frequences'   => ['mensuel' => 1, 'trimestriel' => 3, 'semestriel' => 6, 'annuel' => 12],
    ]);
}

if ($methode === 'GET' && $r0 === 'tarifs') {
    $cats = [];
    foreach (tarifs_categories($bdd) as $c) {
        $grades = tarifs_grades($bdd, (int) $c['id_qt']);
        if (!$grades) { continue; }
        $cats[] = ['id' => (int) $c['id_qt'], 'nom' => $c['designation'],
            'grades' => array_map(fn($g) => ['id' => (int) $g['id_gd'], 'nom' => $g['nom_gd'], 'prix_mensuel' => (float) $g['prix']], $grades)];
    }
    $per = array_map(fn($p) => ['id' => (int) $p['id_cot'], 'nom' => $p['nom_cot'], 'mois' => (int) $p['mois']], tarifs_periodes($bdd));
    api_ok(['devise' => TARIF_DEVISE, 'categories' => $cats, 'periodes' => $per]);
}

if ($methode === 'GET' && $r0 === 'localisation') {
    if ($r1 === 'provinces') {
        api_ok(['items' => $bdd->query('SELECT id_p AS id, nom_p AS nom FROM provinces ORDER BY nom_p')->fetchAll(PDO::FETCH_ASSOC)]);
    }
    if ($r1 === 'territoires') {
        $s = $bdd->prepare('SELECT id_tr AS id, nom_tr AS nom FROM territoires WHERE id_p = ? ORDER BY nom_tr');
        $s->execute([(int) ($_GET['province'] ?? 0)]);
        api_ok(['items' => $s->fetchAll(PDO::FETCH_ASSOC)]);
    }
    if ($r1 === 'secteurs') {
        $s = $bdd->prepare('SELECT id_sec AS id, nom_sec AS nom FROM secteurs WHERE id_tr = ? ORDER BY nom_sec');
        $s->execute([(int) ($_GET['territoire'] ?? 0)]);
        api_ok(['items' => $s->fetchAll(PDO::FETCH_ASSOC)]);
    }
}

/* ======================================================================== connexion */
if ($methode === 'POST' && $r0 === 'auth' && $r1 === 'login') {
    $ident = api_str('identifiant', 120);
    $mdp = (string) (api_input()['mot_de_passe'] ?? '');
    if ($ident === '' || $mdp === '') {
        api_error('champs', 'Identifiant et mot de passe obligatoires.', 422);
    }
    $cle = 'm' . substr(hash('sha256', login_attempts_client_ip() . '|' . mb_strtolower($ident)), 0, 40);
    if (($verrou = login_attempts_check($bdd, $cle)) !== null) {
        api_error('trop_de_tentatives', $verrou, 429);
    }
    $s = $bdd->prepare('SELECT * FROM adhesion WHERE codes = ? OR mail = ? LIMIT 1');
    $s->execute([strtoupper($ident), mb_strtolower($ident)]);
    $u = $s->fetch(PDO::FETCH_ASSOC) ?: null;
    $hash = $u['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuYF8d1x0p4w0Zr1m3o5q7s9u1w3y5a7c'; // temps constant
    $ok = password_verify($mdp, (string) $hash) && $u && !empty($u['password_hash']);
    if ($ok && $u['statut'] === 'suspendu') {
        api_error('compte_suspendu', 'Votre compte est suspendu. Contactez le secrétariat du RCR.', 403);
    }
    if (!$ok) {
        login_attempts_record_failure($bdd, $cle);
        api_error('identifiants', 'Identifiants incorrects.', 401);
    }
    login_attempts_reset($bdd, $cle);
    $bdd->prepare('UPDATE adhesion SET derniere_connexion = NOW() WHERE id_ad = ?')->execute([(int) $u['id_ad']]);
    $token = api_token_create($bdd, (int) $u['id_ad'], api_str('appareil', 120) ?: 'mobile');
    api_ok(['token' => $token, 'membre' => api_member_public($bdd, $u)]);
}

/* ======================================================================== adhésion (création de compte) */
if ($methode === 'POST' && $r0 === 'adhesion') {
    $cleIp = 'a' . substr(hash('sha256', login_attempts_client_ip()), 0, 40);
    if (($verrou = login_attempts_check($bdd, $cleIp)) !== null) {
        api_error('trop_de_tentatives', $verrou, 429);
    }
    $v = static fn(string $k, int $max = 255): string => api_str($k, $max);
    $nom = $v('nom', 50); $postnom = $v('postnom', 50); $prenom = $v('prenom', 200);
    $mail = mb_strtolower($v('email', 200)); $nationalite = $v('nationalite', 200); $civilite = $v('civilite', 200);
    $telephone = $v('telephone', 20); $datenaiss = $v('date_naissance', 10); $adresse = $v('adresse', 500); $ville = $v('ville', 120);
    $diplome = $v('diplome', 200);
    $idQt = (int) $v('id_categorie', 10); $idGd = (int) $v('id_grade', 10); $idCot = (int) $v('id_periode', 10);
    $province = (int) $v('id_province', 10); $territoire = (int) $v('id_territoire', 10); $secteur = (int) $v('id_secteur', 10);
    $mdp = (string) (api_input()['mot_de_passe'] ?? '');
    $sexe = in_array($v('sexe', 1), ['M', 'F'], true) ? $v('sexe', 1) : null;

    foreach (['nom' => $nom, 'postnom' => $postnom, 'prenom' => $prenom, 'email' => $mail, 'nationalite' => $nationalite, 'civilite' => $civilite,
              'telephone' => $telephone, 'date_naissance' => $datenaiss, 'adresse' => $adresse] as $champ => $val) {
        if ($val === '') { api_error('champ_manquant', 'Champ obligatoire manquant : ' . $champ, 422, ['champ' => $champ]); }
    }
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) { api_error('email_invalide', 'Adresse e-mail invalide.', 422, ['champ' => 'email']); }
    if (!preg_match('/^\+?[0-9 ]{9,16}$/', $telephone)) { api_error('telephone_invalide', 'Numéro de téléphone invalide.', 422, ['champ' => 'telephone']); }
    $d = DateTime::createFromFormat('Y-m-d', $datenaiss);
    if (!$d || $d->format('Y-m-d') !== $datenaiss || $d > new DateTime('today')) { api_error('date_invalide', 'Date de naissance invalide (AAAA-MM-JJ).', 422, ['champ' => 'date_naissance']); }
    if (strlen($mdp) < 8) { api_error('mot_de_passe_faible', 'Mot de passe : 8 caractères minimum.', 422, ['champ' => 'mot_de_passe']); }
    if (empty(api_input()['acceptation_legale']) || api_input()['acceptation_legale'] === 'false' || api_input()['acceptation_legale'] === '0') {
        api_error('acceptation_requise', 'Vous devez accepter les mentions légales et la politique de confidentialité.', 422);
    }
    $tarif = tarifs_calculer($bdd, $idQt, $idGd, $idCot);
    if (!$tarif) { api_error('tarif_indisponible', 'Catégorie, grade ou période indisponible. Veuillez refaire votre choix.', 422); }

    $q = $bdd->prepare('SELECT designation FROM qualites WHERE id_qt = ?'); $q->execute([$idQt]); $qlt = $q->fetch(PDO::FETCH_ASSOC);
    $p = $bdd->prepare('SELECT lettre FROM provinces WHERE id_p = ?'); $p->execute([$province]); $pr = $p->fetch(PDO::FETCH_ASSOC);
    $t = $bdd->prepare('SELECT 1 FROM territoires WHERE id_tr = ? AND id_p = ?'); $t->execute([$territoire, $province]);
    $s = $bdd->prepare('SELECT 1 FROM secteurs WHERE id_sec = ? AND id_tr = ?'); $s->execute([$secteur, $territoire]);
    if (!$qlt || !$pr || !$t->fetchColumn() || !$s->fetchColumn()) { api_error('localisation_invalide', 'Localisation invalide (province, territoire ou secteur).', 422); }

    $m = $bdd->prepare('SELECT 1 FROM adhesion WHERE mail = ?'); $m->execute([$mail]);
    if ($m->fetchColumn()) { api_error('email_existant', 'Cette adresse e-mail est déjà utilisée. Connectez-vous pour renouveler votre cotisation.', 409); }

    // Photo obligatoire (JPEG/PNG, 10 Mo max), CV facultatif (PDF) — mêmes contrôles que le site (type réel vérifié)
    $sauve = static function (array $f, array $types, string $dossier, string $prefixe) {
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) { throw new RuntimeException('Le téléversement a échoué.'); }
        if ($f['size'] > 10 * 1048576) { throw new RuntimeException('Fichier trop volumineux (10 Mo maximum).'); }
        $mime = mime_reel($f['tmp_name']);
        if (!isset($types[$mime])) { throw new RuntimeException('Type de fichier non autorisé.'); }
        if (strpos($mime, 'image/') === 0 && @getimagesize($f['tmp_name']) === false) { throw new RuntimeException('Image invalide.'); }
        if (!is_dir($dossier)) { mkdir($dossier, 0755, true); }
        $nomF = $prefixe . '_' . bin2hex(random_bytes(10)) . '.' . $types[$mime];
        if (!move_uploaded_file($f['tmp_name'], rtrim($dossier, '/') . '/' . $nomF)) { throw new RuntimeException('Enregistrement impossible.'); }
        return $nomF;
    };
    $photo = $cv = '';
    try {
        $photo = $sauve($_FILES['photo'] ?? [], ['image/jpeg' => 'jpg', 'image/png' => 'png'], __DIR__ . '/../../media/passeport', 'Photo');
        if (!empty($_FILES['cv']['name'])) { $cv = $sauve($_FILES['cv'], ['application/pdf' => 'pdf'], __DIR__ . '/../../media/cv', 'CV'); }
    } catch (RuntimeException $e) {
        api_error('fichier_invalide', 'Pièces jointes : ' . $e->getMessage(), 422);
    }
    login_attempts_record_failure($bdd, $cleIp); // compte les créations par IP : 5 inscriptions / 15 min au plus

    $token = bin2hex(random_bytes(32));
    $bdd->beginTransaction();
    try {
        $ins = $bdd->prepare("INSERT INTO adhesion
            (codes, nom, postnom, prenom, mail, nationalite, civilite, sexe, id_qt, grade, codepostal, secteur, territoire, province,
             reglement, telephone, datenaiss, categorie, diplome, passeport, cv, adresse, ville, payment_token, password_hash, statut, id_cot, dat_adhesion)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'en_attente', ?, NOW())");
        $ins->execute(['TMP' . strtoupper(bin2hex(random_bytes(8))), $nom, $postnom, $prenom, $mail, $nationalite, $civilite, $sexe, $idQt, $idGd, '', $secteur, $territoire,
            $province, $idCot, $telephone, $datenaiss, $v('categorie_pro', 200), $diplome, $photo, $cv, $adresse, $ville, $token,
            password_hash($mdp, PASSWORD_DEFAULT), $idCot]);
        $idAd = (int) $bdd->lastInsertId();
        $code = strtoupper(substr($qlt['designation'], 0, 1) . str_pad((string) $idAd, 6, '0', STR_PAD_LEFT)
                . str_pad((string) $secteur, 4, '0', STR_PAD_LEFT) . substr($pr['lettre'], 0, 1));
        $bdd->prepare('UPDATE adhesion SET codes = ? WHERE id_ad = ?')->execute([$code, $idAd]);
        $parrain = (int) ($v('id_parrain', 10));
        if ($parrain > 0 && $parrain !== $idAd) {
            $ex = $bdd->prepare('SELECT 1 FROM adhesion WHERE id_ad = ?'); $ex->execute([$parrain]);
            if ($ex->fetchColumn()) { $bdd->prepare('INSERT INTO parner (id_exp, id_dest) VALUES (?, ?)')->execute([$idAd, $parrain]); }
        }
        $bdd->commit();
    } catch (Throwable $e) {
        $bdd->rollBack();
        error_log('[api adhesion] ' . $e->getMessage());
        api_error('erreur_serveur', 'Une erreur est survenue. Veuillez réessayer.', 500);
    }
    $acces = api_token_create($bdd, $idAd, $v('appareil', 120) ?: 'mobile');
    $row = $bdd->prepare('SELECT * FROM adhesion WHERE id_ad = ?'); $row->execute([$idAd]);
    api_ok(['token' => $acces, 'membre' => api_member_public($bdd, $row->fetch(PDO::FETCH_ASSOC)),
            'montant_a_payer' => ['montant' => $tarif['montant'], 'devise' => $tarif['devise']]], 201);
}

/* ======================================================================== dons (public ou connecté) */
if ($methode === 'POST' && $r0 === 'dons' && $r1 === '') {
    $membre = api_member($bdd); // facultatif : un non-membre peut donner
    $brut = str_replace(',', '.', api_str('montant', 12));
    if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $brut) || (float) $brut <= 0 || (float) $brut > 100000) {
        api_error('montant_invalide', 'Montant invalide : supérieur à 0 et 100 000 USD au maximum.', 422, ['champ' => 'montant']);
    }
    $montant = round((float) $brut, 2);
    $nom = api_str('nom', 100); $prenom = api_str('prenom', 100); $postnom = api_str('postnom', 100); $email = api_str('email', 150);
    if ($membre) {
        $nom = $nom ?: $membre['nom']; $prenom = $prenom ?: $membre['prenom']; $postnom = $postnom ?: $membre['postnom']; $email = $email ?: $membre['mail'];
    }
    if ($nom === '' || $prenom === '') { api_error('champ_manquant', 'Nom et prénom obligatoires.', 422); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { api_error('email_invalide', "L'adresse e-mail saisie n'est pas valide.", 422, ['champ' => 'email']); }
    $canal = api_str('canal', 20) === 'carte' ? 'carte' : 'mobile_money';
    $tel = api_str('telephone', 20) ?: (string) ($membre['telephone'] ?? '');
    $msisdn = flexpay_normalize_phone($tel);
    if ($canal === 'mobile_money' && $msisdn === null) { api_error('telephone_invalide', 'Numéro Mobile Money invalide (ex. 0812345678).', 422, ['champ' => 'telephone']); }
    $telephone = $msisdn ?? preg_replace('/\D+/', '', $tel);
    if (strlen($telephone) < 9) { api_error('telephone_invalide', 'Numéro de téléphone invalide.', 422, ['champ' => 'telephone']); }
    $type = api_str('type_don', 12) === 'regulier' ? 'regulier' : 'ponctuel';
    $freq = null;
    if ($type === 'regulier') {
        $freq = in_array(api_str('frequence', 15), ['mensuel', 'trimestriel', 'semestriel', 'annuel'], true) ? api_str('frequence', 15) : null;
        if ($freq === null) { api_error('frequence_invalide', 'Choisissez la fréquence du don régulier.', 422, ['champ' => 'frequence']); }
    }
    $idProv = (int) api_str('id_province', 10) ?: null;
    if ($idProv) { $q = $bdd->prepare('SELECT 1 FROM provinces WHERE id_p = ?'); $q->execute([$idProv]); if (!$q->fetchColumn()) { $idProv = null; } }

    api_don_creer_et_payer($bdd, [
        'nom' => $nom, 'postnom' => $postnom, 'prenom' => $prenom, 'telephone' => $telephone, 'email' => $email, 'montant' => $montant,
        'canal' => $canal, 'type' => $type, 'frequence' => $freq, 'id_ad' => $membre ? (int) $membre['id_ad'] : null,
        'code_membre' => $membre ? $membre['codes'] : null, 'id_province' => $idProv, 'ville' => api_str('ville', 120), 'adresse' => api_str('adresse', 255),
    ], $msisdn);
}

/**
 * Crée un don + lance FlexPay. Réponse : {don:{id,reference,statut,suivi}, redirect_url?}
 * Mobile Money : l'application attend la confirmation (GET /dons/{id}/statut) ; carte : ouvrir redirect_url dans une vue web.
 */
function api_don_creer_et_payer(PDO $bdd, array $d, ?string $msisdn): void
{
    $reference = payment_new_reference('D');
    $ins = $bdd->prepare("INSERT INTO dons
        (nom_donateur, postnom, prenom, telephone, email, montant, devise, provider, reference, status, ip_donateur,
         type_don, frequence, id_ad, canal, id_province, ville, adresse, code_membre, created_at)
        VALUES (?,?,?,?,?,?, 'USD', 'FLEXPAY', ?, 'pending', ?, ?,?,?,?,?,?,?,?, NOW())");
    $ins->execute([$d['nom'], $d['postnom'], $d['prenom'], $d['telephone'], $d['email'], $d['montant'], $reference,
        substr(login_attempts_client_ip(), 0, 50), $d['type'], $d['frequence'], $d['id_ad'], $d['canal'], $d['id_province'], $d['ville'], $d['adresse'], $d['code_membre']]);
    $id = (int) $bdd->lastInsertId();
    payment_log($bdd, 'don', $id, $reference, 'created', null, 'pending', ['montant' => $d['montant'], 'type' => $d['type'], 'canal' => $d['canal'], 'source' => 'app']);

    if ($d['canal'] === 'mobile_money') {
        $r = flexpay_request_mobile($reference, (string) $msisdn, $d['montant'], 'USD');
    } else {
        $retour = api_base_url() . '/adhere/carte_retour.php?ref=' . urlencode($reference) . '&r=';
        $r = flexpay_request_card($reference, $d['montant'], 'USD', 'Don RCR', $retour . 'approve', $retour . 'cancel', $retour . 'decline');
    }
    if ($r['ok']) {
        payment_set_order_number($bdd, 'don', $id, $r['orderNumber']);
        api_ok(['don' => ['id' => $id, 'reference' => $reference, 'statut' => 'processing', 'suivi' => api_suivi_cle($reference)],
                'redirect_url' => $d['canal'] === 'carte' ? $r['url'] : null], 201);
    }
    payment_transition_status($bdd, 'dons', 'id_don', $id, ['pending'], 'failed');
    payment_log($bdd, 'don', $id, $reference, 'init_failed', 'pending', 'failed', $r['message']);
    api_error('paiement_refuse', $r['message'] ?: "FlexPay a refusé la demande de paiement. Vérifiez le numéro saisi.", 502);
}

/** Statut d'un don : propriétaire connecté, ou lien de suivi (?k=) pour un don sans compte. */
function api_don_acces(PDO $bdd, int $id): array
{
    $s = $bdd->prepare('SELECT * FROM dons WHERE id_don = ?');
    $s->execute([$id]);
    $d = $s->fetch(PDO::FETCH_ASSOC);
    $m = api_member($bdd);
    $cle = (string) ($_GET['k'] ?? '');
    if (!$d || !(($m && (int) $d['id_ad'] === (int) $m['id_ad']) || ($cle !== '' && hash_equals(api_suivi_cle((string) $d['reference']), $cle)))) {
        api_error('introuvable', 'Don introuvable.', 404);
    }
    return $d;
}

if ($r0 === 'dons' && ctype_digit($r1) && in_array($r2, ['statut', 'expirer'], true)) {
    $d = api_don_acces($bdd, (int) $r1);
    $id = (int) $d['id_don'];
    if ($r2 === 'statut' && $methode === 'GET') {
        $st = $d['status'];
        if (in_array($st, PAYMENT_OPEN_STATUSES, true) && (int) ($d['verifie_le'] ? strtotime($d['verifie_le']) : 0) < time() - 3) {
            $st = payment_flexpay_check($bdd, 'don', $id) ?? $st;
            $bdd->prepare('UPDATE dons SET verifie_le = NOW() WHERE id_don = ?')->execute([$id]);
        }
        api_ok(['statut' => $st]);
    }
    if ($r2 === 'expirer' && $methode === 'POST') {
        $res = payment_flexpay_check($bdd, 'don', $id);
        if ($res === null) { payment_expire_pending($bdd, 'don', $id); }
        $row = payment_get_row($bdd, 'don', $id);
        api_ok(['statut' => $row['status'] ?? 'expired']);
    }
}

/* ======================================================================== à partir d'ici : membre connecté */
$m = api_require_member($bdd);
$idAd = (int) $m['id_ad'];

if ($methode === 'POST' && $r0 === 'auth' && $r1 === 'logout') {
    $bdd->prepare('UPDATE api_tokens SET revoque = 1 WHERE id_tok = ?')->execute([(int) $m['id_tok']]);
    api_ok();
}

if ($methode === 'GET' && $r0 === 'me' && $r1 === '') {
    api_ok(['membre' => api_member_public($bdd, $m)]);
}

/* ---- profil complet (identité + circonscriptions + abonnement), mêmes informations que l'espace membre du site ---- */
if ($methode === 'GET' && $r0 === 'me' && $r1 === 'profil') {
    $s = $bdd->prepare('SELECT a.codes, a.nom, a.postnom, a.prenom, a.mail, a.telephone, a.datenaiss, a.civilite, a.sexe, a.nationalite, a.adresse, a.ville,
                               a.dat_adhesion, a.date_echeance, a.statut, p.nom_p AS province, t.nom_tr AS territoire, sc.nom_sec AS secteur,
                               q.designation AS categorie, g.nom_gd AS grade, g.prix AS prix_mensuel, c.nom_cot AS periode, c.mois
                        FROM adhesion a
                        LEFT JOIN provinces p ON p.id_p = a.province LEFT JOIN territoires t ON t.id_tr = a.territoire
                        LEFT JOIN secteurs sc ON sc.id_sec = a.secteur LEFT JOIN qualites q ON q.id_qt = a.id_qt
                        LEFT JOIN grades g ON g.id_gd = a.grade LEFT JOIN cotisation c ON c.id_cot = a.reglement
                        WHERE a.id_ad = ?');
    $s->execute([$idAd]);
    $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
    $mois = max(1, (int) ($r['mois'] ?? 1));
    $prix = (float) ($r['prix_mensuel'] ?? 0);
    api_ok(['profil' => [
        'code' => $r['codes'] ?? '', 'nom' => $r['nom'] ?? '', 'postnom' => $r['postnom'] ?? '', 'prenom' => $r['prenom'] ?? '',
        'email' => $r['mail'] ?? '', 'telephone' => $r['telephone'] ?? '', 'date_naissance' => $r['datenaiss'] ?? null,
        'civilite' => $r['civilite'] ?? '', 'sexe' => $r['sexe'] ?? '', 'nationalite' => $r['nationalite'] ?? '',
        'adresse' => $r['adresse'] ?? '', 'ville' => $r['ville'] ?? '', 'date_adhesion' => $r['dat_adhesion'] ?? null,
        'province' => $r['province'] ?? '', 'territoire' => $r['territoire'] ?? '', 'secteur' => $r['secteur'] ?? '',
        'categorie' => $r['categorie'] ?? '', 'grade' => $r['grade'] ?? '', 'periode' => $r['periode'] ?? '',
        'prix_mensuel' => $prix, 'mois' => $mois, 'total_periode' => round($prix * $mois, 2), 'devise' => 'USD',
    ]]);
}

/* ---- parrainage : lien personnel, filleuls, commission estimée (lecture seule, comme sur le site) ---- */
if ($methode === 'GET' && $r0 === 'me' && $r1 === 'parrainage') {
    require_once __DIR__ . '/../../config/commission.php';
    $taux = (float) TAUX_COMMISSION_PARRAINAGE;
    // parner.id_exp = le NOUVEAU membre, parner.id_dest = le PARRAIN (sémantique réelle, voir functions/esp_membre.funct.php)
    $s = $bdd->prepare("SELECT a.id_ad, a.nom, a.postnom, a.prenom, a.codes, a.dat_adhesion, g.nom_gd,
                               COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.montant ELSE 0 END), 0) AS total_paye,
                               COUNT(CASE WHEN pay.status = 'paid' THEN pay.id END) AS nb_paiements
                        FROM adhesion a INNER JOIN parner pr ON a.id_ad = pr.id_exp
                        LEFT JOIN grades g ON a.grade = g.id_gd LEFT JOIN payments pay ON pay.id_ad = a.id_ad
                        WHERE pr.id_dest = ?
                        GROUP BY a.id_ad, a.nom, a.postnom, a.prenom, a.codes, a.dat_adhesion, g.nom_gd
                        ORDER BY a.dat_adhesion DESC LIMIT 200");
    $s->execute([$idAd]);
    $total = 0.0;
    $items = array_map(function ($f) use ($taux, &$total) {
        $com = round((float) $f['total_paye'] * $taux / 100, 2);
        $total += $com;
        return ['nom' => trim($f['prenom'] . ' ' . $f['nom'] . ' ' . $f['postnom']), 'code' => $f['codes'], 'grade' => $f['nom_gd'],
                'date_adhesion' => $f['dat_adhesion'], 'nb_paiements' => (int) $f['nb_paiements'],
                'total_paye' => (float) $f['total_paye'], 'commission' => $com];
    }, $s->fetchAll(PDO::FETCH_ASSOC));
    $lien = api_base_url() . '/adhere/adhesion.php?idmbre=' . $idAd;
    api_ok(['lien' => $lien, 'message' => "Rejoignez le RCR (Rassemblement des Chrétiens Républicains) ! Adhérez via mon lien de parrainage : " . $lien,
            'taux' => $taux, 'nombre_filleuls' => count($items), 'commission_totale' => round($total, 2), 'devise' => 'USD', 'filleuls' => $items]);
}

if ($methode === 'POST' && $r0 === 'me' && $r1 === 'mot-de-passe') {
    $actuel = (string) (api_input()['actuel'] ?? ''); $nouveau = (string) (api_input()['nouveau'] ?? '');
    $cle = 'm' . substr(hash('sha256', login_attempts_client_ip() . '|' . $m['codes']), 0, 40);
    if (($verrou = login_attempts_check($bdd, $cle)) !== null) { api_error('trop_de_tentatives', $verrou, 429); }
    if (!password_verify($actuel, (string) $m['password_hash'])) {
        login_attempts_record_failure($bdd, $cle);
        api_error('mot_de_passe_incorrect', 'Le mot de passe actuel est incorrect.', 422);
    }
    if (strlen($nouveau) < 8 || strlen($nouveau) > 72) { api_error('mot_de_passe_faible', 'Nouveau mot de passe : 8 à 72 caractères.', 422); }
    if (hash_equals($actuel, $nouveau)) { api_error('mot_de_passe_identique', "Le nouveau mot de passe doit être différent de l'actuel.", 422); }
    $bdd->prepare('UPDATE adhesion SET password_hash = ? WHERE id_ad = ?')->execute([password_hash($nouveau, PASSWORD_DEFAULT), $idAd]);
    // Les autres appareils sont déconnectés ; l'appareil courant reste connecté
    $bdd->prepare('UPDATE api_tokens SET revoque = 1 WHERE id_ad = ? AND id_tok <> ?')->execute([$idAd, (int) $m['id_tok']]);
    login_attempts_reset($bdd, $cle);
    api_ok(['message' => 'Mot de passe modifié.']);
}

/* ---- cotisation / adhésion : paiement ---- */
if ($methode === 'POST' && $r0 === 'paiements' && $r1 === '') {
    // L'adhésion initiale et chaque renouvellement passent ici : le type est déduit de l'historique (comme sur le site).
    $idCot = (int) api_str('id_periode', 10) ?: (int) $m['reglement'];
    $tarif = tarifs_calculer($bdd, (int) $m['id_qt'], (int) $m['grade'], $idCot);
    if (!$tarif) { api_error('tarif_indisponible', "Ce tarif n'est plus disponible. Contactez le secrétariat du RCR.", 422); }
    $canal = api_str('canal', 20) === 'carte' ? 'carte' : 'mobile_money';
    $msisdn = null;
    if ($canal === 'mobile_money') {
        $msisdn = flexpay_normalize_phone(api_str('telephone', 20) ?: (string) $m['telephone']);
        if ($msisdn === null) { api_error('telephone_invalide', 'Numéro Mobile Money invalide (ex. 0812345678).', 422, ['champ' => 'telephone']); }
    }
    $deja = $bdd->prepare("SELECT COUNT(*) FROM payments WHERE id_ad = ? AND status = 'paid'");
    $deja->execute([$idAd]);
    $type = ((int) $deja->fetchColumn() > 0) ? 'cotisation' : 'adhesion';

    // Anti double paiement : demande Mobile Money encore en cours (< 3 min) reprise au lieu d'un second push
    if ($canal === 'mobile_money') {
        $ec = $bdd->prepare("SELECT id, reference FROM payments WHERE id_ad = ? AND canal = 'mobile_money' AND status IN ('pending','processing')
                             AND order_number IS NOT NULL AND created_at > (NOW() - INTERVAL 3 MINUTE) ORDER BY id DESC LIMIT 1");
        $ec->execute([$idAd]);
        if ($row = $ec->fetch(PDO::FETCH_ASSOC)) {
            api_ok(['paiement' => ['id' => (int) $row['id'], 'reference' => $row['reference'], 'statut' => 'processing', 'type' => $type], 'redirect_url' => null, 'repris' => true]);
        }
    }
    $reference = payment_new_reference($type === 'adhesion' ? 'A' : 'C');
    $ins = $bdd->prepare("INSERT INTO payments
        (id_ad, adhesion_id, reference, reference_payment, codes_ad, telephone_py, montant, devise, provider, status, type_transaction, id_cot, canal, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'USD', 'FLEXPAY', 'pending', ?, ?, ?, NOW())");
    $ins->execute([$idAd, $idAd, $reference, $reference, $m['codes'], $msisdn ?? '', $tarif['montant'], $type, $tarif['id_cot'], $canal]);
    $pid = (int) $bdd->lastInsertId();
    payment_log($bdd, $type, $pid, $reference, 'created', null, 'pending', ['canal' => $canal, 'montant' => $tarif['montant'], 'source' => 'app']);

    if ($canal === 'mobile_money') {
        $r = flexpay_request_mobile($reference, $msisdn, $tarif['montant'], 'USD');
    } else {
        $retour = api_base_url() . '/adhere/carte_retour.php?ref=' . urlencode($reference) . '&r=';
        $r = flexpay_request_card($reference, $tarif['montant'], 'USD', 'RCR - ' . ($type === 'adhesion' ? 'Adhésion' : 'Cotisation') . ' ' . $m['codes'],
            $retour . 'approve', $retour . 'cancel', $retour . 'decline');
    }
    if ($r['ok']) {
        payment_set_order_number($bdd, 'payment', $pid, $r['orderNumber']);
        api_ok(['paiement' => ['id' => $pid, 'reference' => $reference, 'statut' => 'processing', 'type' => $type, 'montant' => $tarif['montant'], 'devise' => 'USD'],
                'redirect_url' => $canal === 'carte' ? $r['url'] : null], 201);
    }
    payment_transition_status($bdd, 'payments', 'id', $pid, ['pending'], 'failed');
    payment_log($bdd, $type, $pid, $reference, 'init_failed', 'pending', 'failed', $r['message']);
    api_error('paiement_refuse', $r['message'] ?: "FlexPay a refusé la demande de paiement. Vérifiez le numéro saisi.", 502);
}

if ($methode === 'GET' && $r0 === 'paiements' && $r1 === '') {
    $s = $bdd->prepare("SELECT id, reference, type_transaction AS type, montant, devise, canal, status AS statut, created_at AS date, periode_debut, periode_fin
                        FROM payments WHERE id_ad = ? ORDER BY id DESC LIMIT 100");
    $s->execute([$idAd]);
    api_ok(['items' => array_map(function ($r) {
        $r['id'] = (int) $r['id']; $r['montant'] = (float) $r['montant']; $r['recu_disponible'] = $r['statut'] === 'paid';
        return $r;
    }, $s->fetchAll(PDO::FETCH_ASSOC))]);
}

/** Paiement appartenant au membre connecté (jamais celui d'un autre). */
function api_paiement_du_membre(PDO $bdd, int $idAd, int $id): array
{
    $s = $bdd->prepare('SELECT id, reference, status, id_ad FROM payments WHERE id = ? AND id_ad = ?');
    $s->execute([$id, $idAd]);
    $p = $s->fetch(PDO::FETCH_ASSOC);
    if (!$p) { api_error('introuvable', 'Paiement introuvable.', 404); }
    return $p;
}

if ($r0 === 'paiements' && ctype_digit($r1) && in_array($r2, ['statut', 'expirer', 'recu'], true)) {
    $p = api_paiement_du_membre($bdd, $idAd, (int) $r1);
    $id = (int) $p['id'];
    if ($r2 === 'statut' && $methode === 'GET') {
        $st = $p['status'];
        if (in_array($st, PAYMENT_OPEN_STATUSES, true)) {
            $v = $bdd->prepare('SELECT verifie_le FROM payments WHERE id = ?'); $v->execute([$id]);
            $last = $v->fetchColumn();
            if (!$last || strtotime($last) < time() - 3) {
                $st = payment_flexpay_check($bdd, 'adhesion', $id) ?? $st;
                $bdd->prepare('UPDATE payments SET verifie_le = NOW() WHERE id = ?')->execute([$id]);
            }
        }
        api_ok(['statut' => $st, 'membre' => $st === 'paid' ? api_member_public($bdd, $m) : null]);
    }
    if ($r2 === 'expirer' && $methode === 'POST') {
        $res = payment_flexpay_check($bdd, 'adhesion', $id);
        if ($res === null) { payment_expire_pending($bdd, 'adhesion', $id); }
        $row = payment_get_row($bdd, 'adhesion', $id);
        api_ok(['statut' => $row['status'] ?? 'expired']);
    }
    if ($r2 === 'recu' && $methode === 'GET') {
        $_SESSION = ['id_ad' => $idAd]; $_GET['t'] = 'p'; $_GET['id'] = $id; // le script de reçu contrôle lui-même la propriété
        require __DIR__ . '/../../member/recu.php';
        exit;
    }
}

/* ---- dons du membre : liste, renouvellement, reçu ---- */
if ($methode === 'GET' && $r0 === 'dons' && $r1 === '') {
    $s = $bdd->prepare("SELECT d.id_don AS id, d.reference, d.montant, d.devise, d.canal, d.status AS statut, d.type_don AS type, d.frequence,
                               d.prochaine_echeance, d.created_at AS date,
                               (d.type_don = 'regulier' AND d.status = 'paid' AND d.prochaine_echeance IS NOT NULL AND d.prochaine_echeance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                AND NOT EXISTS (SELECT 1 FROM dons d2 WHERE d2.id_ad = d.id_ad AND d2.type_don = 'regulier' AND d2.frequence = d.frequence
                                                AND d2.status = 'paid' AND d2.id_don > d.id_don)) AS a_renouveler
                        FROM dons d WHERE d.id_ad = ? ORDER BY d.id_don DESC LIMIT 100");
    $s->execute([$idAd]);
    api_ok(['items' => array_map(function ($r) {
        $r['id'] = (int) $r['id']; $r['montant'] = (float) $r['montant']; $r['a_renouveler'] = (bool) $r['a_renouveler'];
        $r['recu_disponible'] = $r['statut'] === 'paid';
        return $r;
    }, $s->fetchAll(PDO::FETCH_ASSOC))]);
}

if ($r0 === 'dons' && ctype_digit($r1) && $r2 === 'renouveler' && $methode === 'POST') {
    $s = $bdd->prepare("SELECT * FROM dons WHERE id_don = ? AND id_ad = ? AND type_don = 'regulier' AND status = 'paid'");
    $s->execute([(int) $r1, $idAd]);
    $o = $s->fetch(PDO::FETCH_ASSOC);
    if (!$o) { api_error('introuvable', 'Don régulier introuvable.', 404); }
    $brut = str_replace(',', '.', api_str('montant', 12) ?: (string) $o['montant']);
    if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $brut) || (float) $brut <= 0 || (float) $brut > 100000) {
        api_error('montant_invalide', 'Montant invalide : supérieur à 0 et 100 000 USD au maximum.', 422);
    }
    $canal = api_str('canal', 20) === 'carte' ? 'carte' : ((api_str('canal', 20) === 'mobile_money') ? 'mobile_money' : $o['canal']);
    $msisdn = flexpay_normalize_phone(api_str('telephone', 20) ?: (string) $o['telephone']);
    if ($canal === 'mobile_money' && $msisdn === null) { api_error('telephone_invalide', 'Numéro Mobile Money invalide (ex. 0812345678).', 422, ['champ' => 'telephone']); }
    api_don_creer_et_payer($bdd, [
        'nom' => $o['nom_donateur'], 'postnom' => (string) $o['postnom'], 'prenom' => $o['prenom'], 'telephone' => $msisdn ?? $o['telephone'], 'email' => (string) $o['email'],
        'montant' => round((float) $brut, 2), 'canal' => $canal, 'type' => 'regulier', 'frequence' => $o['frequence'], 'id_ad' => $idAd,
        'code_membre' => $m['codes'], 'id_province' => $o['id_province'] ?: null, 'ville' => (string) $o['ville'], 'adresse' => (string) $o['adresse'],
    ], $msisdn);
}

if ($methode === 'GET' && $r0 === 'dons' && ctype_digit($r1) && $r2 === 'recu') {
    api_don_acces($bdd, (int) $r1);
    $_SESSION = ['id_ad' => $idAd]; $_GET['t'] = 'd'; $_GET['id'] = (int) $r1;
    require __DIR__ . '/../../member/recu.php';
    exit;
}

/* ---- carte de membre (PDF) ---- */
if ($methode === 'GET' && $r0 === 'me' && $r1 === 'carte') {
    $_SESSION = ['id_ad' => $idAd];
    require __DIR__ . '/../../member/carte.php';
    exit;
}

/* ---- fiche d'adhésion (PDF) : le script contrôle lui-même que le membre en est propriétaire ---- */
if ($methode === 'GET' && $r0 === 'me' && $r1 === 'fiche') {
    $_SESSION = ['id_ad' => $idAd]; $_GET['cod'] = $idAd;
    chdir(__DIR__ . '/../../admin/pages/print'); // le script utilise des chemins relatifs
    require __DIR__ . '/../../admin/pages/print/print_adherer.php';
    exit;
}

api_error('route_inconnue', 'Ressource inconnue.', 404);
