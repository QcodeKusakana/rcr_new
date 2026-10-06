<?php
/**
 * PHASE 5 — audit final : cohérence base / code, contenus administrables, messages de contact.
 *   php migrations/phase5_migrate.php            -> simulation (aucune écriture)
 *   php migrations/phase5_migrate.php --apply    -> exécute
 *
 * Idempotent et NON destructif : aucune table ni colonne n'est supprimée, aucune donnée n'est effacée,
 * aucun texte déjà modifié en administration n'est écrasé (insertion uniquement si la clé est absente).
 * Une sauvegarde complète est recommandée avant --apply : php tools/backup.php
 *
 * Contenu :
 *  1. Colonnes utilisées par le code mais absentes des anciennes bases (commentaire, likes, dislike, sous_categorie).
 *  2. Table messages_contact (messages du formulaire de contact, consultables en administration).
 *  3. Réglages de contenu manquants (coordonnées complètes, réseaux sociaux, textes de pages, pied de page).
 *  4. Menu du pied de page (site_menu, zone footer) — reprend les liens actuellement codés en dur.
 *  5. Encodage : toutes les tables en utf8mb4 / utf8mb4_unicode_ci (conversion sans perte).
 *  6. Index complémentaires.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$apply = in_array('--apply', $argv ?? [], true);
require_once dirname(__DIR__) . '/includes/db.php';
$bdd = rcr_db_connect();
$bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$p = fn(string $m) => print(($apply ? '[OK ] ' : '[SIM] ') . $m . "\n");
$skip = fn(string $m) => print("[ -- ] $m\n");
echo $apply ? "=== PHASE 5 : APPLICATION ===\n" : "=== PHASE 5 : SIMULATION (aucune écriture) ===\n";

$tableExiste = function (string $t) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
    $s->execute([$t]);
    return (int) $s->fetchColumn() > 0;
};
$colExiste = function (string $t, string $c) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $s->execute([$t, $c]);
    return (int) $s->fetchColumn() > 0;
};
$idxExiste = function (string $t, string $i) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?');
    $s->execute([$t, $i]);
    return (int) $s->fetchColumn() > 0;
};
$addCol = function (string $t, string $c, string $def) use ($bdd, $apply, $p, $skip, $tableExiste, $colExiste): void {
    if (!$tableExiste($t)) { $skip("table $t absente ($c ignorée)"); return; }
    if ($colExiste($t, $c)) { $skip("$t.$c existe déjà"); return; }
    $p("ALTER $t ADD $c");
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD COLUMN `$c` $def"); }
};
$addIdx = function (string $t, string $i, string $def) use ($bdd, $apply, $p, $tableExiste, $idxExiste): void {
    if (!$tableExiste($t) || $idxExiste($t, $i)) { return; }
    $p("INDEX $t.$i");
    if ($apply) { $bdd->exec("ALTER TABLE `$t` ADD $def"); }
};
$CS = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

/* 1. Colonnes attendues par le code ------------------------------------------------------- */
$addCol('commentaire', 'pseudo', "VARCHAR(60) NOT NULL DEFAULT ''");
$addCol('commentaire', 'commentaire', 'TEXT NULL');
$addCol('commentaire', 'date_pub', 'DATETIME NULL DEFAULT CURRENT_TIMESTAMP');
$addCol('likes', 'ip', "VARCHAR(45) NOT NULL DEFAULT ''");
$addCol('dislike', 'ip', "VARCHAR(45) NOT NULL DEFAULT ''");
$addCol('sous_categorie', 'id_cat', 'INT NULL');
$addCol('vu', 'ip', "VARCHAR(45) NOT NULL DEFAULT ''");

/* 2. Messages du formulaire de contact ---------------------------------------------------- */
if (!$tableExiste('messages_contact')) {
    $p('CREATE TABLE messages_contact');
    if ($apply) {
        $bdd->exec("CREATE TABLE messages_contact (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(120) NOT NULL,
            email VARCHAR(150) NOT NULL,
            objet VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            ip VARCHAR(45) NOT NULL DEFAULT '',
            statut ENUM('nouveau','lu','traite','archive') NOT NULL DEFAULT 'nouveau',
            traite_par INT NULL,
            cree_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            maj_le TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_msg_statut (statut, cree_le)
        ) $CS");
    }
} else { $skip('messages_contact existe déjà'); }

/* 3. Réglages de contenu manquants (jamais d'écrasement) ---------------------------------- */
$reglages = [
    // [clé, valeur par défaut = texte actuellement codé en dur, groupe, libellé, type]
    ['contact_adresse_complete', "03, Avenue Olongi,\nQuartier Mama-Yemo,\nNgaliema - Kinshasa", 'contact', 'Adresse complète (pied de page, page contact)', 'texte'],
    ['contact_telephone3', '', 'contact', 'Téléphone 3 (page contact)', 'texte'],
    ['reseau_instagram', '', 'contact', 'Lien Instagram', 'url'],
    ['reseau_tiktok', '', 'contact', 'Lien TikTok', 'url'],
    ['reseau_linkedin', '', 'contact', 'Lien LinkedIn', 'url'],
    ['footer_contact_intro', "Une question, une suggestion ou besoin d'informations ?", 'contact', 'Pied de page : texte « Contactez-nous »', 'texte'],
    ['contact_titre', 'Contactez-nous', 'pages', 'Page contact : titre', 'texte'],
    ['contact_intro', 'Retrouvez ici nos coordonnées et un formulaire pour nous écrire directement.', 'pages', 'Page contact : introduction', 'texte'],
    ['soutenir_titre', 'Soutenir le RCR', 'pages', 'Page « Je soutiens » : titre', 'texte'],
    ['evenements_titre', 'Nos événements', 'pages', 'Page actualités : titre', 'texte'],
    ['evenements_intro', 'Découvrez les dernières activités, conférences et événements organisés par le RCR.', 'pages', 'Page actualités : introduction', 'texte'],
    ['seo_image', '', 'seo', 'Image de partage par défaut (URL absolue)', 'url'],
    ['equipe_surtitre', 'Notre équipe', 'accueil', 'Accueil : surtitre de la section équipe', 'texte'],
    ['equipe_titre', 'Les membres du RCR', 'accueil', 'Accueil : titre de la section équipe', 'texte'],
    ['partenaires_surtitre', 'Ils nous soutiennent', 'accueil', 'Accueil : surtitre de la section partenaires', 'texte'],
    ['partenaires_titre', 'Nos partenaires', 'accueil', 'Accueil : titre de la section partenaires', 'texte'],
    ['partenaires_intro', 'Les organisations et personnalités qui accompagnent l’action du Rassemblement des Chrétiens Républicains.', 'accueil', 'Accueil : introduction de la section partenaires', 'texte'],
    ['equipe_intro', 'Découvrez les responsables et membres du Rassemblement des Chrétiens Républicains.', 'accueil', 'Accueil : introduction de la section équipe', 'texte'],
];
if ($tableExiste('site_reglages')) {
    $chk = $bdd->prepare("SELECT COUNT(*) FROM site_reglages WHERE cle = ? AND langue = 'fr'");
    $ins = $bdd->prepare("INSERT INTO site_reglages (cle, langue, valeur, groupe, libelle, type) VALUES (?, 'fr', ?, ?, ?, ?)");
    foreach ($reglages as [$cle, $val, $grp, $lib, $type]) {
        $chk->execute([$cle]);
        if ((int) $chk->fetchColumn() > 0) { $skip("réglage $cle existe déjà"); continue; }
        $p("réglage $cle");
        if ($apply) { $ins->execute([$cle, $val, $grp, $lib, $type]); }
    }
}

/* 4. Menu du pied de page ---------------------------------------------------------------- */
if ($tableExiste('site_menu')) {
    $n = (int) $bdd->query("SELECT COUNT(*) FROM site_menu WHERE zone = 'footer'")->fetchColumn();
    if ($n === 0) {
        $p('menu du pied de page (2 colonnes)');
        if ($apply) {
            $hasClasse = $colExiste('site_menu', 'classe');
            $ins = $bdd->prepare("INSERT INTO site_menu (zone, parent_id, langue, libelle, url, ordre, actif) VALUES ('footer', ?, 'fr', ?, ?, ?, 1)");
            $colonnes = [
                'Menu' => [['Accueil', '?pages=home'], ['Qui sommes-nous', '?pages=apropos'], ['Nos événements', '?pages=publication'],
                           ['Mot du Président', '?pages=parti'], ['Nos Idées Forces', '?pages=home#textes-fondamentaux'], ['Soutenir', '?pages=soutenir']],
                'Nous rejoindre' => [["J'adhère", './adhere/adhesion.php'], ['Je renouvelle', '@renouveler'], ['Je soutiens', '?pages=soutenir']],
            ];
            $o = 1;
            foreach ($colonnes as $titre => $liens) {
                $ins->execute([null, $titre, '#', $o++]);
                $pid = (int) $bdd->lastInsertId();
                $k = 1;
                foreach ($liens as [$lib, $url]) { $ins->execute([$pid, $lib, $url, $k++]); }
            }
        }
    } else { $skip('menu du pied de page déjà présent'); }
}

/* 5. Encodage : utf8mb4_unicode_ci partout ------------------------------------------------- */
$tables = $bdd->query("SELECT table_name AS t, table_collation AS c FROM information_schema.tables
                       WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'")->fetchAll();
foreach ($tables as $t) {
    if ($t['c'] === 'utf8mb4_unicode_ci') { continue; }
    $p("CONVERT {$t['t']} ({$t['c']} -> utf8mb4_unicode_ci)");
    if ($apply) { $bdd->exec("ALTER TABLE `{$t['t']}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); }
}
$dbColl = (string) $bdd->query('SELECT @@collation_database')->fetchColumn();
if ($dbColl !== 'utf8mb4_unicode_ci') {
    $p("base : collation par défaut $dbColl -> utf8mb4_unicode_ci");
    if ($apply) { $bdd->exec('ALTER DATABASE `' . str_replace('`', '', DB_NAME) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'); }
}

/* 6. Index complémentaires --------------------------------------------------------------- */
$addIdx('activite', 'idx_activite_date', 'KEY idx_activite_date (date_pub)');
$addIdx('commentaire', 'idx_comm_act', 'KEY idx_comm_act (id_act)');
$addIdx('likes', 'idx_likes_act', 'KEY idx_likes_act (id_act, ip)');
$addIdx('dislike', 'idx_dislike_act', 'KEY idx_dislike_act (id_act, ip)');
$addIdx('vu', 'idx_vu_act', 'KEY idx_vu_act (id_act, ip)');
$addIdx('payment_logs', 'idx_paylog_refid', 'KEY idx_paylog_refid (type_transaction, ref_id)');
$addIdx('grades', 'idx_grades_qt', 'KEY idx_grades_qt (id_qt, actif, ordre)');

// Cache des contenus : vidé pour que les nouveaux réglages / menus soient pris en compte immédiatement
if ($apply) {
    foreach (glob(dirname(__DIR__) . '/storage/cache/contenu_*.json') ?: [] as $f) { @unlink($f); }
}
echo $apply ? "=== TERMINÉ ===\n" : "=== Simulation terminée. Relancez avec --apply (après sauvegarde). ===\n";
