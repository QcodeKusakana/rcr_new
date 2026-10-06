<?php
/**
 * Actualités / événements (table activite) — lecture publique.
 * Une seule requête par liste (compteurs vues / j'aime / commentaires agrégés), pagination bornée,
 * uniquement les articles publiés (etat_modifier = 1).
 */

if (!function_exists('actu_liste')) {
    /**
     * @return array{items:array, total:int, current:int, nbPage:int}
     */
    function actu_liste(PDO $bdd, ?string $categorie, int $parPage = 12): array
    {
        $parPage = max(1, $parPage);
        $where   = 'a.etat_modifier = 1';
        $params  = [];
        if ($categorie !== null && $categorie !== '') {
            $where   .= ' AND a.categorie = ?';
            $params[] = $categorie;
        }
        $c = $bdd->prepare("SELECT COUNT(*) FROM activite a WHERE $where");
        $c->execute($params);
        $total  = (int) $c->fetchColumn();
        $nbPage = max(1, (int) ceil($total / $parPage));
        $current = (int) ($_GET['pag'] ?? 1);
        $current = min($nbPage, max(1, $current));
        $offset  = ($current - 1) * $parPage;

        $s = $bdd->prepare("SELECT a.id_act, a.titre, a.description, a.photo, a.categorie, a.date_pub,
                    (SELECT COUNT(*) FROM vu v WHERE v.id_act = a.id_act) AS nb_vues,
                    (SELECT COUNT(*) FROM likes l WHERE l.id_act = a.id_act) AS nb_likes,
                    (SELECT COUNT(*) FROM commentaire m WHERE m.id_act = a.id_act) AS nb_commentaires
                FROM activite a WHERE $where ORDER BY a.date_pub DESC LIMIT $offset, $parPage");
        $s->execute($params);
        return ['items' => $s->fetchAll(PDO::FETCH_ASSOC), 'total' => $total, 'current' => $current, 'nbPage' => $nbPage];
    }
}

if (!function_exists('actu_article')) {
    function actu_article(PDO $bdd, int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $s = $bdd->prepare('SELECT a.*,
                    (SELECT COUNT(*) FROM vu v WHERE v.id_act = a.id_act) AS nb_vues,
                    (SELECT COUNT(*) FROM likes l WHERE l.id_act = a.id_act) AS nb_likes,
                    (SELECT COUNT(*) FROM dislike d WHERE d.id_act = a.id_act) AS nb_dislikes
                FROM activite a WHERE a.id_act = ? AND a.etat_modifier = 1 LIMIT 1');
        $s->execute([$id]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }
}

if (!function_exists('actu_recents')) {
    function actu_recents(PDO $bdd, int $exclureId, int $limite = 5): array
    {
        $limite = max(1, min(20, $limite));
        $s = $bdd->prepare("SELECT id_act, titre, photo, categorie, date_pub FROM activite
                            WHERE etat_modifier = 1 AND id_act <> ? ORDER BY date_pub DESC LIMIT $limite");
        $s->execute([$exclureId]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('actu_enregistrer_vue')) {
    /** Une vue par article et par adresse IP (ignorée en cas d'erreur : jamais bloquant). */
    function actu_enregistrer_vue(PDO $bdd, int $id): void
    {
        try {
            $ip = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
            $s = $bdd->prepare('SELECT 1 FROM vu WHERE id_act = ? AND ip = ? LIMIT 1');
            $s->execute([$id, $ip]);
            if (!$s->fetchColumn()) {
                $bdd->prepare('INSERT INTO vu (id_act, ip) VALUES (?, ?)')->execute([$id, $ip]);
            }
        } catch (Throwable $e) {
            error_log('[actu_vue] ' . $e->getMessage());
        }
    }
}

if (!function_exists('actu_commentaires')) {
    function actu_commentaires(PDO $bdd, int $id): array
    {
        $s = $bdd->prepare('SELECT pseudo, commentaire, date_pub FROM commentaire WHERE id_act = ? ORDER BY date_pub DESC, id_cmt DESC LIMIT 200');
        $s->execute([$id]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('actu_url')) {
    function actu_url(array $a): string
    {
        return '?pages=detail&id=' . (int) $a['id_act'];
    }
}

if (!function_exists('actu_extrait')) {
    /** Extrait texte sans balises, coupé proprement (UTF-8). */
    function actu_extrait(?string $texte, int $longueur): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $texte), ENT_QUOTES, 'UTF-8')));
        return mb_strlen($t) > $longueur ? rtrim(mb_substr($t, 0, $longueur)) . '…' : $t;
    }
}

if (!function_exists('actu_date')) {
    function actu_date(?string $date, string $format = 'd/m/Y'): string
    {
        $ts = $date ? strtotime($date) : false;
        return $ts ? date($format, $ts) : '';
    }
}
