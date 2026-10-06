# RCR — Audit, corrections et finalisation (octobre 2026)

## 0. À faire de votre côté (2 commandes, dans le terminal Laragon, dossier `C:\laragon\www\rcr_new`)
```
php tools\backup.php                        :: sauvegarde base + fichiers (recommandé avant la migration)
php migrations\phase5_migrate.php           :: simulation : affiche ce qui va être fait
php migrations\phase5_migrate.php --apply   :: application (idempotente, ne supprime rien)
php tests\run_tests.php                     :: tests sans base (doit afficher 0 échec)
```
Ensuite : vider le cache du navigateur (Ctrl+F5) et se reconnecter à l'administration.
Le fichier `rcr_diag_7f3a.php` (racine) est un script neutralisé (404) : vous pouvez le supprimer.

## 1. Méthode
- Copie complète du projet + de votre base `rcr_db` (export fourni) dans un environnement de test isolé
  (PHP 8.3, MariaDB, FlexPay **simulé** — aucun appel réel, aucune donnée envoyée à l'extérieur).
- Lecture du code, exploitation du journal `storage/logs/php-error.log`, parcours automatisés de toutes
  les pages publiques et d'administration (journal d'erreurs vérifié après chaque page), tests de bout en bout.
- Sauvegarde des fichiers d'origine modifiés : `storage/backups/audit_20261005/` (même arborescence).

## 2. Bugs corrigés (constatés et reproduits)
| Zone | Problème | Correction |
|---|---|---|
| Tableau de bord / Paiements (admin) | Erreur 500 « Illegal mix of collations » (MySQL 8 : littéraux en utf8mb4_0900_ai_ci vs tables utf8mb4_unicode_ci) | Connexion PDO unique `includes/db.php` : `SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci` |
| Paiements | Fuseau PHP (UTC) ≠ MySQL (UTC+1) : la règle « échec après 3 min » ne fonctionnait pas | Fuseau Africa/Kinshasa côté PHP et MySQL ; âge calculé par MySQL |
| Territoires (admin) | **Le bouton « Supprimer ce territoire » supprimait une ACTIVITÉ** portant le même n° | Suppression du territoire uniquement s'il n'est plus utilisé (secteurs, membres) + journal |
| Secteurs (admin) | « Bloquer » bloquait toujours le territoire n°1 ; avertissements PHP sans `tr_id` | Territoire réellement concerné ; redirection propre |
| Équipe (admin) | Erreur SQL 1064 (colonne `function` = mot réservé) ; e-mail vide refusé au 2ᵉ membre | Colonne entre accents graves ; e-mail facultatif |
| Activités / Partenaires (admin) | Téléversement impossible (`media/images_activ` et `images_part` inexistants) | Dossiers créés + fonction commune `upload_enregistrer_image()` |
| Vérification (QR) | Erreur fatale « Cannot redeclare url_partage() » | Inclusion unique |
| Actualités | `?pages=categories` sans catégorie → erreur fatale ; détail : redirection après affichage, article non publié visible, XSS possible, 1 requête par compteur | Module `includes/actualites.php` réécrit (requêtes agrégées, pagination bornée, échappement, 404 propre) |
| Détail article | Commentaires sans protection anti-robot ni anti-répétition, double envoi au rechargement | Champ piège, 1 commentaire / 30 s, redirection après envoi |
| Adhésion | 7 avertissements PHP à chaque affichage ; textes mal encodés (« AccÃ©der ») ; liens du menu cassés depuis `adhere/` | Variables définies, encodage réparé, menu commun piloté par la base |
| Contact | Messages perdus (mail() non configuré), adresse en dur, e-mail du visiteur en expéditeur | Messages enregistrés en base + page **Admin → Messages de contact** ; envoi SMTP si configuré |
| Fiche PDF | Avertissement TCPDF (bordure flottante) ; dépréciations QR code PHP 8.1+ | Corrigés (2 lignes du QR code TCPDF) |
| Carte / reçu PDF | Titre coupé, logo absent du reçu | Mise en page corrigée |
| Export CSV | Dépréciation PHP 8.4 `fputcsv` | Paramètre d'échappement explicite |
| Pagination | Décalage négatif (erreur SQL) si `?pag=` sur une liste vide | Bornage partout |
| Pied de page | Lien mort `?pages=texte`, réseaux sociaux en dur / vides | Pied de page piloté par la base |
| Nouvel adhérent | Lien « télécharger ma fiche » refusé (403) juste après l'adhésion | Autorisé pour SA fiche uniquement (session) |
| Tests | Compteurs du script de tests écrasés (« Increment on type bool ») | Corrigé |

## 3. Base de données
- Tables vérifiées : 37 (structure, clés, index, collations, encodage) — toutes en utf8mb4_unicode_ci.
- **Migration `migrations/phase5_migrate.php`** (idempotente, non destructive, simulation par défaut) :
  - table `messages_contact` ;
  - 12 réglages de contenu manquants (adresse complète, téléphone 3, Instagram/TikTok/LinkedIn, titres et
    introductions des pages, texte du pied de page, image SEO) — jamais d'écrasement ;
  - menu du pied de page (`site_menu`, zone `footer`) ;
  - colonnes attendues par le code si absentes (commentaire, likes, dislike, sous_categorie, vu) ;
  - conversion utf8mb4_unicode_ci des tables qui ne le seraient pas ; 3 index supplémentaires.
- Constat : le grade « Sympathisant / Argent » vaut **1 USD** en base (modifié en administration le 04/10/2026,
  barème initial 5 USD). Le code applique le prix en base ; à vérifier s'il s'agit d'un test.

## 4. Sécurité
- CSRF : **tous** les formulaires POST de l'administration exigent désormais le jeton (y compris les pages
  historiques), injecté automatiquement ; refus 403 sinon. Formulaires publics : déjà protégés, vérifiés.
- Accès : fiches PDF, cartes et listes réservées aux administrateurs ayant le droit « membres » (et non plus
  à tout administrateur) ; tests des 4 rôles : pages interdites → 403 ; anonyme → connexion.
- Connexion admin : traitée avant affichage, temps constant (pas de divulgation des pseudos), rehachage auto,
  pseudo unique et mot de passe ≥ 10 caractères à l'inscription (compte inactif tant qu'il n'est pas validé).
- Membres : compte suspendu refusé à la connexion ; déconnexion après 2 h d'inactivité.
- Téléversements : type réel (finfo + getimagesize), nom aléatoire, 5 Mo, dossiers sans exécution PHP —
  testé avec un faux JPEG contenant du PHP : refusé.
- XSS : échappement systématique des messages et des données affichées (actualités, contact, admin).
- Notes d'audit retirées du code HTML envoyé aux visiteurs (elles restent en commentaires PHP).
- `.gitignore` ajouté : `config/database.php`, `config/flexpay.php`, `config/mail.php`, journaux, sauvegardes,
  pièces d'identité/CV ne doivent jamais être versionnés.
- ⚠️ Le jeton FlexPay figure en clair dans `config/flexpay.php` (normal, protégé par .htaccess), mais il a
  circulé dans des conversations : demandez sa régénération à FlexPay avant la mise en ligne.

## 5. FlexPay
Fichiers vérifiés : `config/flexpay.php`, `includes/flexpay_client.php`, `includes/payment_helpers.php`,
`api/flexpay_callback.php`, `adhere/webhook_flexpay.php`, `code_paiement.php`, `paiement.php`, `don_paiement.php`,
`carte_retour.php`, `check_payment.php`, `expire_payment.php`, `loading.php`, `success/failed/pending.php`,
`tools/cron.php`, `admin/pages/paiements.php`.

Scénarios testés (FlexPay simulé) — tous conformes :
1. succès → `paid`, membre activé, période et échéance calculées ; 2. échec → `failed` seulement après 3 min ;
3. en attente → reste ouvert ; 4. délai navigateur dépassé → `expired` (reste ouvert) puis confirmé plus tard → `paid` ;
5. échec puis confirmation tardive → `paid` ; 6. callback rejoué 3 fois → 1 seul enregistrement (`already_paid`) ;
7. référence inconnue → 404 + journal ; 8. montant différent → refusé, journalisé, reste ouvert ;
9. référence différente → refusé ; 10. erreur FlexPay à l'initialisation → message propre ;
11. renouvellement → type `cotisation`, période prolongée à partir de l'échéance ;
12. revérification et annulation depuis l'administration.

Améliorations : idempotence explicite du callback ; `orderNumber` divergent journalisé ; une confirmation FlexPay
**vérifiée** sur une transaction annulée côté site est enregistrée (l'argent a été encaissé) ; anti double
push Mobile Money (< 3 min) ; ancienne URL `/webhook/flexpay` redirigée vers le callback.
Statuts utilisés : pending, processing, paid, failed, cancelled, expired (`expired` remplace `expired_pending`).
Jamais de « payé » sur simple retour navigateur : uniquement après vérification serveur (montant, devise, référence).

## 6. Administration
Nouveau gabarit aux couleurs du RCR (bleu encre / doré) : barre latérale groupée (Pilotage, Membres, Finances,
Site public, Système) filtrée par permissions, menu mobile coulissant, barre supérieure (utilisateur,
déconnexion, lien site), notifications SweetAlert, confirmations modernes, anti double-clic, graphique des
encaissements (6 mois) sur le tableau de bord, page de connexion refaite, nouvelle page « Messages de contact »
(recherche, filtres, statuts, export CSV). Contenus : menus d'en-tête ET de pied de page éditables, traductions
des réglages possibles, seules les valeurs modifiées sont enregistrées (journal plus lisible).

## 7. Site public piloté par la base
Déjà en base : textes des pages, menu, identité, accueil, boutons, coordonnées, SEO. Ajouté : pied de page
(coordonnées, réseaux sociaux, colonnes de liens), page contact (titre, intro, adresse, téléphones, e-mail),
page actualités (titre, intro), menu commun aussi sur la page d'adhésion.
Performance : logos optimisés (1,5 Mo → 0,24 Mo par page ; originaux dans `media/lo/originaux/`),
listes d'actualités en 1 requête au lieu de 4 par article.

## 8. Tests réalisés (environnement de test, copie de votre base)
- Lint PHP de tous les fichiers : 0 erreur. 57 pages publiques + administration : 0 avertissement PHP.
- `tests/run_tests.php` : 54/54 ; `--db` : 167/167 (barème, combinaisons invalides, cycle de paiement, droits).
- Parcours : adhésion complète (photo, validation, paiement, confirmation, fiche PDF + QR décodé → page de
  vérification « Fiche authentique »), connexion membre, espace membre, carte PDF, reçu PDF, accès au reçu d'un
  autre membre (403), déconnexion ; dons ponctuel et régulier, montants 0 / texte refusés ; administration
  (équipe, activité, partenaire avec image, faux JPEG refusé, POST sans jeton → 403, rôle restreint → 403).
- Affichage contrôlé en 390 px et 1366 px (site et administration) : aucun débordement horizontal.

## 9. Limites / points restants
- Non testable depuis l'environnement d'audit : un vrai paiement FlexPay (faire un test réel de 1 USD en Mobile
  Money puis par carte), l'envoi d'e-mails (créer `config/mail.php`), les règles `.htaccess` (Apache).
- Dons réguliers : FlexPay ne propose pas de prélèvement automatique (rappels par `tools/cron.php`).
- Les pages historiques de l'administration (provinces, statistiques…) gardent leur structure : elles
  bénéficient du nouveau gabarit mais n'ont pas été réécrites.
- Avant la mise en ligne : HTTPS dans `.htaccess`, `php tools/go_live_check.php --url=https://rcr.cd` → « PRÊT ».
