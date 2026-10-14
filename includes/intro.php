<?php
/**
 * Écran d'accueil du site : logo RCR dans un cercle, sur fond bleu foncé, puis fondu vers la page.
 * Affiché une seule fois par visite (navigateur) ; ne bloque jamais le site (repli CSS si JavaScript est désactivé).
 * Inclus juste après <body> dans index.php.
 */
?>
<style>
    #rcr-intro{position:fixed;inset:0;z-index:99999;background:#05294b;display:flex;align-items:center;justify-content:center;
        opacity:1;visibility:visible;transition:opacity .7s ease,visibility .7s ease;
        animation:rcr-intro-secours 0s linear 4s forwards}
    #rcr-intro.rcr-intro-fin{opacity:0;visibility:hidden}
    #rcr-intro .rcr-cercle{width:min(300px,72vw);aspect-ratio:1;border-radius:50%;display:flex;align-items:center;justify-content:center;
        background:radial-gradient(circle at 50% 40%,#0b3a68 0%,#05294b 75%);
        box-shadow:0 0 0 2px rgba(201,162,74,.85),0 0 0 8px rgba(201,162,74,.12),0 18px 50px rgba(0,0,0,.45);
        animation:rcr-intro-entree .9s cubic-bezier(.2,.8,.2,1) both}
    #rcr-intro img{width:76%;height:auto;display:block;animation:rcr-intro-logo 1.1s ease .15s both}
    @keyframes rcr-intro-entree{from{opacity:0;transform:scale(.82)}to{opacity:1;transform:scale(1)}}
    @keyframes rcr-intro-logo{from{opacity:0;transform:scale(.92)}to{opacity:1;transform:scale(1)}}
    @keyframes rcr-intro-secours{to{opacity:0;visibility:hidden}}
    @media (prefers-reduced-motion:reduce){#rcr-intro .rcr-cercle,#rcr-intro img{animation:none}}
</style>
<div id="rcr-intro" aria-hidden="true">
    <div class="rcr-cercle"><img src="media/lo/logo-intro.png" alt="" width="231" height="144"></div>
</div>
<script>
(function () {
    var e = document.getElementById('rcr-intro');
    if (!e) { return; }
    try { if (sessionStorage.getItem('rcr_intro_vu') === '1') { e.parentNode.removeChild(e); return; } } catch (x) {}
    var debut = Date.now(), minimum = 1700, fini = false;
    function fermer() {
        if (fini) { return; } fini = true;
        e.classList.add('rcr-intro-fin');
        try { sessionStorage.setItem('rcr_intro_vu', '1'); } catch (x) {}
        setTimeout(function () { if (e.parentNode) { e.parentNode.removeChild(e); } }, 900);
    }
    function pret() { setTimeout(fermer, Math.max(0, minimum - (Date.now() - debut))); }
    if (document.readyState === 'complete') { pret(); } else { window.addEventListener('load', pret); }
    setTimeout(fermer, 4000);
})();
</script>
