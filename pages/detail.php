<?php
/** Détail d'une actualité (données préparées par functions/detail.funct.php). */
$a = $article;
?>
<style>
.actu-detail{background:#F1E9D8;padding:48px 0 64px;font-family:'IBM Plex Sans',system-ui,sans-serif;color:#241F1A}
.actu-detail .crumb{font-size:14px;margin-bottom:18px}
.actu-detail .crumb a{color:#9C7A2E;text-decoration:none}
.actu-card{background:#FBF8F0;border:1px solid #E7DBBF;border-radius:6px;overflow:hidden}
.actu-card img.cover{width:100%;max-height:460px;object-fit:cover;display:block}
.actu-card .body{padding:28px}
.actu-tag{display:inline-block;background:#1B2A44;color:#F1E9D8;font-size:11.5px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;padding:5px 11px;text-decoration:none}
.actu-card h1{font-family:'Fraunces',Georgia,serif;font-size:clamp(24px,3.4vw,34px);color:#1B2A44;margin:14px 0 8px;line-height:1.25}
.actu-meta{color:#5B5346;font-size:14px;display:flex;flex-wrap:wrap;gap:16px;margin-bottom:18px}
.actu-texte{line-height:1.75;font-size:16.5px;overflow-wrap:anywhere}
.actu-side{background:#FBF8F0;border:1px solid #E7DBBF;border-radius:6px;padding:20px}
.actu-side h2{font-family:'Fraunces',Georgia,serif;font-size:20px;color:#1B2A44;margin:0 0 14px}
.actu-side a.item{display:flex;gap:12px;text-decoration:none;color:#241F1A;margin-bottom:14px}
.actu-side a.item img{width:78px;height:58px;object-fit:cover;border-radius:4px;flex-shrink:0}
.actu-side a.item:hover span{color:#9C7A2E}
.comm{background:#FBF8F0;border:1px solid #E7DBBF;border-radius:6px;padding:14px 16px;margin-bottom:10px}
.comm small{color:#5B5346}
.hp-field{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
</style>

<section class="actu-detail">
<div class="container">
    <nav class="crumb" aria-label="Fil d'Ariane">
        <a href="?pages=home">Accueil</a> / <a href="?pages=publication">Actualités</a> / <?= e(actu_extrait($a['titre'], 60)) ?>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <article class="actu-card">
                <img class="cover" loading="lazy" decoding="async" src="./media/images_activ/<?= e(rawurlencode((string) $a['photo'])) ?>" alt="<?= e(actu_extrait($a['titre'], 100)) ?>">
                <div class="body">
                    <a class="actu-tag" href="?pages=publication&amp;cat=<?= e(rawurlencode((string) $a['categorie'])) ?>"><?= e($a['categorie']) ?></a>
                    <h1><?= e($a['titre']) ?></h1>
                    <div class="actu-meta">
                        <span><i class="bi bi-calendar3"></i> <?= e(actu_date($a['date_pub'], 'd/m/Y à H:i')) ?></span>
                        <span><i class="bi bi-eye"></i> <?= (int) $a['nb_vues'] ?> vue<?= (int) $a['nb_vues'] > 1 ? 's' : '' ?></span>
                        <span><i class="bi bi-chat"></i> <?= count($commentaires) ?> commentaire<?= count($commentaires) > 1 ? 's' : '' ?></span>
                    </div>
                    <div class="actu-texte"><?= nl2br(e($a['description'])) ?></div>
                </div>
            </article>

            <section id="commentaires" class="mt-4">
                <h2 class="h5" style="font-family:'Fraunces',Georgia,serif;color:#1B2A44">Commentaires</h2>

                <?php if ($flashCommentaire): ?><div class="alert alert-success"><?= e($flashCommentaire) ?></div><?php endif; ?>
                <?php if ($erreur): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>

                <form method="post" action="?pages=detail&amp;id=<?= (int) $a['id_act'] ?>#commentaires" class="actu-card p-3 mb-3">
                    <?= csrf_field() ?>
                    <div class="hp-field" aria-hidden="true"><label>Site web <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>
                    <label class="form-label" for="c_pseudo">Votre nom</label>
                    <input type="text" id="c_pseudo" name="pseudo" class="form-control mb-2" maxlength="60" required value="<?= e($_POST['pseudo'] ?? '') ?>">
                    <label class="form-label" for="c_texte">Votre commentaire</label>
                    <textarea id="c_texte" name="commentaire" class="form-control mb-2" rows="4" maxlength="1000" required><?= e($_POST['commentaire'] ?? '') ?></textarea>
                    <button type="submit" name="comment" value="1" class="btn btn-primary">Publier</button>
                </form>

                <?php if (!$commentaires): ?>
                    <p class="text-muted">Soyez le premier à réagir.</p>
                <?php endif; ?>
                <?php foreach ($commentaires as $c): ?>
                    <div class="comm">
                        <strong><?= e($c['pseudo']) ?></strong> · <small><?= e(actu_date($c['date_pub'], 'd/m/Y à H:i')) ?></small>
                        <p class="mb-0 mt-1"><?= nl2br(e($c['commentaire'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>

        <aside class="col-lg-4">
            <div class="actu-side">
                <h2>Articles récents</h2>
                <?php if (!$recents): ?><p class="text-muted mb-0">Aucun autre article.</p><?php endif; ?>
                <?php foreach ($recents as $p): ?>
                    <a class="item" href="<?= e(actu_url($p)) ?>">
                        <img loading="lazy" decoding="async" src="./media/images_activ/<?= e(rawurlencode((string) $p['photo'])) ?>" alt="">
                        <span><?= e(actu_extrait($p['titre'], 60)) ?><br><small class="text-muted"><?= e(actu_date($p['date_pub'])) ?></small></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</div>
</section>
