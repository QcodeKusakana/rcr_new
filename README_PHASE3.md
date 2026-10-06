# RCR — Phase 3 (administration) — archive CUMULATIVE (Phases 1 + 2 + 3)

Remplace les archives précédentes : copier par-dessus le site, puis lancer les migrations.

## Ordre d'installation
1. Sauvegarde complète (mysqldump + copie des fichiers).
2. Copier l'archive (config/flexpay.php et config/database.php ne sont PAS touchés).
3. `php migrations/phase1_migrate.php` puis `--apply`  (une seule fois : elle vide les données de test)
4. `php migrations/create_admin.php`                 (crée l'administrateur principal)
5. `php migrations/phase2_migrate.php --apply`      (menu public)
6. `php migrations/phase3_migrate.php --apply`      (file de notifications + index)
7. Chez FlexPay : callback = https://rcr.cd/api/flexpay_callback.php

## Administration (admin/index.php?pages=…) — chaque page vérifie sa permission côté serveur
| Page | Permission | Contenu |
|---|---|---|
| dashboard | dashboard.voir | membres, nouveaux, actifs, expirés, cotisations et dons du mois, paiements en attente/échoués, échéances proches |
| membres | membres.voir / membres.gerer | recherche, filtres, export CSV, fiche PDF, suspension / réactivation |
| cotisations | paiements.voir | membre, catégorie, grade, fréquence, montant, période, échéance, filtres actif/expiré/en attente/payé/impayé |
| dons | paiements.voir | ponctuel/régulier, membre/non-membre, période, montant, moyen, statut, export |
| paiements | paiements.voir / paiements.valider | toutes transactions, revérification FlexPay, annulation, reçu PDF |
| tarifs | tarifs.gerer | prix mensuel, périodes, activation, ajout de grade, historique |
| contenus | contenu.gerer | textes des pages, réglages (identité, accueil, boutons, contacts, SEO), menu public, 5 langues prévues |
| administrateurs | admins.gerer | comptes, rôles, activation, mot de passe, matrice des droits |
| journaux | logs.voir | actions admin, événements de paiement, blocages de connexion |

Rôles : administrateur principal (tout) ; responsable publications (contenu, médias) ;
gestionnaire des effectifs (membres, adhésions, provinces, paiements en lecture) ;
responsable numérique (système, administrateurs, journaux, tarifs, paiements en lecture).

## Garde-fous
- Seul un administrateur principal peut créer, promouvoir, modifier ou désactiver un administrateur principal.
- On ne peut ni se désactiver ni changer son propre rôle ; au moins un administrateur principal actif reste toujours.
- Aucun bouton « marquer payé » : un paiement n'est validé que si FlexPay le confirme côté serveur.
- Le HTML saisi dans « Contenus » est nettoyé (scripts, attributs on*, liens javascript:).
- Les anciennes pages (publications, équipes, provinces…) sont conservées et protégées par le même système de permissions.

## Non testé en exécution (PHP/MySQL indisponibles pendant la rédaction)
Lancer d'abord les simulations, puis tester : connexion de chaque rôle (et accès refusé aux pages interdites),
modification d'un tarif, d'un texte et du menu, filtres/exports, revérification d'un paiement, création d'un administrateur.

## Compléments apportés lors de la vérification finale
- Connexion admin : blocage temporaire après 5 échecs (même mécanisme que la connexion membre), journalisation des connexions, échecs, blocages et déconnexions ; expiration de session après 30 min d'inactivité.
- `migrations/phase3_migrate.php` ajoute aussi la colonne `dons.prochaine_echeance` (dons réguliers) ; sans elle la confirmation d'un don régulier aurait échoué. La planification de l'échéance ne peut plus bloquer une confirmation de paiement.
- La table `login_attempts` (anti brute-force) n'existait pas dans le dump fourni : `phase3_migrate.php` la crée si besoin.
- Anciennes tables inchangées mais déjà incohérentes avec leur code dans l'archive d'origine (non liées à cette refonte) : `sous_categorie.id_cat`, `likes.ip`, `dislike.ip`, `commentaire.(commentaire, pseudo, date_pub)`. À contrôler sur la base de production.
