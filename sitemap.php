<?php
/**
 * sitemap.xml dynamique (servi via .htaccess : /sitemap.xml -> sitemap.php).
 * Pages publiques indexables + articles publiés. Mis en cache 1 heure dans storage/cache.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=utf-8');
$cache = __DIR__ . '/storage/cache/sitemap.xml';
if (is_file($cache) && (time() - filemtime($cache)) < 3600) {
    readfile($cache);
    exit;
}

$base = seo_base_url();
$urls = [];
foreach (SEO_PAGES_PUBLIQUES as $page => $path) {
    if (in_array($page, SEO_PAGES_NOINDEX, true) || $page === 'detail') { continue; }
    $urls[] = [$base . '/' . $path, null, $page === 'home' ? '1.0' : '0.7'];
}
try {
    $q = $bdd->query("SELECT id_act, date_pub FROM activite WHERE etat_modifier = 1 ORDER BY date_pub DESC LIMIT 5000");
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $urls[] = [$base . '/article-' . (int) $a['id_act'], substr((string) $a['date_pub'], 0, 10), '0.6'];
    }
} catch (Throwable $e) {
    error_log('[sitemap] ' . $e->getMessage());
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $mod, $prio]) {
    $xml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
          . ($mod && preg_match('/^\d{4}-\d{2}-\d{2}$/', $mod) ? '<lastmod>' . $mod . '</lastmod>' : '')
          . '<priority>' . $prio . '</priority></url>' . "\n";
}
$xml .= '</urlset>' . "\n";
@file_put_contents($cache, $xml, LOCK_EX);
echo $xml;
