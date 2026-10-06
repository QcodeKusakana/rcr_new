<?php
/**
 * Fichier mort (découverte lors de la modernisation) : le routeur central
 * (index.php) inclut automatiquement functions/<page>.funct.php pour
 * chaque page — pour "detail", il cherche donc "detail.funct.php". Ce
 * fichier s'appelle "detail.functdd.php" (faute de frappe dans le nom),
 * il n'a donc JAMAIS été chargé automatiquement.
 *
 * Sa logique (récupération de l'article, likes/dislikes/commentaires) a
 * depuis été réécrite et intégrée directement dans pages/code_detail.php,
 * qui est bien inclus par pages/detail.php et qui corrige au passage le
 * bug de $categoriez (table sous_categorie déjà signalée comme
 * incompatible dans le rapport d'audit).
 *
 * Conservé vide (plutôt que supprimé) pour l'historique, au cas où le nom
 * de fichier aurait été utilisé ailleurs par erreur — vérifié : aucune
 * référence trouvée dans tout le projet.
 */
