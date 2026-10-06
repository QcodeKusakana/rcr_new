<?php
/**
 * PHASE 4 — index de performance (à lancer après les migrations 1, 2 et 3).
 *   php migrations/phase4_migrate.php            -> simulation
 *   php migrations/phase4_migrate.php --apply    -> exécute
 * Idempotent. Ne modifie aucune donnée : uniquement des index, pour tenir le trafic annoncé
 * (≈ 12 000 visiteurs/jour) sans balayage complet des tables à chaque page.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$apply = in_array('--apply', $argv ?? [], true);
require_once dirname(__DIR__) . '/config/database.php';
$bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
echo $apply ? "=== PHASE 4 : APPLICATION ===\n" : "=== PHASE 4 : SIMULATION ===\n";

$existeTable = fn(string $t) => (int) $bdd->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = " . $bdd->quote($t))->fetchColumn() > 0;
$existeIdx = fn(string $t, string $i) => (int) $bdd->query("SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = " . $bdd->quote($t) . " AND index_name = " . $bdd->quote($i))->fetchColumn() > 0;
$existeCol = fn(string $t, string $c) => (int) $bdd->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = " . $bdd->quote($t) . " AND column_name = " . $bdd->quote($c))->fetchColumn() > 0;

$index = [
    // [table, nom, définition, colonne exigée]
    ['horsline',   'idx_horsline_ip',      'KEY idx_horsline_ip (ip_user)',            'ip_user'],
    ['horsline',   'idx_horsline_temps',   'KEY idx_horsline_temps (temps)',           'temps'],
    ['onlines',    'idx_onlines_usr',      'KEY idx_onlines_usr (id_usr)',             'id_usr'],
    ['onlines',    'idx_onlines_temps',    'KEY idx_onlines_temps (temps)',            'temps'],
    ['activite',   'idx_activite_etat',    'KEY idx_activite_etat (etat_modifier, date_pub)', 'etat_modifier'],
    ['activite',   'idx_activite_categ',   'KEY idx_activite_categ (categorie, etat_modifier)', 'categorie'],
    ['commentaire','idx_comm_act',         'KEY idx_comm_act (id_act)',                'id_act'],
    ['territoires','idx_terr_prov',        'KEY idx_terr_prov (id_p)',                 'id_p'],
    ['secteurs',   'idx_sect_terr',        'KEY idx_sect_terr (id_tr)',                'id_tr'],
    ['adhesion',   'idx_adhesion_mail',    'KEY idx_adhesion_mail (mail)',             'mail'],
    ['adhesion',   'idx_adhesion_token',   'KEY idx_adhesion_token (payment_token)',   'payment_token'],
    ['payments',   'idx_payments_order',   'KEY idx_payments_order (order_number)',    'order_number'],
    ['dons',       'idx_dons_order',       'KEY idx_dons_order (order_number)',        'order_number'],
    ['dons',       'idx_dons_echeance',    'KEY idx_dons_echeance (type_don, prochaine_echeance)', 'prochaine_echeance'],
    ['audit_logs', 'idx_audit_cible',      'KEY idx_audit_cible (cible, cible_id)',    'cible'],
    ['notifications', 'idx_notif_evt',     'KEY idx_notif_evt (evenement, statut)',    'evenement'],
];
$n = 0;
foreach ($index as [$t, $i, $def, $col]) {
    if (!$existeTable($t) || !$existeCol($t, $col)) { echo "[ -- ] $t.$col absent, ignoré\n"; continue; }
    if ($existeIdx($t, $i)) { echo "[ -- ] $t.$i existe déjà\n"; continue; }
    echo ($apply ? '[OK ] ' : '[SIM] ') . "INDEX $t.$i\n";
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD $def"); }
    $n++;
}
echo "$n index " . ($apply ? 'créés' : 'à créer') . "\n";
echo $apply ? "=== TERMINÉ ===\n" : "=== Simulation terminée ===\n";
