<?php
/**
 * SEO : titre, description, canonical, Open Graph, Twitter, données structurées.
 * Les textes viennent de la base (site_reglages) : seo_titre_<page>, seo_description_<page>,
 * seo_description_defaut, seo_image. Valeurs de secours codées ici uniquement si la base est vide.
 * Aucune donnée venant du navigateur (Host, REQUEST_URI) n'est écrite dans la page.
 */
if (!function_exists('seo_base_url')) {
    function seo_base_url(): string
    {
        return defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : 'https://rcr.cd';
    }
}

/** Pages publiques autorisées par le routeur => URL propre. */
const SEO_PAGES_PUBLIQUES = [
    'home' => '', 'apropos' => 'apropos', 'parti' => 'parti', 'mission' => 'mission', 'equipe' => 'equipe',
    'organe' => 'organe', 'detail_organe' => 'detail_organe', 'partenaire' => 'partenaire',
    'publication' => 'publication', 'categories' => 'categories', 'detail' => 'detail', 'contact' => 'contact',
    'contactcode' => 'contactcode', 'soutenir' => 'soutenir', 'login' => 'login', 'register' => 'register', 'mdp_oublie' => 'mdp_oublie', 'mdp_reset' => 'mdp_reset',
    'deconnexion' => 'deconnexion', 'esp_membre' => 'esp_membre', 'mention' => 'mention',
    'politique-confidentialite' => 'politique-confidentialite', 'verifier' => 'verifier', 'service' => 'service',
];

/** Pages à ne pas faire indexer (privées ou sans intérêt pour un moteur de recherche). */
const SEO_PAGES_NOINDEX = ['login', 'register', 'mdp_oublie', 'mdp_reset', 'deconnexion', 'esp_membre', 'verifier', 'contactcode', 'service'];

if (!function_exists('seo_article')) {
    /** @return array{titre:string,description:string,photo:string}|null */
    function seo_article(PDO $bdd, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $s = $bdd->prepare('SELECT titre, description, photo FROM activite WHERE id_act = ? AND etat_modifier = 1 LIMIT 1');
            $s->execute([$id]);
            $r = $s->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return null;
        }
        if (!$r) {
            return null;
        }
        $txt = trim(preg_replace('/\s+/', ' ', strip_tags((string) $r['description'])));
        return ['titre' => (string) $r['titre'], 'description' => mb_substr($txt, 0, 160), 'photo' => (string) $r['photo']];
    }
}

if (!function_exists('seo_meta')) {
    /** Calcule toutes les métadonnées de la page courante. */
    function seo_meta(PDO $bdd, string $page): array
    {
        $nom     = reglage('parti_sigle', 'RCR');
        $titres  = [
            'home' => $nom . ' — Rassemblement des Chrétiens Républicains',
            'apropos' => 'Qui sommes-nous ?', 'parti' => 'Le Chef du Parti', 'mission' => 'Notre mission',
            'equipe' => 'Notre équipe', 'organe' => 'Nos instances', 'publication' => 'Actualités et événements',
            'categories' => 'Actualités', 'contact' => 'Contact', 'soutenir' => 'Soutenir le ' . $nom,
            'mention' => 'Mentions légales', 'politique-confidentialite' => 'Politique de confidentialité',
            'verifier' => 'Vérifier une carte de membre', 'login' => 'Connexion membre', 'esp_membre' => 'Mon espace membre',
        ];
        $titre = reglage('seo_titre_' . $page, $titres[$page] ?? $nom);
        $desc  = reglage('seo_description_' . $page, reglage('seo_description_defaut', 'Site officiel du Rassemblement des Chrétiens Républicains (RCR), RDC.'));
        $image = seo_base_url() . '/' . ltrim(reglage('seo_image', 'media/lo/logorcr.png'), '/');
        $path  = SEO_PAGES_PUBLIQUES[$page] ?? '';
        $type  = 'website';

        if ($page === 'detail') {
            $id = (int) ($_GET['id'] ?? 0);
            if ($a = seo_article($bdd, $id)) {
                $titre = $a['titre']; $desc = $a['description'] !== '' ? $a['description'] : $desc; $type = 'article';
                $path  = 'article-' . $id;
                if ($a['photo'] !== '') { $image = seo_base_url() . '/media/images_activ/' . rawurlencode($a['photo']); }
            }
        }
        $full = $page === 'home' ? $nom . ' — Rassemblement des Chrétiens Républicains' : $titre . ' | ' . $nom;
        return [
            'titre'     => $page === 'detail' ? $titre . ' | ' . $nom : $full,
            'description' => $desc,
            'canonical' => seo_base_url() . '/' . $path,
            'image'     => $image,
            'type'      => $type,
            'noindex'   => in_array($page, SEO_PAGES_NOINDEX, true),
        ];
    }
}

if (!function_exists('seo_head')) {
    /** Écrit les balises <title>, description, canonical, Open Graph, Twitter et JSON-LD. */
    function seo_head(PDO $bdd, string $page): void
    {
        $m = seo_meta($bdd, $page);
        $h = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
        echo '<title>' . $h($m['titre']) . "</title>\n";
        echo '    <meta name="description" content="' . $h($m['description']) . "\">\n";
        echo '    <meta name="robots" content="' . ($m['noindex'] ? 'noindex, nofollow' : 'index, follow') . "\">\n";
        echo '    <link rel="canonical" href="' . $h($m['canonical']) . "\">\n";
        echo '    <meta property="og:type" content="' . $m['type'] . "\">\n";
        echo '    <meta property="og:title" content="' . $h($m['titre']) . "\">\n";
        echo '    <meta property="og:description" content="' . $h($m['description']) . "\">\n";
        echo '    <meta property="og:url" content="' . $h($m['canonical']) . "\">\n";
        echo '    <meta property="og:image" content="' . $h($m['image']) . "\">\n";
        echo '    <meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '    <meta name="twitter:title" content="' . $h($m['titre']) . "\">\n";
        echo '    <meta name="twitter:description" content="' . $h($m['description']) . "\">\n";
        echo '    <meta name="twitter:image" content="' . $h($m['image']) . "\">\n";
        if ($page === 'home') {
            $sameAs = array_values(array_filter([reglage('reseau_facebook'), reglage('reseau_x'), reglage('reseau_youtube')]));
            $ld = [
                '@context' => 'https://schema.org', '@type' => 'Organization',
                'name' => reglage('parti_nom', 'Rassemblement des Chrétiens Républicains'),
                'alternateName' => reglage('parti_sigle', 'RCR'),
                'url' => seo_base_url() . '/', 'logo' => seo_base_url() . '/media/lo/logo.png',
                'slogan' => reglage('parti_devise'),
            ];
            if ($sameAs) { $ld['sameAs'] = $sameAs; }
            echo '    <script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . "</script>\n";
        }
    }
}
