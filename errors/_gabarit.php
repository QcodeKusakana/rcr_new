<?php
/**
 * Gabarit commun des pages d'erreur. Autonome : aucune base de données, aucune session,
 * aucun chemin serveur, aucun message technique n'est affiché au visiteur.
 */
function rcr_page_erreur(int $code, string $titre, string $message): void
{
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        header('X-Robots-Tag: noindex');
    }
    $titre   = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<meta name="robots" content="noindex"><title>' . $code . ' — ' . $titre . ' | RCR</title>'
       . '<style>'
       . ':root{--ink:#1B2A44;--gold:#9C7A2E;--paper:#F1E9D8}'
       . '*{box-sizing:border-box}body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;'
       . 'background:var(--paper);color:var(--ink);font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;padding:24px}'
       . '.c{max-width:520px;text-align:center}.n{font-size:clamp(64px,18vw,112px);font-weight:800;color:var(--gold);line-height:1;margin:0}'
       . 'h1{font-size:clamp(22px,5vw,30px);margin:12px 0}p{line-height:1.6;margin:0 0 24px}'
       . '.b{display:inline-block;background:var(--ink);color:#fff;text-decoration:none;padding:12px 26px;border-radius:8px;font-weight:600}'
       . '.b:hover{background:var(--gold)}.s{margin-top:18px;font-size:14px}.s a{color:var(--ink)}'
       . '</style></head><body><div class="c"><p class="n">' . $code . '</p><h1>' . $titre . '</h1><p>' . $message . '</p>'
       . '<a class="b" href="/">Retour à l\'accueil</a>'
       . '<div class="s"><a href="/?pages=contact">Nous contacter</a></div></div></body></html>';
}
