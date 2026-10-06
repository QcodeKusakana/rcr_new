<?php
/**
 * ⚠️ CORRECTIF (audit) — contenu erroné retiré.
 *
 * Cette page affichait un texte "MISSION" hors-sujet (recherche en
 * agroforesterie, maraîchage, biodiversité...) suivi d'un paragraphe
 * "Lorem ipsum" — reliquat du gabarit d'origine ("Institut Africain de
 * Recherche sur le Développement Durable", cf. pages/organe.php) jamais
 * adapté au RCR.
 *
 * Cette page n'est reliée à aucun lien du site, MAIS reste atteignable
 * directement via ?pages=mission : le routeur (admin/index.php et
 * index.php) rend accessible tout fichier présent dans pages/, qu'il
 * soit lié ou non dans un menu. Neutralisée par précaution, au même
 * titre que pages/organe.php et pages/detail_organe.php.
 *
 * Comme pour pages/mention.php, je n'ai pas rédigé de texte de
 * remplacement définitif : la mission du RCR est un contenu propre au
 * parti que je ne peux pas inventer. Merci de me transmettre le texte
 * réel si vous souhaitez que cette page (ou une page "Mission"
 * effectivement reliée au menu) soit republiée avec un contenu exact.
 */
?>
<br><br>

<section id="mission" class="py-5">
    <div class="container">

        <div class="section-title text-center mb-5">

            <span class="badge bg-primary px-4 py-2 mb-3 fs-6">
                R.C.R
            </span>

            <h2 class="fw-bold text-uppercase text-dark">
                Notre mission
            </h2>

            <div class="mx-auto mt-3"
                 style="width: 90px; height: 4px; background: #0d6efd; border-radius: 10px;">
            </div>

        </div>

        <div class="alert alert-secondary shadow-sm">
            <i class="bi bi-info-circle-fill me-2"></i>
            Contenu à venir.
        </div>

    </div>
</section>
