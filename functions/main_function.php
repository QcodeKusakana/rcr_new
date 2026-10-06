<?php
require_once __DIR__ . '/../includes/bootstrap.php';

function url_partage()
{
    require_once __DIR__ . '/../includes/seo.php';
    $uri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
    $qs  = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $url = seo_base_url() . $uri . ($qs !== '' ? '?' . $qs : ''); // hôte fixe : un en-tête Host falsifié ne peut pas s'y glisser
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}
