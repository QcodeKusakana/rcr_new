<?php
/**
 * PHASE 2 — complément de migration (à lancer APRÈS phase1_migrate.php --apply).
 *   php migrations/phase2_migrate.php            -> simulation
 *   php migrations/phase2_migrate.php --apply    -> exécute
 * Idempotent : ajoute la colonne site_menu.classe et installe le menu public
 * hiérarchique (identique à l'ancien menu) s'il n'a pas encore été personnalisé.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$apply = in_array('--apply', $argv ?? [], true);
require_once dirname(__DIR__) . '/config/database.php';
$bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
echo $apply ? "=== PHASE 2 : APPLICATION ===\n" : "=== PHASE 2 : SIMULATION ===\n";

$has = (int) $bdd->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'site_menu' AND column_name = 'classe'")->fetchColumn();
if (!$has) {
    echo ($apply ? '[OK ] ' : '[SIM] ') . "site_menu.classe\n";
    if ($apply) { $bdd->exec("ALTER TABLE site_menu ADD COLUMN classe VARCHAR(40) NOT NULL DEFAULT ''"); }
} else { echo "[ -- ] site_menu.classe existe déjà\n"; }

// Menu : on remplace uniquement le menu plat posé par la phase 1 (URL "index.php..."), jamais un menu modifié en admin.
$n = (int) $bdd->query('SELECT COUNT(*) FROM site_menu')->fetchColumn();
$plat = (int) $bdd->query("SELECT COUNT(*) FROM site_menu WHERE url LIKE 'index.php%' OR url LIKE 'adhere/adhesion.php%'")->fetchColumn();
if ($n === 0 || $plat > 0) {
    echo ($apply ? '[OK ] ' : '[SIM] ') . "menu public hiérarchique\n";
    if ($apply) {
        $bdd->exec('DELETE FROM site_menu');
        $ins = $bdd->prepare("INSERT INTO site_menu (zone, parent_id, libelle, url, ordre, classe) VALUES ('header', ?, ?, ?, ?, ?)");
        $add = function (?int $parent, string $lib, string $url, int $ordre, string $classe = '') use ($ins, $bdd): int {
            $ins->execute([$parent, $lib, $url, $ordre, $classe]);
            return (int) $bdd->lastInsertId();
        };
        $add(null, 'Accueil', '?pages=home', 1);
        $q = $add(null, 'Qui sommes-nous ?', '#', 2);
        $add($q, 'Présentation', '?pages=apropos', 1);
        $add($q, 'Chef du parti', '?pages=parti', 2);
        $o = $add(null, 'Où en sommes-nous ?', '#', 3);
        $add($o, 'Nos événements', '?pages=publication', 1);
        $add(null, 'Contact', '?pages=contact', 4);
        $add(null, 'Votre soutien', '?pages=soutenir', 5, 'support-item');
        $j = $add(null, 'Nous rejoindre', '#', 6, 'dropdown join-item');
        $add($j, 'Adhérer', './adhere/adhesion.php', 1);
        $add($j, 'Renouveler mon adhésion', '@renouveler', 2);
    }
} else { echo "[ -- ] menu déjà personnalisé, conservé\n"; }
echo $apply ? "=== TERMINÉ ===\n" : "=== Simulation terminée ===\n";
