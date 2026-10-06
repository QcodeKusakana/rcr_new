# Nouvelle base de données RCR

Fichier : `rcr_nouvelle_base.sql` (fourni à part) — remplace l'import de l'ancien dump + les migrations 1 à 4.

## Pourquoi un nouveau fichier plutôt que l'ancien dump ?
- L'ancien dump référence la table `admin` (ALTER TABLE) **sans la créer** : l'import s'arrête en erreur.
- Il utilise la collation MariaDB `utf8mb3_uca1400_ai_ci`, inconnue de MySQL 8 (Laragon peut tourner sous MySQL).
- Il contient des données de test (2 adhésions, 1 paiement, 2 dons) et l'ancien barème (Super Diamant Fondateur à 3150 $, Platine à 5 $…).

## Ce que la nouvelle base conserve / améliore
| Élément | Traitement |
|---|---|
| Provinces (26), territoires (150), secteurs (761) | **Conservés** à l'identique (mêmes identifiants), nettoyés (espaces), clés étrangères conservées. Contrôlés : aucun orphelin, aucun doublon. |
| Barème | 3 catégories, 12 grades au tarif 2026, 4 périodes (×1, ×3, ×6, ×12) — modifiables en administration. Montants en DECIMAL (plus de FLOAT). |
| Paiements / dons | 6 statuts, type de transaction, canal (Mobile Money / carte), n° de commande FlexPay, périodes couvertes, échéance des dons réguliers. Références uniques. |
| Membres | statut, échéance, mot de passe haché, dernière connexion, parrainage conservé. |
| Sécurité | rôles et permissions (RBAC), journal d'audit, journal des paiements, blocage des tentatives de connexion. |
| Contenus | textes, menu et réglages stockés en base (32 blocs, 34 réglages), éditables en administration. |
| Encodage | utf8mb4 / InnoDB partout. |
| Incohérences corrigées | colonnes manquantes utilisées par le code : `commentaire.(pseudo, commentaire, date_pub)`, `likes.ip`, `dislike.ip`, `sous_categorie.id_cat`. Valeurs par défaut personnelles supprimées dans `equipes` (téléphone et e-mail d'un individu étaient codés en dur). |
| Index | ~45 index (membres, paiements, dons, actualités, compteurs en ligne…). |

## Écarté volontairement (données de test)
Adhésions, paiements, dons, catégorie « Réligions », anciens grades, compteurs de visites. **Les administrateurs ne sont pas repris** : créez-les avec `php migrations/create_admin.php`.

## Installation
1. Créer une base VIDE (ex. `rcr_new`, interclassement utf8mb4_unicode_ci).
2. Importer `rcr_nouvelle_base.sql` (phpMyAdmin ou `mysql -u root rcr_new < rcr_nouvelle_base.sql`).
3. `config/database.php` : pointer vers cette base.
4. `php migrations/create_admin.php`
5. Contrôle : `php tools/go_live_check.php` (tout doit être OK hormis les conseils de production).

Compatibilité : MySQL 5.7+ / MariaDB 10.3+.
Les scripts `migrations/phase1..4_migrate.php` restent utiles pour mettre à niveau une base EXISTANTE ; depuis cette version ils détectent une base déjà migrée et ne suppriment plus rien.

## Ajouts : e-mails et « mot de passe oublié »
- Table `password_resets` (jetons à usage unique, stockés sous forme d'empreinte SHA-256, valables 1 h).
- Pages `?pages=mdp_oublie` et `?pages=mdp_reset` ; lien « Mot de passe oublié ? » sur la page de connexion.
- Envoi d'e-mails par SMTP : copier `config/mail.example.php` en `config/mail.php` et renseigner le compte de messagerie de l'hébergeur.
  - Test : `php tools/send_notifications.php --test=votre@adresse.com`
  - File d'attente : `*/5 * * * *  php /chemin/site/tools/send_notifications.php` (ou via `tools/cron.php`, qui l'appelle aussi).
  - Messages prêts : adhésion confirmée, paiement confirmé, échéance proche (J-7, J-1), cotisation expirée, don reçu, rappel de don régulier.
- Les SMS ne sont pas envoyés (aucune API SMS disponible) : ils restent en file dans `notifications`.
- Sans `config/mail.php`, le site fonctionne ; seul « mot de passe oublié » n'enverra rien (le message reste générique pour ne pas révéler l'existence d'un compte).
