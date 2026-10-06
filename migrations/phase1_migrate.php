<?php
/**
 * PHASE 1 — Migration de la base RCR.
 *
 * Usage (ligne de commande uniquement) :
 *   php migrations/phase1_migrate.php            -> simulation (ne touche à rien)
 *   php migrations/phase1_migrate.php --apply    -> exécute la migration
 *
 * Ce que fait le script (dans l'ordre) :
 *  1. Sauvegarde JSON des tables qui vont être vidées (storage/backups/).
 *  2. Convertit provinces/territoires/secteurs en utf8mb4 SANS toucher aux données.
 *  3. Vide les données de test (tout SAUF provinces, territoires, secteurs, et la
 *     structure des grades/qualités/cotisation, remplacée à l'étape 5).
 *  4. Crée les nouvelles tables : contenus (site_blocs, site_reglages, site_menu),
 *     RBAC (roles, permissions, role_permissions), audit_logs, payment_logs,
 *     grades_historique.
 *  5. Installe les nouveaux tarifs (valeurs stockées en base, modifiables en admin).
 *  6. Étend payments / dons / adhesion (statuts, type de transaction, compte membre).
 *  7. Importe les textes existants (seed_contenu.json) dans la base.
 *
 * Le script peut être relancé : chaque étape vérifie l'existant (idempotent).
 * FAITES UNE SAUVEGARDE COMPLÈTE (mysqldump) AVANT --apply.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Accès interdit.');
}

$apply = in_array('--apply', $argv ?? [], true);
$root  = dirname(__DIR__);

require_once $root . '/config/database.php';

try {
    $bdd = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    fwrite(STDERR, "Connexion impossible : " . $e->getMessage() . "\n");
    exit(1);
}

function say(string $m): void { echo $m . "\n"; }

function run(PDO $bdd, bool $apply, string $label, string $sql, array $params = []): void
{
    say(($apply ? '[OK ] ' : '[SIM] ') . $label);
    if ($apply) {
        $bdd->prepare($sql)->execute($params);
    }
}

function table_exists(PDO $bdd, string $t): bool
{
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $s->execute([$t]);
    return (int) $s->fetchColumn() > 0;
}

function column_exists(PDO $bdd, string $t, string $c): bool
{
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $s->execute([$t, $c]);
    return (int) $s->fetchColumn() > 0;
}

function add_column(PDO $bdd, bool $apply, string $t, string $c, string $def): void
{
    if (!table_exists($bdd, $t)) { say("[ -- ] table $t absente, colonne $c ignorée"); return; }
    if (column_exists($bdd, $t, $c)) { say("[ -- ] $t.$c existe déjà"); return; }
    say(($apply ? '[OK ] ' : '[SIM] ') . "ALTER $t ADD $c");
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD COLUMN `$c` $def"); }
}

function index_exists(PDO $bdd, string $t, string $i): bool
{
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?');
    $s->execute([$t, $i]);
    return (int) $s->fetchColumn() > 0;
}

function add_index(PDO $bdd, bool $apply, string $t, string $i, string $def): void
{
    if (!table_exists($bdd, $t) || index_exists($bdd, $t, $i)) { return; }
    say(($apply ? '[OK ] ' : '[SIM] ') . "INDEX $t.$i");
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD $def"); }
}

say($apply ? "=== MIGRATION PHASE 1 : APPLICATION ===" : "=== MIGRATION PHASE 1 : SIMULATION (aucune écriture) ===");

/* ---------------------------------------------------------------- GARDE-FOU : base déjà migrée
 * Si la base a déjà la structure cible (tables site_*, colonne adhesion.statut) — par exemple après
 * l'import de rcr_nouvelle_base.sql, ou après un premier passage de ce script — on NE PURGE RIEN et on
 * ne touche pas au barème : relancer ce script ne peut plus effacer des membres, des paiements ni des
 * administrateurs. Il se contente de compléter ce qui manque (tables, colonnes, index, contenus). */
$dejaMigre = table_exists($bdd, 'site_blocs') && column_exists($bdd, 'adhesion', 'statut');
if ($dejaMigre) {
    say('[ -- ] base déjà migrée : sauvegarde, purge et réécriture du barème IGNORÉES (aucune donnée supprimée)');
}

/* ------------------------------------------------------------------ 1. SAUVEGARDE */
$aVider = [
    'adhesion', 'payments', 'dons', 'historiquepaiement', 'admin', 'activite',
    'categorie', 'sous_categorie', 'commentaire', 'likes', 'dislike', 'vu',
    'onlines', 'horsline', 'parner', 'partenaire', 'equipes', 'encadreur', 'operateurs',
];
$aVider = array_values(array_filter($aVider, fn($t) => table_exists($bdd, $t)));
if ($dejaMigre) { $aVider = []; }

$backupDir = $root . '/storage/backups';
if ($dejaMigre) {
    // rien à sauvegarder : aucune donnée ne sera supprimée
} elseif ($apply) {
    if (!is_dir($backupDir)) { mkdir($backupDir, 0750, true); }
    $dump = [];
    foreach (array_merge($aVider, ['provinces', 'territoires', 'secteurs', 'grades', 'qualites', 'cotisation']) as $t) {
        if (table_exists($bdd, $t)) {
            $dump[$t] = $bdd->query("SELECT * FROM `$t`")->fetchAll();
        }
    }
    $file = $backupDir . '/phase1_avant_' . date('Ymd_His') . '.json';
    file_put_contents($file, json_encode($dump, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    chmod($file, 0640);
    say("[OK ] sauvegarde JSON : $file");
} else {
    say('[SIM] sauvegarde JSON des tables : ' . implode(', ', $aVider));
}

/* ---------------------------------------------------- 2. utf8mb4 sur la géographie */
foreach (['provinces', 'territoires', 'secteurs'] as $t) {
    if (table_exists($bdd, $t)) {
        say(($apply ? '[OK ] ' : '[SIM] ') . "CONVERT $t en utf8mb4 (données conservées)");
        if ($apply) { $bdd->exec("ALTER TABLE `$t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); }
    }
}

/* ------------------------------------------------------------ 3. PURGE DES DONNÉES */
if ($apply) { $bdd->exec('SET FOREIGN_KEY_CHECKS = 0'); }
foreach ($aVider as $t) {
    say(($apply ? '[OK ] ' : '[SIM] ') . "TRUNCATE $t");
    if ($apply) { $bdd->exec("TRUNCATE TABLE `$t`"); }
}
if ($apply) { $bdd->exec('SET FOREIGN_KEY_CHECKS = 1'); }

/* --------------------------------------------------------- 4. NOUVELLES TABLES */
$CS = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

$tables = [
'admin' => "CREATE TABLE IF NOT EXISTS admin (
    id_adm INT NOT NULL AUTO_INCREMENT,
    pseudo VARCHAR(50) NOT NULL,
    mail VARCHAR(120) NOT NULL,
    password TEXT NOT NULL,
    fonction VARCHAR(50) DEFAULT NULL,
    niveau INT NOT NULL DEFAULT 0,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    confirmer INT NOT NULL DEFAULT 0,
    id_role INT UNSIGNED NULL,
    PRIMARY KEY (id_adm),
    KEY idx_admin_pseudo (pseudo)
) $CS",
'site_blocs' => "CREATE TABLE IF NOT EXISTS site_blocs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    page VARCHAR(80) NOT NULL,
    cle VARCHAR(80) NOT NULL,
    langue CHAR(2) NOT NULL DEFAULT 'fr',
    titre VARCHAR(255) NOT NULL DEFAULT '',
    contenu MEDIUMTEXT NOT NULL,
    type ENUM('texte','html') NOT NULL DEFAULT 'html',
    ordre INT NOT NULL DEFAULT 0,
    meta TEXT NULL,
    statut ENUM('brouillon','publie','archive') NOT NULL DEFAULT 'publie',
    modifie_par INT NULL,
    modifie_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_bloc (page, cle, langue),
    KEY idx_page (page, langue, statut, ordre)
) $CS",

'site_reglages' => "CREATE TABLE IF NOT EXISTS site_reglages (
    cle VARCHAR(80) NOT NULL,
    langue CHAR(2) NOT NULL DEFAULT 'fr',
    valeur TEXT NOT NULL,
    groupe VARCHAR(40) NOT NULL DEFAULT 'general',
    libelle VARCHAR(160) NOT NULL DEFAULT '',
    type ENUM('texte','html','nombre','url') NOT NULL DEFAULT 'texte',
    modifie_par INT NULL,
    modifie_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (cle, langue),
    KEY idx_groupe (groupe)
) $CS",

'site_menu' => "CREATE TABLE IF NOT EXISTS site_menu (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    zone ENUM('header','footer') NOT NULL DEFAULT 'header',
    parent_id INT UNSIGNED NULL,
    langue CHAR(2) NOT NULL DEFAULT 'fr',
    libelle VARCHAR(120) NOT NULL,
    url VARCHAR(255) NOT NULL,
    ordre INT NOT NULL DEFAULT 0,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_zone (zone, langue, actif, ordre)
) $CS",

'roles' => "CREATE TABLE IF NOT EXISTS roles (
    id_role INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(40) NOT NULL,
    libelle VARCHAR(120) NOT NULL,
    fonction VARCHAR(160) NOT NULL DEFAULT '',
    PRIMARY KEY (id_role),
    UNIQUE KEY uq_role_code (code)
) $CS",

'permissions' => "CREATE TABLE IF NOT EXISTS permissions (
    id_perm INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(60) NOT NULL,
    libelle VARCHAR(160) NOT NULL,
    PRIMARY KEY (id_perm),
    UNIQUE KEY uq_perm_code (code)
) $CS",

'role_permissions' => "CREATE TABLE IF NOT EXISTS role_permissions (
    id_role INT UNSIGNED NOT NULL,
    id_perm INT UNSIGNED NOT NULL,
    PRIMARY KEY (id_role, id_perm)
) $CS",

'audit_logs' => "CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_adm INT NULL,
    action VARCHAR(60) NOT NULL,
    cible VARCHAR(80) NOT NULL DEFAULT '',
    cible_id VARCHAR(60) NOT NULL DEFAULT '',
    detail TEXT NULL,
    ip VARCHAR(45) NOT NULL DEFAULT '',
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_action (action, cree_le),
    KEY idx_adm (id_adm, cree_le)
) $CS",

'payment_logs' => "CREATE TABLE IF NOT EXISTS payment_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    type_transaction ENUM('adhesion','cotisation','don') NOT NULL,
    ref_id INT UNSIGNED NULL,
    reference VARCHAR(100) NOT NULL DEFAULT '',
    evenement VARCHAR(40) NOT NULL,
    statut_avant VARCHAR(20) NULL,
    statut_apres VARCHAR(20) NULL,
    payload TEXT NULL,
    ip VARCHAR(45) NOT NULL DEFAULT '',
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ref (reference),
    KEY idx_evt (evenement, cree_le)
) $CS",

'grades_historique' => "CREATE TABLE IF NOT EXISTS grades_historique (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_gd INT NOT NULL,
    ancien_prix DECIMAL(10,2) NULL,
    nouveau_prix DECIMAL(10,2) NULL,
    ancien_actif TINYINT(1) NULL,
    nouveau_actif TINYINT(1) NULL,
    id_adm INT NULL,
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_gd (id_gd)
) $CS",
];

foreach ($tables as $nom => $sql) {
    if (table_exists($bdd, $nom)) { say("[ -- ] table $nom existe déjà"); continue; }
    say(($apply ? '[OK ] ' : '[SIM] ') . "CREATE TABLE $nom");
    if ($apply) { $bdd->exec($sql); }
}

/* ----------------------------------------------------------------- 5. TARIFS */
// grades : prix = tarif MENSUEL en USD. On ajoute ordre/actif, on désactive
// (sans supprimer) les anciennes lignes, puis on insère le nouveau barème.
add_column($bdd, $apply, 'grades', 'actif', 'TINYINT(1) NOT NULL DEFAULT 1');
add_column($bdd, $apply, 'grades', 'ordre', 'INT NOT NULL DEFAULT 0');
add_column($bdd, $apply, 'grades', 'ancien', 'TINYINT(1) NOT NULL DEFAULT 0');
add_column($bdd, $apply, 'cotisation', 'mois', 'INT NOT NULL DEFAULT 1');
add_column($bdd, $apply, 'cotisation', 'actif', 'TINYINT(1) NOT NULL DEFAULT 1');

if ($apply) {
    $bdd->exec('ALTER TABLE grades MODIFY prix DECIMAL(10,2) NOT NULL DEFAULT 0'); // FLOAT -> DECIMAL (argent)
    // anciennes lignes : conservées pour l'historique mais désactivées
    if (!$dejaMigre) {
        $bdd->exec("UPDATE grades SET actif = 0, ancien = 1 WHERE ancien = 0 AND id_gd <= 18");
    }

    $nouveaux = [
        // [id_qt, nom, prix mensuel USD, ordre]
        [1, 'Diamant Puissant', 200, 1], [1, 'Super Diamant', 100, 2], [1, 'Diamant Force', 50, 3], [1, 'Diamant', 25, 4],
        [2, 'Super Diamant', 100, 1],    [2, 'Diamant Force', 50, 2],  [2, 'Diamant', 25, 3],       [2, 'Platine', 20, 4],
        [3, 'Diamant', 25, 1],           [3, 'Platine', 20, 2],        [3, 'Or', 10, 3],            [3, 'Argent', 5, 4],
    ];
    $chk = $bdd->prepare('SELECT id_gd FROM grades WHERE id_qt = ? AND nom_gd = ? AND ancien = 0 LIMIT 1');
    $ins = $bdd->prepare('INSERT INTO grades (nom_gd, prix, id_qt, actif, ordre, ancien) VALUES (?, ?, ?, 1, ?, 0)');
    foreach ($nouveaux as [$qt, $nom, $prix, $ordre]) {
        $chk->execute([$qt, $nom]);
        if (!$chk->fetchColumn()) { $ins->execute([$nom, $prix, $qt, $ordre]); }
    }
    say('[OK ] barème : 12 grades (4 par catégorie)');

    // périodes : multiplicateur du tarif mensuel (modifiable en admin)
    if (!$dejaMigre) { $bdd->exec('DELETE FROM cotisation'); }
    $per = $bdd->prepare('INSERT INTO cotisation (nom_cot, jours, prix, mois, actif) VALUES (?, ?, 0, ?, 1)');
    if ((int) $bdd->query('SELECT COUNT(*) FROM cotisation')->fetchColumn() === 0) {
        foreach ([['Mensuelle', 30, 1], ['Trimestrielle', 90, 3], ['Semestrielle', 180, 6], ['Annuelle', 365, 12]] as $p) {
            $per->execute($p);
        }
    }
    say('[OK ] périodes : mensuelle=x1, trimestrielle=x3, semestrielle=x6, annuelle=x12');
} else {
    say('[SIM] barème : 12 grades actifs ; anciens grades désactivés (conservés)');
    say('[SIM] périodes : x1 / x3 / x6 / x12');
}

/* ---------------------------------------- 6. EXTENSIONS payments / dons / adhesion */
$statuts = "ENUM('pending','processing','paid','failed','cancelled','expired') NOT NULL DEFAULT 'pending'";

if ($apply) {
    // Les anciennes lignes 'expired_pending' n'existent plus (tables vidées) ; on aligne l'enum.
    $bdd->exec("ALTER TABLE payments MODIFY status $statuts");
    $bdd->exec("ALTER TABLE dons MODIFY status $statuts");
    say('[OK ] statuts payments/dons : pending, processing, paid, failed, cancelled, expired');
} else {
    say('[SIM] statuts payments/dons étendus (6 statuts)');
}

add_column($bdd, $apply, 'payments', 'type_transaction', "ENUM('adhesion','cotisation') NOT NULL DEFAULT 'adhesion'");
add_column($bdd, $apply, 'payments', 'id_cot', 'INT NULL');
add_column($bdd, $apply, 'payments', 'canal', "ENUM('mobile_money','carte') NOT NULL DEFAULT 'mobile_money'");
add_column($bdd, $apply, 'payments', 'order_number', 'VARCHAR(80) NULL');
add_column($bdd, $apply, 'payments', 'provider_reference', 'VARCHAR(80) NULL');
add_column($bdd, $apply, 'payments', 'periode_debut', 'DATE NULL');
add_column($bdd, $apply, 'payments', 'periode_fin', 'DATE NULL');
add_column($bdd, $apply, 'payments', 'verifie_le', 'DATETIME NULL');
add_column($bdd, $apply, 'payments', 'maj_le', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
add_index($bdd, $apply, 'payments', 'uq_payments_reference', 'UNIQUE KEY uq_payments_reference (reference)');
add_index($bdd, $apply, 'payments', 'idx_payments_status', 'KEY idx_payments_status (status, created_at)');

add_column($bdd, $apply, 'dons', 'type_don', "ENUM('ponctuel','regulier') NOT NULL DEFAULT 'ponctuel'");
add_column($bdd, $apply, 'dons', 'frequence', "ENUM('mensuel','trimestriel','semestriel','annuel') NULL");
add_column($bdd, $apply, 'dons', 'id_ad', 'INT NULL');
add_column($bdd, $apply, 'dons', 'canal', "ENUM('mobile_money','carte') NOT NULL DEFAULT 'mobile_money'");
add_column($bdd, $apply, 'dons', 'order_number', 'VARCHAR(80) NULL');
add_column($bdd, $apply, 'dons', 'provider_reference', 'VARCHAR(80) NULL');
add_column($bdd, $apply, 'dons', 'verifie_le', 'DATETIME NULL');
foreach (['postnom' => 'VARCHAR(100)', 'prenom' => 'VARCHAR(100)', 'nationalite' => 'VARCHAR(100)',
          'ville' => 'VARCHAR(120)', 'adresse' => 'TEXT'] as $c => $d) {
    add_column($bdd, $apply, 'dons', $c, $d . ' NULL');
}
add_column($bdd, $apply, 'dons', 'sexe', "ENUM('M','F') NULL");
add_column($bdd, $apply, 'dons', 'province', 'INT NULL');
add_column($bdd, $apply, 'dons', 'territoire', 'INT NULL');
add_column($bdd, $apply, 'dons', 'maj_le', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');
add_column($bdd, $apply, 'dons', 'postnom', 'VARCHAR(100) NULL');
add_column($bdd, $apply, 'dons', 'prenom', 'VARCHAR(100) NULL');
add_column($bdd, $apply, 'dons', 'id_province', 'INT NULL');
add_column($bdd, $apply, 'dons', 'ville', 'VARCHAR(120) NULL');
add_column($bdd, $apply, 'dons', 'adresse', 'VARCHAR(255) NULL');
add_column($bdd, $apply, 'dons', 'code_membre', 'VARCHAR(50) NULL');
add_index($bdd, $apply, 'dons', 'uq_dons_reference', 'UNIQUE KEY uq_dons_reference (reference)');

// Compte membre (la table adhesion reste la fiche membre)
add_column($bdd, $apply, 'adhesion', 'sexe', "ENUM('M','F') NULL");
add_column($bdd, $apply, 'adhesion', 'ville', 'VARCHAR(120) NULL');
add_column($bdd, $apply, 'adhesion', 'statut', "ENUM('en_attente','actif','expire','suspendu') NOT NULL DEFAULT 'en_attente'");
add_column($bdd, $apply, 'adhesion', 'id_cot', 'INT NULL');
add_column($bdd, $apply, 'adhesion', 'date_echeance', 'DATE NULL');
add_column($bdd, $apply, 'adhesion', 'password_hash', 'VARCHAR(255) NULL');
add_column($bdd, $apply, 'adhesion', 'derniere_connexion', 'DATETIME NULL');
add_index($bdd, $apply, 'adhesion', 'idx_adhesion_statut', 'KEY idx_adhesion_statut (statut, date_echeance)');
add_index($bdd, $apply, 'adhesion', 'uq_adhesion_codes', 'UNIQUE KEY uq_adhesion_codes (codes)');

// Admin : rattachement au rôle RBAC (la colonne niveau est conservée)
add_column($bdd, $apply, 'admin', 'id_role', 'INT UNSIGNED NULL');
if ($apply) { $bdd->exec('ALTER TABLE admin MODIFY mail VARCHAR(120) NOT NULL'); }

/* ----------------------------------------------------------- RÔLES / PERMISSIONS */
$perms = [
    'dashboard.voir' => 'Voir le tableau de bord',
    'contenu.gerer' => 'Gérer les pages, textes, actualités, événements',
    'media.gerer' => 'Gérer les médias',
    'membres.voir' => 'Consulter les membres',
    'membres.gerer' => 'Créer / modifier les membres et adhésions',
    'geo.gerer' => 'Gérer provinces, territoires, secteurs',
    'tarifs.gerer' => 'Modifier les tarifs de cotisation',
    'paiements.voir' => 'Voir cotisations, dons et paiements',
    'paiements.valider' => 'Valider / corriger manuellement un paiement',
    'admins.gerer' => 'Gérer les administrateurs et les rôles',
    'systeme.gerer' => 'Paramètres techniques, sécurité, sauvegardes',
    'logs.voir' => 'Consulter les journaux',
];
$roles = [
    'admin_principal' => ['Administrateur principal', 'Président du Rassemblement', array_keys($perms)],
    'resp_publications' => ['Responsable publications', 'Rapporteur Général', ['dashboard.voir', 'contenu.gerer', 'media.gerer']],
    'gest_effectifs' => ['Gestionnaire des effectifs', 'Secrétaire Exécutif Spécial chargé des Affaires Intérieures',
        ['dashboard.voir', 'membres.voir', 'membres.gerer', 'geo.gerer', 'paiements.voir']],
    'resp_numerique' => ['Responsable numérique', 'Secrétaire Permanent chargé du Numérique',
        ['dashboard.voir', 'systeme.gerer', 'admins.gerer', 'logs.voir', 'paiements.voir', 'tarifs.gerer']],
];

if ($apply) {
    $ip = $bdd->prepare('INSERT IGNORE INTO permissions (code, libelle) VALUES (?, ?)');
    foreach ($perms as $c => $l) { $ip->execute([$c, $l]); }

    $ir = $bdd->prepare('INSERT IGNORE INTO roles (code, libelle, fonction) VALUES (?, ?, ?)');
    $gr = $bdd->prepare('SELECT id_role FROM roles WHERE code = ?');
    $gp = $bdd->prepare('SELECT id_perm FROM permissions WHERE code = ?');
    $rp = $bdd->prepare('INSERT IGNORE INTO role_permissions (id_role, id_perm) VALUES (?, ?)');
    foreach ($roles as $code => [$lib, $fn, $liste]) {
        $ir->execute([$code, $lib, $fn]);
        $gr->execute([$code]);
        $idr = (int) $gr->fetchColumn();
        foreach ($liste as $pc) {
            $gp->execute([$pc]);
            $rp->execute([$idr, (int) $gp->fetchColumn()]);
        }
    }
    say('[OK ] RBAC : 4 rôles et ' . count($perms) . ' permissions');
} else {
    say('[SIM] RBAC : 4 rôles et ' . count($perms) . ' permissions');
}

/* ------------------------------------------------------------- 7. CONTENUS (BDD) */
$seedFile = __DIR__ . '/seed_contenu.json';
$seed = is_file($seedFile) ? json_decode(file_get_contents($seedFile), true) : null;
if (!is_array($seed)) {
    say('[ ! ] seed_contenu.json introuvable ou invalide : contenus non importés');
} elseif ($apply) {
    $b = $bdd->prepare('INSERT INTO site_blocs (page, cle, langue, titre, contenu, type, ordre, meta, statut)
                        VALUES (?, ?, \'fr\', ?, ?, ?, ?, ?, \'publie\')
                        ON DUPLICATE KEY UPDATE id = id'); // jamais d'écrasement d'un texte déjà modifié en admin
    foreach ($seed['blocs'] as $x) {
        $b->execute([$x['page'], $x['cle'], $x['titre'], $x['contenu'], $x['type'], $x['ordre'], $x['meta']]);
    }
    $r = $bdd->prepare('INSERT INTO site_reglages (cle, langue, valeur, groupe, libelle, type)
                        VALUES (?, \'fr\', ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE cle = cle');
    foreach ($seed['reglages'] as $x) {
        $r->execute([$x['cle'], $x['valeur'], $x['groupe'], $x['libelle'], $x['type']]);
    }
    say('[OK ] contenus importés : ' . count($seed['blocs']) . ' blocs, ' . count($seed['reglages']) . ' réglages');
} else {
    say('[SIM] contenus à importer : ' . count($seed['blocs']) . ' blocs, ' . count($seed['reglages']) . ' réglages');
}

// Menu public de départ (modifiable en admin)
if ($apply && (int) $bdd->query('SELECT COUNT(*) FROM site_menu')->fetchColumn() === 0) {
    $m = $bdd->prepare('INSERT INTO site_menu (zone, libelle, url, ordre) VALUES (\'header\', ?, ?, ?)');
    foreach ([
        ['Accueil', 'index.php', 1], ['Qui sommes-nous', '?pages=apropos', 2], ['Adhérer', 'adhere/adhesion.php', 3],
        ['Soutenir', '?pages=soutenir', 4], ['Contact', '?pages=contact', 5],
    ] as $x) { $m->execute($x); }
    say('[OK ] menu public initial');
}

if ($apply) {
    say("");
    say("/!\\ La table admin a été vidée : créez maintenant l'administrateur principal :");
    say("    php migrations/create_admin.php");
}
say($apply ? "=== TERMINÉ ===" : "=== Simulation terminée. Relancez avec --apply après sauvegarde. ===");
