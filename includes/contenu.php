<?php
/**
 * Contenus pilotés par la base de données.
 *
 * Tous les textes du site (pages, valeurs, boutons, coordonnées, menu) viennent des
 * tables site_blocs / site_reglages / site_menu et seront éditables depuis l'admin.
 *
 * Usage dans une page :
 *   <h1><?= e(reglage('home_titre')) ?></h1>
 *   <?php foreach (blocs('apropos') as $b): ?>
 *       <section id="<?= e($b['cle']) ?>"><?= bloc_rendu($b) ?></section>
 *   <?php endforeach; ?>
 *   <?= bloc_html('home', 'notre_maitre') ?>
 *
 * Performance (objectif ~12 000 visiteurs/jour) : une seule lecture par page et par
 * langue, mise en cache dans storage/cache/ ; le cache est vidé à chaque modification admin.
 * Multilingue : colonne `langue`. Si un texte n'existe pas dans la langue demandée,
 * on retombe sur le français.
 */

require_once __DIR__ . '/helpers.php'; // e()

if (!function_exists('contenu_langue')) {
    function contenu_langue(): string
    {
        $l = $_SESSION['langue'] ?? 'fr';
        return in_array($l, ['fr', 'ln', 'sw', 'kg', 'lu'], true) ? $l : 'fr'; // fr, lingala, swahili, kikongo, tshiluba
    }
}

if (!function_exists('contenu_cache_dir')) {
    function contenu_cache_dir(): string
    {
        $d = __DIR__ . '/../storage/cache';
        if (!is_dir($d)) {
            @mkdir($d, 0750, true);
        }
        return $d;
    }
}

if (!function_exists('contenu_cache_purge')) {
    /** À appeler après toute modification de texte depuis l'admin. */
    function contenu_cache_purge(): void
    {
        foreach (glob(contenu_cache_dir() . '/contenu_*.json') ?: [] as $f) {
            @unlink($f);
        }
    }
}

if (!function_exists('contenu_cached')) {
    function contenu_cached(string $name, callable $loader): array
    {
        static $mem = [];
        if (isset($mem[$name])) {
            return $mem[$name];
        }
        $file = contenu_cache_dir() . '/contenu_' . preg_replace('/[^a-z0-9_\-]/i', '_', $name) . '.json';
        if (is_file($file) && (time() - filemtime($file)) < 3600) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                return $mem[$name] = $data;
            }
        }
        try {
            $data = $loader();
        } catch (Throwable $e) {
            // Tables site_* absentes (migrations pas encore lancées) ou base indisponible : le site reste en ligne
            // avec les textes par défaut codés dans les pages. Rien n'est mis en cache pour réessayer au prochain appel.
            error_log('[contenu] ' . $name . ' : ' . $e->getMessage());
            return $mem[$name] = [];
        }
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
        return $mem[$name] = $data;
    }
}

if (!function_exists('contenu_bdd')) {
    function contenu_bdd(): PDO
    {
        global $bdd;
        if (!($bdd instanceof PDO)) {
            throw new RuntimeException('Connexion $bdd absente');
        }
        return $bdd;
    }
}

/* ------------------------------------------------------------------ RÉGLAGES */

if (!function_exists('reglages_tous')) {
    function reglages_tous(string $langue): array
    {
        return contenu_cached('reglages_' . $langue, function () use ($langue) {
            $s = contenu_bdd()->prepare("SELECT cle, valeur FROM site_reglages WHERE langue IN ('fr', ?) ORDER BY (langue = 'fr') DESC");
            $s->execute([$langue]);
            $out = [];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $out[$r['cle']] = $r['valeur']; // la langue demandée écrase le français (tri ci-dessus)
            }
            return $out;
        });
    }
}

if (!function_exists('reglage')) {
    function reglage(string $cle, string $defaut = ''): string
    {
        $all = reglages_tous(contenu_langue());
        return isset($all[$cle]) && $all[$cle] !== '' ? $all[$cle] : $defaut;
    }
}

if (!function_exists('app_mobile_liens')) {
    /**
     * Liens de téléchargement de l'application mobile (réglages app_android_url / app_ios_url).
     * Accepte une URL https://… ou un chemin relatif au site (ex. telechargements/rcr.apk) ; tout autre
     * schéma (javascript:, data:…) est refusé. Valeur vide = lien non publié.
     * @return array{android:string,ios:string}
     */
    function app_mobile_liens(string $base = ''): array
    {
        $out = ['android' => '', 'ios' => ''];
        foreach (['android' => 'app_android_url', 'ios' => 'app_ios_url'] as $k => $cle) {
            $u = trim(reglage($cle));
            if ($u === '') { continue; }
            if (preg_match('#^https?://#i', $u)) { $out[$k] = $u; }
            elseif (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $u) && !str_starts_with($u, '//')) { $out[$k] = $base . ltrim($u, '/'); }
        }
        return $out;
    }
}

/* --------------------------------------------------------------------- BLOCS */

if (!function_exists('blocs')) {
    /** Blocs publiés d'une page, triés. @return array<int,array<string,mixed>> */
    function blocs(string $page): array
    {
        $langue = contenu_langue();
        $rows = contenu_cached('blocs_' . $page . '_' . $langue, function () use ($page, $langue) {
            $s = contenu_bdd()->prepare("SELECT cle, langue, titre, contenu, type, ordre, meta FROM site_blocs
                                         WHERE page = ? AND langue IN ('fr', ?) AND statut = 'publie' ORDER BY ordre, id");
            $s->execute([$page, $langue]);
            $byKey = [];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if (!isset($byKey[$r['cle']]) || $r['langue'] === $langue) {
                    $byKey[$r['cle']] = $r; // traduction prioritaire sur le français
                }
            }
            return array_values($byKey);
        });
        return $rows;
    }
}

if (!function_exists('bloc')) {
    function bloc(string $page, string $cle): ?array
    {
        foreach (blocs($page) as $b) {
            if ($b['cle'] === $cle) {
                return $b;
            }
        }
        return null;
    }
}

if (!function_exists('bloc_rendu')) {
    /**
     * Contenu prêt à afficher. type "html" : HTML saisi par un administrateur autorisé
     * (permission contenu.gerer), affiché tel quel. type "texte" : échappé.
     */
    function bloc_rendu(array $b): string
    {
        return $b['type'] === 'html' ? (string) $b['contenu'] : nl2br(e((string) $b['contenu']));
    }
}

if (!function_exists('bloc_html')) {
    function bloc_html(string $page, string $cle, string $defaut = ''): string
    {
        $b = bloc($page, $cle);
        return $b ? bloc_rendu($b) : $defaut;
    }
}

if (!function_exists('bloc_titre')) {
    function bloc_titre(string $page, string $cle, string $defaut = ''): string
    {
        $b = bloc($page, $cle);
        return $b ? (string) $b['titre'] : $defaut;
    }
}

/* ---------------------------------------------------------------------- MENU */

if (!function_exists('menu_items')) {
    /** @return array<int,array{id:int,parent_id:?int,libelle:string,url:string,classe:string}> */
    function menu_items(string $zone = 'header'): array
    {
        $langue = contenu_langue();
        return contenu_cached('menu_' . $zone . '_' . $langue, function () use ($zone, $langue) {
            try {
                $s = contenu_bdd()->prepare("SELECT id, parent_id, libelle, url, classe FROM site_menu WHERE zone = ? AND langue IN ('fr', ?) AND actif = 1 ORDER BY ordre, id");
                $s->execute([$zone, $langue]);
            } catch (Throwable $e) { // colonne `classe` absente : migration phase 2 pas encore lancée
                $s = contenu_bdd()->prepare("SELECT id, parent_id, libelle, url, '' AS classe FROM site_menu WHERE zone = ? AND langue IN ('fr', ?) AND actif = 1 ORDER BY ordre, id");
                $s->execute([$zone, $langue]);
            }
            return $s->fetchAll(PDO::FETCH_ASSOC);
        });
    }
}

if (!function_exists('menu_url')) {
    /**
     * URL d'un élément de menu, ajustée à la page qui l'affiche ($base = '' à la racine, '../' depuis adhere/).
     * '@renouveler' : espace membre si connecté, sinon page de connexion.
     */
    function menu_url(string $u, string $base = '', bool $connecte = false): string
    {
        if ($u === '@renouveler') {
            $u = $connecte ? '?pages=esp_membre' : '?pages=login';
        }
        if ($u === '' || $u[0] === '#' || $u[0] === '/' || preg_match('#^[a-z][a-z0-9+.-]*:#i', $u)) {
            return $u; // ancre, chemin absolu, lien externe (https:, mailto:, tel:)
        }
        if ($u[0] === '?') {
            return $base === '' ? $u : $base . 'index.php' . $u;
        }
        return $base . preg_replace('#^\./#', '', $u);
    }
}

if (!function_exists('menu_rendu')) {
    /**
     * Rendu HTML (<li>…) du menu public piloté par la base (table site_menu, zone header).
     * Retourne '' si le menu est vide/indisponible (la page affiche alors son menu de secours).
     */
    function menu_rendu(string $pageActive, string $base = '', bool $connecte = false): string
    {
        try {
            $rows = menu_items('header');
        } catch (Throwable $e) {
            return '';
        }
        if (!$rows) {
            return '';
        }
        $parParent = [];
        foreach ($rows as $r) {
            $parParent[(int) ($r['parent_id'] ?? 0)][] = $r;
        }
        $h = '';
        foreach ($parParent[0] ?? [] as $it) {
            $enfants = $parParent[(int) $it['id']] ?? [];
            $actif = strpos((string) $it['url'], 'pages=' . $pageActive) !== false;
            foreach ($enfants as $k) {
                if (strpos((string) $k['url'], 'pages=' . $pageActive) !== false) { $actif = true; }
            }
            $classe = trim(($enfants && strpos((string) ($it['classe'] ?? ''), 'dropdown') === false ? 'dropdown ' : '') . ($it['classe'] ?? '') . ($actif ? ' active' : ''));
            $h .= '<li' . ($classe !== '' ? ' class="' . e($classe) . '"' : '') . '>';
            if ($enfants) {
                $h .= '<a href="#"><span>' . e($it['libelle']) . '</span> <i class="bi bi-chevron-down"></i></a><ul>';
                foreach ($enfants as $k) {
                    $h .= '<li><a href="' . e(menu_url((string) $k['url'], $base, $connecte)) . '">' . e($k['libelle']) . '</a></li>';
                }
                $h .= '</ul>';
            } else {
                $h .= '<a href="' . e(menu_url((string) $it['url'], $base, $connecte)) . '"' . ($actif ? ' class="active"' : '') . '>' . e($it['libelle']) . '</a>';
            }
            $h .= '</li>';
        }
        return $h;
    }
}

/* ------------------------------------------------------- ÉCRITURE (admin) */

if (!function_exists('contenu_assainir')) {
    /**
     * Nettoyage minimal du HTML saisi en administration (défense en profondeur) :
     * retire <script>, <object>/<embed>, les attributs on*="…" et les liens javascript:/data:text/html.
     * Les <iframe> restent autorisés (vidéos) mais uniquement vers https://.
     */
    function contenu_assainir(string $html): string
    {
        $html = preg_replace('#<\s*(script|object|embed|applet|base|meta|link)\b[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html);
        $html = preg_replace('#<\s*(script|object|embed|applet|base|meta|link)\b[^>]*/?>#is', '', $html);
        $html = preg_replace('#\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html);
        $html = preg_replace('#(href|src|xlink:href)\s*=\s*(["\'])\s*(javascript|vbscript|data\s*:\s*text/html)[^"\']*\2#i', '$1=$2#$2', $html);
        $html = preg_replace_callback('#<iframe\b[^>]*>#i', function ($m) {
            return preg_match('#\ssrc\s*=\s*["\']https://#i', $m[0]) ? $m[0] : '';
        }, $html);
        return (string) $html;
    }
}

if (!function_exists('bloc_enregistrer')) {
    /**
     * Crée ou met à jour un bloc. À n'appeler qu'après require_permission('contenu.gerer').
     * Journalise via audit_log() si disponible et purge le cache.
     */
    function bloc_enregistrer(PDO $bdd, string $page, string $cle, string $langue, string $titre, string $contenu, string $type, string $statut, ?int $idAdm): void
    {
        $type   = $type === 'texte' ? 'texte' : 'html';
        if ($type === 'html') {
            $contenu = contenu_assainir($contenu);
        }
        $statut = in_array($statut, ['brouillon', 'publie', 'archive'], true) ? $statut : 'brouillon';
        $s = $bdd->prepare("INSERT INTO site_blocs (page, cle, langue, titre, contenu, type, ordre, statut, modifie_par)
                            VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)
                            ON DUPLICATE KEY UPDATE titre = VALUES(titre), contenu = VALUES(contenu), type = VALUES(type),
                                                    statut = VALUES(statut), modifie_par = VALUES(modifie_par)");
        $s->execute([$page, $cle, $langue, $titre, $contenu, $type, $statut, $idAdm]);
        contenu_cache_purge();
        if (function_exists('audit_log')) {
            audit_log($bdd, 'contenu.modifier', 'site_blocs', "$page/$cle/$langue");
        }
    }
}

if (!function_exists('reglage_enregistrer')) {
    function reglage_enregistrer(PDO $bdd, string $cle, string $langue, string $valeur, ?int $idAdm): void
    {
        $s = $bdd->prepare('UPDATE site_reglages SET valeur = ?, modifie_par = ? WHERE cle = ? AND langue = ?');
        $s->execute([$valeur, $idAdm, $cle, $langue]);
        if ($s->rowCount() === 0) {
            $c = $bdd->prepare('INSERT IGNORE INTO site_reglages (cle, langue, valeur, groupe, libelle, type, modifie_par)
                                SELECT cle, ?, ?, groupe, libelle, type, ? FROM site_reglages WHERE cle = ? AND langue = \'fr\' LIMIT 1');
            $c->execute([$langue, $valeur, $idAdm, $cle]);
            if ($c->rowCount() === 0) {
                $bdd->prepare("INSERT IGNORE INTO site_reglages (cle, langue, valeur, groupe, libelle) VALUES (?, ?, ?, 'general', ?)")
                    ->execute([$cle, $langue, $valeur, $cle]);
            }
        }
        contenu_cache_purge();
        if (function_exists('audit_log')) {
            audit_log($bdd, 'reglage.modifier', 'site_reglages', "$cle/$langue");
        }
    }
}
