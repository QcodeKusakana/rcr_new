# RCR — Phase 4 (tests, sécurité, optimisation, mise en production) — archive CUMULATIVE (Phases 1 à 4)

Remplace toutes les archives précédentes : copier par-dessus le site, puis lancer les migrations.

## Ordre d'installation sur un site neuf ou à migrer
1. Sauvegarde complète : `php tools/backup.php` (base + fichiers) ET copie manuelle hors du serveur.
2. Copier l'archive (config/flexpay.php et config/database.php ne sont PAS touchés).
3. `php migrations/phase1_migrate.php` puis `--apply`  (UNE seule fois : elle vide les données de test)
4. `php migrations/create_admin.php`
5. `php migrations/phase2_migrate.php --apply`
6. `php migrations/phase3_migrate.php --apply`
7. `php migrations/phase4_migrate.php --apply`   (index de performance)
8. Chez FlexPay : URL de callback = https://rcr.cd/api/flexpay_callback.php
9. Tâches planifiées (cPanel → Tâches cron) :
   - tous les jours à 02h00 : `php /chemin/site/tools/cron.php >> /chemin/site/storage/logs/cron.log 2>&1`
   - toutes les 15 min    : `php /chemin/site/tools/cron.php --paiements`
   - tous les jours à 03h00 : `php /chemin/site/tools/backup.php`
10. Activer la redirection HTTPS dans `.htaccess` (bloc commenté) dès que le certificat SSL est actif.
11. Supprimer du serveur : `rcr.sql`, `README_*.md`, le dossier `tests/` (ou le laisser protégé) et tout fichier .zip.
12. **Contrôle final** : `php tools/go_live_check.php --url=https://rcr.cd`  → doit afficher « PRÊT ».

## Ce que contient la Phase 4
| Domaine | Éléments |
|---|---|
| Tests | `tests/run_tests.php` (logique pure + contrôle des fichiers) ; `--db` : barème complet (7 grades × 4 périodes), combinaisons invalides, cycle de vie d'un paiement avec FlexPay SIMULÉ (montant falsifié, mauvaise référence/devise, confirmation tardive, rejeu, annulation, renouvellement), droits des 4 rôles. Refuse de tourner hors d'une base dont le nom contient « test ». |
| Mise en production | `tools/go_live_check.php` (PHP, extensions, config, droits, base, données, jeton FlexPay et sa date d'expiration, tests HTTP externes de fichiers sensibles/en-têtes/callback) |
| Tâches planifiées | `tools/cron.php` : rattrapage des paiements restés ouverts, passage des membres en « expiré », rappels J-7/J-1 et après expiration, rappels de dons réguliers, nettoyage |
| Sauvegarde | `tools/backup.php` (base compressée + fichiers, rotation) |
| Erreurs | pages 403 / 404 / 500 sobres, erreurs PHP journalisées dans `storage/logs/php-error.log`, jamais affichées |
| SEO | titres/descriptions/Open Graph dynamiques (`includes/seo.php`), `sitemap.xml` généré (cache 1 h), `robots.txt`, URLs propres (anciennes URLs conservées) |
| Performance | compression, cache navigateur, index SQL (`phase4_migrate.php`), compteurs « en ligne » ramenés à ≤ 1 passage SQL / 30 s / session, pagination sur toutes les listes admin |
| Sécurité (corrigé en Phase 4) | commentaires publics : CSRF + plus de fuite d'erreur SQL + redirection assainie ; `contactcode.php` (sans CSRF, adresse en dur) et `forms/contact.php` (modèle non installé) neutralisés ; pagination admin typée en entier ; uploads : type MIME réel, nom aléatoire, PHP interdit dans `media/` |

## Plan de tests manuels (à dérouler avant l'ouverture)
**Adhésion** : un membre par catégorie (Fondateur, Effectif, Sympathisant) × chaque période ; vérifier le montant affiché = tableau du cahier des charges ; paiement Mobile Money réel de 1 USD puis carte ; compte créé, connexion, carte PDF + QR, reçu PDF.
**Cotisation** : renouvellement anticipé (l'échéance se prolonge), expiration (changer une échéance en base à hier puis `cron.php`).
**Dons** : ponctuel et régulier, montant libre ; refus de 0, négatif, texte, vide ; reçu PDF.
**Paiement** : fermer le navigateur avant validation puis valider sur le téléphone (rattrapage par callback/cron) ; annuler sur la page carte ; échec carte ; rejouer le callback (aucun doublon).
**Admin** : se connecter avec chacun des 4 rôles et tenter les pages interdites (403 attendu) ; modifier un tarif et vérifier le journal ; modifier un texte et le voir sur le site ; 6 mauvais mots de passe → blocage.
**Sécurité** : formulaire sans jeton CSRF → refus ; `<script>` dans un commentaire/contenu → neutralisé ; téléverser un .php renommé en .jpg → refus ; accéder à /config/, /storage/, /includes/, /migrations/ → 403/404.
**Responsive** : 320, 375, 390, 430, 768, 1024, 1366, 1440, 1920 px sur l'accueil, l'adhésion, « Je soutiens », l'espace membre et les tableaux d'administration (Chrome → outils de développement → mode appareil).
**Performance** : Lighthouse (mobile) sur l'accueil et les actualités ; viser ≥ 80 en performance.

## Limites connues (honnêteté sur l'état du projet)
- Rien de ce code n'a pu être exécuté pendant sa rédaction (PHP/MySQL indisponibles). Les contrôles faits : équilibre syntaxique, fonctions et colonnes référencées, scan statique de sécurité. Les tests ci-dessus sont donc à passer pour de vrai.
- Les notifications (e-mail, SMS, interne) sont mises en file dans la table `notifications` mais aucun envoi n'est branché : il faut un accès SMTP et/ou une API SMS.
- Don « régulier » : FlexPay n'offre pas de prélèvement automatique ; le système enregistre l'engagement et relance le donateur, chaque versement se paie manuellement.
- Pas de « mot de passe oublié » (dépend de l'envoi d'e-mails).
- Secteurs : la table n'en contient que 2 pour 116 territoires ; les adhérents ne trouveront pas leur secteur tant qu'elle n'est pas complétée.
- Le jeton FlexPay et les identifiants du portail marchand ont circulé dans une conversation : changez le mot de passe du portail marchand et demandez un nouveau jeton si possible.
- Anciennes tables incohérentes avec leur code d'origine : `sous_categorie.id_cat`, `likes.ip`, `dislike.ip`, `commentaire.(commentaire, pseudo, date_pub)` — à contrôler en production.
