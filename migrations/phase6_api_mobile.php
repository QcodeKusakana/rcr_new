<?php
/**
 * Phase 6 — API mobile : table des jetons d'accès (api_tokens).
 * Idempotente, non destructive, simulation par défaut :
 *   php migrations/phase6_api_mobile.php            (affiche ce qui sera fait)
 *   php migrations/phase6_api_mobile.php --apply    (applique)
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/bootstrap.php';

$apply = in_array('--apply', $argv ?? [], true);
$existe = false;
try { $bdd->query('SELECT 1 FROM api_tokens LIMIT 1'); $existe = true; } catch (Throwable $e) {}

/* Réglages « application mobile » (jamais d'écrasement) */
$reglages = [
    ['app_android_url', '', 'contact', "Application Android : lien de téléchargement (URL https ou chemin, ex. telechargements/rcr.apk)", 'url'],
    ['app_ios_url', '', 'contact', "Application iPhone : lien App Store / TestFlight (vide = « Bientôt »)", 'url'],
];
try {
    $chk = $bdd->prepare("SELECT COUNT(*) FROM site_reglages WHERE cle = ? AND langue = 'fr'");
    $ins = $bdd->prepare("INSERT INTO site_reglages (cle, langue, valeur, groupe, libelle, type) VALUES (?, 'fr', ?, ?, ?, ?)");
    foreach ($reglages as [$cle, $val, $grp, $lib, $type]) {
        $chk->execute([$cle]);
        if ((int) $chk->fetchColumn() > 0) { echo "réglage $cle : déjà présent.\n"; continue; }
        echo ($apply ? 'Création' : 'À créer') . " : réglage $cle\n";
        if ($apply) { $ins->execute([$cle, $val, $grp, $lib, $type]); }
    }
} catch (Throwable $e) { echo "Réglages ignorés (site_reglages absente) : " . $e->getMessage() . "\n"; }

if ($existe) { echo "api_tokens : déjà présente, rien à faire.\n"; exit(0); }
echo ($apply ? 'Création' : 'À créer') . " : table api_tokens\n";
if (!$apply) { echo "Simulation : relancez avec --apply pour appliquer.\n"; exit(0); }

$bdd->exec("CREATE TABLE api_tokens (
    id_tok INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_ad INT(11) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    appareil VARCHAR(120) DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    derniere_utilisation DATETIME DEFAULT NULL,
    expire_le DATETIME NOT NULL,
    revoque TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_tok),
    UNIQUE KEY uq_api_token_hash (token_hash),
    KEY idx_api_tokens_membre (id_ad, revoque),
    KEY idx_api_tokens_expire (expire_le),
    CONSTRAINT fk_api_tokens_membre FOREIGN KEY (id_ad) REFERENCES adhesion (id_ad) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK.\n";
