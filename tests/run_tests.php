<?php
/**
 * Batterie de tests RCR (CLI).
 *
 *   php tests/run_tests.php            -> tests SANS base de données (logique pure + contrôles de fichiers)
 *   php tests/run_tests.php --db       -> + tests d'intégration sur une base de TEST
 *
 * SÉCURITÉ : le mode --db écrit dans la base. Il REFUSE de tourner si le nom de la base
 * (DB_NAME de config/database.php) ne contient pas « test ». Créez une base rcr_test,
 * importez rcr.sql, lancez phase1/2/3_migrate.php --apply dessus, puis pointez config/database.php vers elle.
 * Le FlexPay réel n'est JAMAIS appelé : la vérification de transaction est remplacée par un simulateur.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$root = dirname(__DIR__);
$withDb = in_array('--db', $argv ?? [], true);
$GLOBALS['t_ok'] = 0; $GLOBALS['t_ko'] = 0; $GLOBALS['echecs'] = [];

function t(string $nom, bool $cond, string $detail = ''): void
{
    if ($cond) { $GLOBALS['t_ok']++; echo "  [OK ] $nom\n"; return; }
    $GLOBALS['t_ko']++; $GLOBALS['echecs'][] = $nom;
    echo "  [FAIL] $nom" . ($detail !== '' ? " — $detail" : '') . "\n";
}
function eq(string $nom, $attendu, $obtenu): void
{
    t($nom, $attendu === $obtenu, 'attendu ' . var_export($attendu, true) . ', obtenu ' . var_export($obtenu, true));
}
function titre(string $s): void { echo "\n== $s\n"; }

// Simulateur FlexPay (défini AVANT le client réel : celui-ci ne redéfinit pas une fonction existante)
$GLOBALS['fp_stub'] = null;
function flexpay_check_order(string $orderNumber): ?array { return $GLOBALS['fp_stub']; }

require_once $root . '/includes/flexpay_client.php';
require_once $root . '/includes/helpers.php';
require_once $root . '/includes/contenu.php';
require_once $root . '/includes/upload.php';
require_once $root . '/includes/audit.php';
require_once $root . '/includes/tarifs.php';
require_once $root . '/includes/payment_helpers.php';

/* ------------------------------------------------------------------ SANS BASE */
titre('Numéros de téléphone Mobile Money');
eq('0812345678', '243812345678', flexpay_normalize_phone('0812345678'));
eq('+243 812 345 678', '243812345678', flexpay_normalize_phone('+243 812 345 678'));
eq('243812345678', '243812345678', flexpay_normalize_phone('243812345678'));
eq('812345678 (sans préfixe)', '243812345678', flexpay_normalize_phone('812345678'));
eq('00243812345678', '243812345678', flexpay_normalize_phone('00243812345678'));
eq('trop court', null, flexpay_normalize_phone('0812'));
eq('lettres', null, flexpay_normalize_phone('abcdefghij'));
eq('vide', null, flexpay_normalize_phone(''));

titre('Format des montants envoyés à FlexPay');
eq('10', '10', flexpay_amount(10.0));
eq('10.5', '10.5', flexpay_amount(10.50));
eq('0.99', '0.99', flexpay_amount(0.99));
eq('2400', '2400', flexpay_amount(2400.0));

titre('Références de transaction');
$refs = [];
for ($i = 0; $i < 200; $i++) { $refs[payment_new_reference('A')] = 1; }
eq('200 références uniques', 200, count($refs));
t('format attendu par carte_retour.php', (bool) preg_match('/^RCR-[ACD]-\d{6}-[A-F0-9]{10}$/', array_key_first($refs)));

titre('Assainissement du HTML saisi en administration (XSS)');
$v = [
    'script'        => ['<p>a</p><script>alert(1)</script>', 'script'],
    'onerror'       => ['<img src="x" onerror="alert(1)">', 'onerror'],
    'onclick'       => ['<a href="#" onclick=\'alert(1)\'>x</a>', 'onclick'],
    'javascript:'   => ['<a href="javascript:alert(1)">x</a>', 'javascript:'],
    'iframe http'   => ['<iframe src="http://evil.example/x"></iframe>', 'evil.example'],
    'object'        => ['<object data="x.swf"></object>', '<object'],
];
foreach ($v as $nom => [$html, $interdit]) {
    t("retire : $nom", stripos(contenu_assainir($html), $interdit) === false, contenu_assainir($html));
}
t('conserve le HTML légitime', strpos(contenu_assainir('<h2>Titre</h2><p><strong>ok</strong></p>'), '<strong>ok</strong>') !== false);
t('conserve une vidéo https', strpos(contenu_assainir('<iframe src="https://www.youtube.com/embed/x"></iframe>'), 'youtube.com') !== false);

titre('Noms de fichiers téléversés');
$n1 = upload_nom_aleatoire('Photo ../../x', '.jpg'); $n2 = upload_nom_aleatoire('Photo', '.jpg');
t('pas de séparateur de chemin', strpos($n1, '/') === false && strpos($n1, '..') === false, $n1);
t('noms différents', $n1 !== $n2);

titre('Modèles d\'e-mails');
require_once $root . '/includes/notif_sender.php';
foreach (['adhesion_confirmee', 'paiement_confirme', 'echeance_proche', 'cotisation_expiree', 'don_recu', 'don_rappel'] as $ev) {
    $r = notif_rendre($ev, ['montant' => 20, 'devise' => 'USD', 'reference' => 'RCR-A-260101-AAAAAAAAAA', 'echeance' => '2026-12-31', 'jours' => 7]);
    t("modèle $ev", is_array($r) && $r[0] !== '' && strpos($r[1], '<html') !== false);
}
$r = notif_rendre('don_recu', ['montant' => 5, 'devise' => 'USD', 'reference' => '<script>alert(1)</script>']);
t('référence échappée dans l\'e-mail', strpos($r[1], '<script>') === false);
eq('événement inconnu', null, notif_rendre('inconnu', []));
t('injection d\'en-tête refusée', mail_envoyer("a@b.cd\r\nBcc: x@y.cd", 's', 'h')[0] === false);

titre('Protection des fichiers et des dossiers');
foreach (['config', 'includes', 'storage', 'migrations', 'tests', 'tools'] as $d) {
    t("$d/.htaccess présent", is_file("$root/$d/.htaccess"));
}
t('media/.htaccess bloque PHP', is_file("$root/media/.htaccess") && strpos(file_get_contents("$root/media/.htaccess"), 'php') !== false);
$rob = (string) @file_get_contents("$root/robots.txt");
t('robots.txt interdit /admin/', strpos($rob, 'Disallow: /admin/') !== false);
t('robots.txt indique le sitemap', strpos($rob, 'sitemap.xml') !== false);
$ht = (string) @file_get_contents("$root/.htaccess");
t('.htaccess : en-têtes de sécurité', strpos($ht, 'X-Content-Type-Options') !== false && strpos($ht, 'X-Frame-Options') !== false);
t('.htaccess : pages 403/404/500', strpos($ht, 'ErrorDocument 404') !== false && strpos($ht, 'ErrorDocument 500') !== false);

titre('Aucun secret en clair hors de config/');
$trouves = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    $p = $f->getPathname(); $rel = ltrim(str_replace($root, '', $p), '/\\');
    if (!$f->isFile() || strpos($rel, 'TCPDF') !== false || strpos($rel, 'config/') === 0 || strpos($rel, 'storage/') === 0
        || strpos($rel, 'tests/') === 0 || !preg_match('/\.(php|js|html|md|txt|json|sql|sh|ini)$/i', $rel) || $f->getSize() > 2000000) { continue; }
    if (preg_match('/eyJ[A-Za-z0-9_-]{20,}\.eyJ[A-Za-z0-9_-]{20,}\.[A-Za-z0-9_-]{20,}/', (string) file_get_contents($p))) { $trouves[] = $rel; }
}
t('aucun jeton JWT (FlexPay) hors config/', $trouves === [], implode(', ', $trouves));

titre('Fichiers résiduels à ne pas mettre en production');
foreach (['rcr.sql', 'rcr.zip', 'phpinfo.php', 'info.php', 'allfiles.txt', 'files.txt', 'index1.php', 'anim.html', 'php.ini'] as $f) {
    t("absent : $f", !file_exists("$root/$f"), 'à supprimer du serveur de production');
}

/* ------------------------------------------------------------------- AVEC BASE */
if (!$withDb) {
    echo "\n(tests d'intégration ignorés : relancez avec --db sur une base de TEST)\n";
} else {
    require_once $root . '/config/database.php';
    if (stripos(DB_NAME, 'test') === false) {
        fwrite(STDERR, "\nREFUS : la base « " . DB_NAME . " » ne contient pas « test ». Les tests --db écrivent dans la base.\n");
        exit(2);
    }
    $bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    echo "\n>>> Base de test : " . DB_NAME . "\n";
    $creesAd = []; $creesPay = [];

    try {
        titre('Barème des cotisations (cahier des charges)');
        $qt = [];
        foreach ($bdd->query('SELECT id_qt, designation FROM qualites')->fetchAll() as $q) {
            foreach (['fond' => 'Fondateur', 'effe' => 'Effectif', 'symp' => 'Sympathisant'] as $cle => $mot) {
                if (stripos($q['designation'], $mot) !== false) { $qt[$cle] = (int) $q['id_qt']; }
            }
        }
        $per = []; foreach ($bdd->query('SELECT id_cot, mois FROM cotisation WHERE actif = 1')->fetchAll() as $p) { $per[(int) $p['mois']] = (int) $p['id_cot']; }
        ksort($per);
        eq('4 périodes actives (1, 3, 6, 12 mois)', [1, 3, 6, 12], array_keys($per));
        $bareme = [
            'fond' => ['Diamant Puissant' => 200, 'Super Diamant' => 100, 'Diamant Force' => 50, 'Diamant' => 25],
            'effe' => ['Super Diamant' => 100, 'Diamant Force' => 50, 'Diamant' => 25, 'Platine' => 20],
            'symp' => ['Diamant' => 25, 'Platine' => 20, 'Or' => 10, 'Argent' => 5],
        ];
        $grade = [];
        foreach ($bareme as $cat => $grades) {
            t("catégorie $cat trouvée", isset($qt[$cat]));
            foreach ($grades as $nom => $mensuel) {
                $s = $bdd->prepare('SELECT id_gd FROM grades WHERE id_qt = ? AND nom_gd = ? AND actif = 1 AND ancien = 0');
                $s->execute([$qt[$cat] ?? 0, $nom]);
                $idGd = (int) $s->fetchColumn();
                $grade[$cat][$nom] = $idGd;
                t("$cat / $nom existe", $idGd > 0);
                // Le montant facturé doit toujours être : prix mensuel EN BASE × nombre de mois (le prix est modifiable
                // en administration). Un écart avec le barème du cahier des charges est signalé, sans faire échouer le test.
                $s2 = $bdd->prepare('SELECT prix FROM grades WHERE id_gd = ?'); $s2->execute([$idGd]);
                $prixBase = (float) $s2->fetchColumn();
                if ($idGd && abs($prixBase - $mensuel) > 0.001) {
                    echo "  [INFO] $cat / $nom : prix en base {$prixBase} USD (barème initial {$mensuel} USD) — modifié en administration\n";
                }
                foreach ([1, 3, 6, 12] as $mois) {
                    $r = $idGd ? tarifs_calculer($bdd, $qt[$cat], $idGd, $per[$mois] ?? 0) : null;
                    eq("$cat / $nom / {$mois} mois = " . ($prixBase * $mois) . ' USD', round($prixBase * $mois, 2), isset($r['montant']) ? round((float) $r['montant'], 2) : null);
                }
            }
        }
        titre('Refus des combinaisons invalides');
        eq('Platine refusé pour un Fondateur', null, tarifs_calculer($bdd, $qt['fond'], $grade['effe']['Platine'], $per[1]));
        eq('Argent refusé pour un Effectif', null, tarifs_calculer($bdd, $qt['effe'], $grade['symp']['Argent'], $per[1]));
        eq('période inconnue', null, tarifs_calculer($bdd, $qt['symp'], $grade['symp']['Argent'], 999999));
        $bdd->prepare('UPDATE grades SET actif = 0 WHERE id_gd = ?')->execute([$grade['symp']['Argent']]);
        eq('grade désactivé refusé', null, tarifs_calculer($bdd, $qt['symp'], $grade['symp']['Argent'], $per[1]));
        $bdd->prepare('UPDATE grades SET actif = 1 WHERE id_gd = ?')->execute([$grade['symp']['Argent']]);

        titre('Cycle de vie d\'un paiement (FlexPay simulé)');
        $uniq = 'T' . substr(bin2hex(random_bytes(6)), 0, 10);
        $bdd->prepare("INSERT INTO adhesion (codes, nom, postnom, prenom, mail, nationalite, civilite, id_qt, grade, codepostal, secteur, territoire, province,
                        reglement, telephone, datenaiss, categorie, diplome, passeport, cv, adresse, dat_adhesion, statut, id_cot)
                       VALUES (?, 'Test', 'Test', 'Test', ?, 'Congolaise', 'M', ?, ?, '0', 1, 1, 1, ?, '243812345678', '1990-01-01', 'x', 'x', 'x', '', 'x', NOW(), 'en_attente', ?)")
            ->execute([$uniq, "$uniq@test.invalid", $qt['effe'], $grade['effe']['Platine'], $per[1], $per[1]]);
        $idAd = (int) $bdd->lastInsertId(); $creesAd[] = $idAd;
        $membre = ['id_ad' => $idAd, 'codes' => $uniq];
        $tarif = tarifs_calculer($bdd, $qt['effe'], $grade['effe']['Platine'], $per[1]);
        eq('Platine mensuel Effectif = 20 USD', 20.0, $tarif['montant']);

        $nouveau = function () use ($bdd, $membre, $tarif, &$creesPay) {
            [$id, $ref] = payment_create($bdd, $membre, $tarif, 'adhesion', 'mobile_money', '243812345678');
            $creesPay[] = $id;
            payment_set_order_number($bdd, 'payment', $id, 'TESTORDER' . strtoupper(bin2hex(random_bytes(6))));
            return [$id, $ref];
        };
        $statut = fn(int $id) => $bdd->query("SELECT status FROM payments WHERE id = $id")->fetchColumn();
        $trouver = fn(string $ref) => payment_find_by_reference($bdd, $ref);

        [$id1, $ref1] = $nouveau();
        eq('état initial : processing', 'processing', $statut($id1));

        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref1, 'amount' => 1.0, 'currency' => 'USD'];
        eq('montant falsifié refusé', null, payment_verify_and_confirm($bdd, $trouver($ref1)));
        eq('… et le paiement reste ouvert', 'processing', $statut($id1));

        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => 'RCR-A-000000-AAAAAAAAAA', 'amount' => 20.0, 'currency' => 'USD'];
        eq('référence différente refusée', null, payment_verify_and_confirm($bdd, $trouver($ref1)));

        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref1, 'amount' => 20.0, 'currency' => 'CDF'];
        eq('mauvaise devise refusée', null, payment_verify_and_confirm($bdd, $trouver($ref1)));

        $GLOBALS['fp_stub'] = null;
        eq('FlexPay injoignable : aucun changement', null, payment_verify_and_confirm($bdd, $trouver($ref1)));
        eq('… statut inchangé', 'processing', $statut($id1));

        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '1', 'reference' => $ref1, 'amount' => 20.0, 'currency' => 'USD'];
        eq('échec < 3 min : on attend encore', null, payment_verify_and_confirm($bdd, $trouver($ref1)));
        $bdd->exec("UPDATE payments SET created_at = (NOW() - INTERVAL 10 MINUTE) WHERE id = $id1");
        eq('échec > 3 min : failed', 'failed', payment_verify_and_confirm($bdd, $trouver($ref1)));

        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref1, 'amount' => 20.0, 'currency' => 'USD'];
        eq('confirmation TARDIVE après échec : paid', 'paid', payment_verify_and_confirm($bdd, $trouver($ref1)));
        eq('statut final paid', 'paid', $statut($id1));
        $m = $bdd->query("SELECT statut, date_echeance FROM adhesion WHERE id_ad = $idAd")->fetch();
        eq('membre activé', 'actif', $m['statut']);
        eq('échéance = aujourd\'hui + 1 mois', date('Y-m-d', strtotime('+1 month')), $m['date_echeance']);
        $echeance1 = $m['date_echeance'];

        payment_verify_and_confirm($bdd, $trouver($ref1));
        payment_flexpay_check($bdd, 'adhesion', $id1);
        $m2 = $bdd->query("SELECT date_echeance FROM adhesion WHERE id_ad = $idAd")->fetchColumn();
        eq('rejeu du callback : échéance inchangée (pas de doublon)', $echeance1, $m2);
        $nb = (int) $bdd->query("SELECT COUNT(*) FROM payment_logs WHERE reference = " . $bdd->quote($ref1) . " AND evenement = 'status_change' AND statut_apres = 'paid'")->fetchColumn();
        eq('une seule transition vers paid dans le journal', 1, $nb);

        [$id2, $ref2] = $nouveau();
        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref2, 'amount' => 20.0, 'currency' => 'USD'];
        payment_verify_and_confirm($bdd, $trouver($ref2));
        $m3 = $bdd->query("SELECT date_echeance FROM adhesion WHERE id_ad = $idAd")->fetchColumn();
        eq('renouvellement anticipé : +1 mois à partir de l\'ancienne échéance', date('Y-m-d', strtotime($echeance1 . ' +1 month')), $m3);

        [$id3, $ref3] = $nouveau();
        payment_apply_status($bdd, $trouver($ref3), 'cancelled', false);
        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref3, 'amount' => 20.0, 'currency' => 'USD'];
        payment_verify_and_confirm($bdd, $trouver($ref3));
        // Règle (audit 2026-10) : si FlexPay CONFIRME l'encaissement d'une transaction annulée côté site
        // (ex. annulation sur la page carte puis validation tardive), l'argent est réellement reçu : on l'enregistre.
        eq('annulé puis confirmé par FlexPay (vérifié) : paid', 'paid', $statut($id3));
        $GLOBALS['fp_stub'] = null;
        [$id3b, $ref3b] = $nouveau();
        payment_apply_status($bdd, $trouver($ref3b), 'cancelled', false);
        eq('annulé sans confirmation FlexPay : reste cancelled', 'cancelled', $statut($id3b));

        [$id4, $ref4] = $nouveau();
        $GLOBALS['fp_stub'] = ['found' => true, 'status' => '0', 'reference' => $ref4, 'amount' => 5.0, 'currency' => 'USD'];
        eq('payment_mark_status_by_reference(paid) passe par la vérification', null, payment_mark_status_by_reference($bdd, $ref4, 'paid'));
        eq('… statut inchangé', 'processing', $statut($id4));
        $ok = payment_apply_status($bdd, $trouver($ref4), 'paid', false);
        t('forcer paid sans vérification est impossible', $ok === false && $statut($id4) === 'processing');

        titre('Droits des rôles (RBAC)');
        $droits = [];
        foreach ($bdd->query('SELECT r.code AS role, p.code AS perm FROM role_permissions rp JOIN roles r ON r.id_role = rp.id_role JOIN permissions p ON p.id_perm = rp.id_perm')->fetchAll() as $x) { $droits[$x['role']][] = $x['perm']; }
        $nbPerm = (int) $bdd->query('SELECT COUNT(*) FROM permissions')->fetchColumn();
        eq('administrateur principal : toutes les permissions', $nbPerm, count($droits['admin_principal'] ?? []));
        $interdits = [
            'resp_publications' => ['membres.gerer', 'tarifs.gerer', 'paiements.valider', 'admins.gerer', 'systeme.gerer', 'geo.gerer'],
            'gest_effectifs'    => ['tarifs.gerer', 'contenu.gerer', 'admins.gerer', 'systeme.gerer', 'paiements.valider'],
            'resp_numerique'    => ['membres.gerer', 'contenu.gerer', 'paiements.valider', 'media.gerer'],
        ];
        foreach ($interdits as $role => $liste) {
            foreach ($liste as $perm) { t("$role n'a PAS $perm", !in_array($perm, $droits[$role] ?? [], true)); }
        }
        foreach (['resp_publications' => ['contenu.gerer', 'media.gerer'], 'gest_effectifs' => ['membres.gerer', 'geo.gerer'], 'resp_numerique' => ['systeme.gerer', 'admins.gerer', 'logs.voir']] as $role => $liste) {
            foreach ($liste as $perm) { t("$role a $perm", in_array($perm, $droits[$role] ?? [], true)); }
        }
    } catch (Throwable $e) {
        t('exception pendant les tests d\'intégration', false, get_class($e) . ' : ' . $e->getMessage() . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
    } finally {
        // Nettoyage des données de test
        foreach ($creesPay as $id) {
            $r = $bdd->query("SELECT reference FROM payments WHERE id = " . (int) $id)->fetchColumn();
            if ($r) { $bdd->exec("DELETE FROM payment_logs WHERE reference = " . $bdd->quote($r)); }
            $bdd->exec("DELETE FROM payments WHERE id = " . (int) $id);
        }
        foreach ($creesAd as $id) { $bdd->exec("DELETE FROM notifications WHERE id_ad = " . (int) $id); $bdd->exec("DELETE FROM adhesion WHERE id_ad = " . (int) $id); }
        echo "\n(données de test supprimées)\n";
    }
}

echo "\n================ RÉSULTAT : {$GLOBALS['t_ok']} réussis, {$GLOBALS['t_ko']} échecs ================\n";
if ($GLOBALS['t_ko'] > 0) { echo "Échecs :\n - " . implode("\n - ", $GLOBALS['echecs']) . "\n"; exit(1); }
exit(0);
