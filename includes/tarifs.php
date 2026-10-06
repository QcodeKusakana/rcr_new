<?php
/**
 * Tarifs de cotisation — SOURCE UNIQUE : tables qualites, grades, cotisation.
 *  - grades.prix      = tarif MENSUEL en USD pour (catégorie, grade)
 *  - cotisation.mois  = multiplicateur de la période (1, 3, 6, 12)
 *  - montant d'une souscription = prix mensuel x mois, TOUJOURS recalculé côté serveur
 *    (le montant envoyé par le navigateur n'est jamais utilisé pour payer).
 * Aucun prix n'est écrit en dur dans le code.
 */
require_once __DIR__ . '/audit.php';

const TARIF_DEVISE = 'USD';

if (!function_exists('tarifs_categories')) {
    function tarifs_categories(PDO $bdd): array
    {
        return $bdd->query('SELECT id_qt, designation FROM qualites ORDER BY id_qt')->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('tarifs_periodes')) {
    function tarifs_periodes(PDO $bdd, bool $seulementActives = true): array
    {
        $sql = 'SELECT id_cot, nom_cot, jours, mois, actif FROM cotisation' . ($seulementActives ? ' WHERE actif = 1' : '') . ' ORDER BY mois';
        return $bdd->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('tarifs_grades')) {
    /** Grades d'une catégorie (uniquement les actifs et non archivés par défaut). */
    function tarifs_grades(PDO $bdd, int $idQt, bool $seulementActifs = true): array
    {
        $sql = 'SELECT id_gd, nom_gd, prix, id_qt, actif, ordre FROM grades WHERE id_qt = ? AND ancien = 0'
             . ($seulementActifs ? ' AND actif = 1' : '') . ' ORDER BY ordre, id_gd';
        $s = $bdd->prepare($sql);
        $s->execute([$idQt]);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('tarifs_calculer')) {
    /**
     * Valide (catégorie, grade, période) et calcule le montant.
     * @return array{montant:float,devise:string,mois:int,jours:int,grade:string,categorie:string,id_gd:int,id_qt:int,id_cot:int}|null
     *         null si le grade n'appartient pas à la catégorie, est désactivé, ou si la période est inconnue.
     */
    function tarifs_calculer(PDO $bdd, int $idQt, int $idGd, int $idCot): ?array
    {
        $s = $bdd->prepare('SELECT g.id_gd, g.nom_gd, g.prix, g.id_qt, q.designation
                            FROM grades g JOIN qualites q ON q.id_qt = g.id_qt
                            WHERE g.id_gd = ? AND g.id_qt = ? AND g.actif = 1 AND g.ancien = 0');
        $s->execute([$idGd, $idQt]);
        $g = $s->fetch(PDO::FETCH_ASSOC);
        $c = $bdd->prepare('SELECT id_cot, mois, jours FROM cotisation WHERE id_cot = ? AND actif = 1');
        $c->execute([$idCot]);
        $p = $c->fetch(PDO::FETCH_ASSOC);
        if (!$g || !$p || (float) $g['prix'] <= 0 || (int) $p['mois'] < 1) {
            return null;
        }
        return [
            'montant'   => round((float) $g['prix'] * (int) $p['mois'], 2),
            'devise'    => TARIF_DEVISE,
            'mois'      => (int) $p['mois'],
            'jours'     => (int) $p['jours'],
            'grade'     => $g['nom_gd'],
            'categorie' => $g['designation'],
            'id_gd'     => (int) $g['id_gd'],
            'id_qt'     => (int) $g['id_qt'],
            'id_cot'    => (int) $p['id_cot'],
        ];
    }
}

if (!function_exists('tarifs_grille_admin')) {
    /** Grille "Catégorie | Grade | Mensuel | Trimestriel | Semestriel | Annuel | Statut" pour l'admin. */
    function tarifs_grille_admin(PDO $bdd): array
    {
        $periodes = tarifs_periodes($bdd, false);
        $rows = $bdd->query('SELECT g.id_gd, q.designation, g.nom_gd, g.prix, g.actif FROM grades g
                             JOIN qualites q ON q.id_qt = g.id_qt WHERE g.ancien = 0 ORDER BY g.id_qt, g.ordre, g.id_gd')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['montants'] = [];
            foreach ($periodes as $p) {
                $r['montants'][$p['nom_cot']] = round((float) $r['prix'] * (int) $p['mois'], 2);
            }
        }
        return $rows;
    }
}

if (!function_exists('tarifs_modifier_grade')) {
    /** Modifie le prix mensuel et/ou l'activation d'un grade, avec historique + audit. À protéger par require_permission('tarifs.gerer'). */
    function tarifs_modifier_grade(PDO $bdd, int $idGd, float $nouveauPrix, bool $actif, ?int $idAdm): bool
    {
        if ($nouveauPrix <= 0 || $nouveauPrix > 100000) {
            return false;
        }
        $s = $bdd->prepare('SELECT prix, actif FROM grades WHERE id_gd = ? AND ancien = 0');
        $s->execute([$idGd]);
        $old = $s->fetch(PDO::FETCH_ASSOC);
        if (!$old) {
            return false;
        }
        $bdd->beginTransaction();
        try {
            $bdd->prepare('UPDATE grades SET prix = ?, actif = ? WHERE id_gd = ?')->execute([round($nouveauPrix, 2), $actif ? 1 : 0, $idGd]);
            $bdd->prepare('INSERT INTO grades_historique (id_gd, ancien_prix, nouveau_prix, ancien_actif, nouveau_actif, id_adm) VALUES (?,?,?,?,?,?)')
                ->execute([$idGd, $old['prix'], round($nouveauPrix, 2), $old['actif'], $actif ? 1 : 0, $idAdm]);
            $bdd->commit();
        } catch (Throwable $e) {
            $bdd->rollBack();
            error_log('[tarifs_modifier_grade] ' . $e->getMessage());
            return false;
        }
        audit_log($bdd, 'tarif.modifier', 'grades', (string) $idGd, ['ancien' => $old, 'nouveau' => ['prix' => $nouveauPrix, 'actif' => $actif]]);
        return true;
    }
}

if (!function_exists('tarifs_modifier_periode')) {
    function tarifs_modifier_periode(PDO $bdd, int $idCot, int $mois, bool $actif): bool
    {
        if ($mois < 1 || $mois > 60) {
            return false;
        }
        $ok = $bdd->prepare('UPDATE cotisation SET mois = ?, jours = ?, actif = ? WHERE id_cot = ?')
                  ->execute([$mois, $mois * 30, $actif ? 1 : 0, $idCot]);
        audit_log($bdd, 'periode.modifier', 'cotisation', (string) $idCot, ['mois' => $mois, 'actif' => $actif]);
        return $ok;
    }
}
