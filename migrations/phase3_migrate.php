<?php
/**
 * PHASE 3 — complément de migration (à lancer après phase1 et phase2).
 *   php migrations/phase3_migrate.php            -> simulation
 *   php migrations/phase3_migrate.php --apply    -> exécute
 * Crée la file de notifications et ajoute les index utiles à l'administration
 * (listes paginées, filtres, tableau de bord). Idempotent.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$apply = in_array('--apply', $argv ?? [], true);
require_once dirname(__DIR__) . '/config/database.php';
$bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
echo $apply ? "=== PHASE 3 : APPLICATION ===\n" : "=== PHASE 3 : SIMULATION ===\n";

$existe = function (string $t) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'); $s->execute([$t]);
    return (int) $s->fetchColumn() > 0;
};
$indexExiste = function (string $t, string $i) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?'); $s->execute([$t, $i]);
    return (int) $s->fetchColumn() > 0;
};

// Limitation des tentatives de connexion (membres ET administrateurs) : la table doit exister,
// sinon la protection anti brute-force est silencieusement inactive.
if (!$existe('login_attempts')) {
    echo ($apply ? '[OK ] ' : '[SIM] ') . "CREATE TABLE login_attempts (migration 003)\n";
    if ($apply) {
        $sql003 = (string) @file_get_contents(__DIR__ . '/003_create_login_attempts.sql');
        if (preg_match('/CREATE TABLE.*?;/is', $sql003, $m)) { $bdd->exec($m[0]); }
        else { echo "[ ! ] 003_create_login_attempts.sql introuvable : créez la table manuellement\n"; }
    }
} else { echo "[ -- ] login_attempts existe déjà\n"; }

if (!$existe('password_resets')) {
    echo ($apply ? '[OK ] ' : '[SIM] ') . "CREATE TABLE password_resets\n";
    if ($apply) {
        $bdd->exec("CREATE TABLE password_resets (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            id_ad INT NOT NULL,
            token_hash CHAR(64) NOT NULL,
            expire_le DATETIME NOT NULL,
            utilise_le DATETIME NULL,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_reset_token (token_hash),
            KEY idx_reset_membre (id_ad, cree_le),
            KEY idx_reset_ip (ip, cree_le)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} else { echo "[ -- ] password_resets existe déjà\n"; }

if (!$existe('notifications')) {
    echo ($apply ? '[OK ] ' : '[SIM] ') . "CREATE TABLE notifications\n";
    if ($apply) {
        $bdd->exec("CREATE TABLE notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            evenement VARCHAR(40) NOT NULL,
            canal ENUM('interne','email','sms') NOT NULL,
            id_ad INT NULL,
            destinataire VARCHAR(200) NOT NULL,
            donnees TEXT NULL,
            cle_unique VARCHAR(120) NULL,
            statut ENUM('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
            cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            envoye_le DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_notif_cle (cle_unique),
            KEY idx_notif_statut (statut, canal, cree_le),
            KEY idx_notif_membre (id_ad, cree_le)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
} else { echo "[ -- ] notifications existe déjà\n"; }

if ($existe('dons')) {
    $c = (int) $bdd->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'dons' AND column_name = 'prochaine_echeance'")->fetchColumn();
    if (!$c) {
        echo ($apply ? '[OK ] ' : '[SIM] ') . "dons.prochaine_echeance\n";
        if ($apply) { $bdd->exec('ALTER TABLE dons ADD COLUMN prochaine_echeance DATE NULL'); }
    } else { echo "[ -- ] dons.prochaine_echeance existe déjà\n"; }
}

$index = [
    ['payments', 'idx_payments_membre', 'KEY idx_payments_membre (id_ad, status)'],
    ['payments', 'idx_payments_date',   'KEY idx_payments_date (created_at)'],
    ['dons',     'idx_dons_date',       'KEY idx_dons_date (created_at, status)'],
    ['dons',     'idx_dons_membre',     'KEY idx_dons_membre (id_ad)'],
    ['adhesion', 'idx_adhesion_qt',     'KEY idx_adhesion_qt (id_qt)'],
    ['adhesion', 'idx_adhesion_prov',   'KEY idx_adhesion_prov (province)'],
    ['adhesion', 'idx_adhesion_date',   'KEY idx_adhesion_date (dat_adhesion)'],
];
foreach ($index as [$t, $i, $def]) {
    if (!$existe($t) || $indexExiste($t, $i)) { continue; }
    echo ($apply ? '[OK ] ' : '[SIM] ') . "INDEX $t.$i\n";
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD $def"); }
}
echo $apply ? "=== TERMINÉ ===\n" : "=== Simulation terminée ===\n";
