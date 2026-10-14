# RCR — Site officiel du Rassemblement des Chrétiens Républicains

Site web officiel du **Rassemblement des Chrétiens Républicains (R.C.R)**, parti politique de la République démocratique du Congo : présentation du mouvement, actualités et événements, adhésion en ligne, dons et cotisations, avec un back-office d'administration.

- **Production** : <https://rcr.cd>
- **Local (Laragon)** : `http://rcr_new.test`

> Ce dépôt contient le **site web** (PHP / MySQL). L'application mobile (Flutter) utilise la même base de données et les mêmes identifiants, mais elle vit dans un dépôt séparé.

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Structure du projet](#structure-du-projet)
4. [Prérequis](#prérequis)
5. [Installation en local](#installation-en-local)
6. [Configuration](#configuration)
7. [Paiements (FlexPay)](#paiements-flexpay)
8. [Sécurité](#sécurité)
9. [Déploiement](#déploiement)
10. [Workflow Git](#workflow-git)
11. [Licence et contact](#licence-et-contact)

---

## Fonctionnalités

**Site public**

- Page d'accueil : bandeau d'accueil avec diaporama, « Notre Maître », « Qui sommes-nous », Président du Parti, Événements (onglets « Où en sommes-nous » et « Rendez-vous » avec compte à rebours automatique), textes fondamentaux, Nos Idées Forces.
- Page « Qui sommes-nous » : chapitres alimentés depuis l'administration (présentation officielle, fondateurs, exécutif national, valeurs, objectifs).
- Actualités et activités, galerie photo, partenaires.
- Adhésion en ligne, cotisation / renouvellement, dons.
- Contact.

**Back-office (`/admin`)**

- Gestion des contenus : actualités, activités, galerie, partenaires, équipe et instances.
- Gestion des chapitres de la page « Qui sommes-nous » (table de blocs).
- Réglages du site (textes d'accueil, titres, sous-titres) via la fonction `reglage()`.
- Suivi des membres, adhésions, cotisations et dons.

## Stack technique

| Couche | Technologie |
|---|---|
| Langage | PHP (procédural), PDO |
| Base de données | MySQL / MariaDB |
| Front | HTML5, CSS3, JavaScript, Bootstrap 5, Bootstrap Icons |
| Typographie | Fraunces, IBM Plex Sans (Google Fonts) |
| Paiement | FlexPay (Mobile Money / carte) |
| Serveur local | Laragon (Apache + PHP + MySQL) |
| Intégration continue mobile | GitHub Actions (dépôt de l'application) |

## Structure du projet

> Arborescence indicative : adapte-la si ton dépôt diffère.

```text
rcr_new/
├── index.php                 # Point d'entrée et routeur (?pages=...)
├── pages/                    # Pages publiques (home.php, apropos.php, ...)
├── functions/                # Requêtes et logique par page (home.funct.php, ...)
├── adhere/                   # Parcours d'adhésion (adhesion.php, ...)
├── admin/                    # Back-office d'administration
│   └── media/                # Fichiers téléversés depuis l'admin (équipe, ...)
├── assets/                   # CSS, JS, images du thème
├── media/                    # Médias publics (hero, galerie, partenaires, ...)
└── README.md
```

Le routage fonctionne par paramètre d'URL : `index.php?pages=apropos` charge la page `apropos`.
Les fonctions utilitaires communes (`e()` pour l'échappement HTML, `reglage()` pour les réglages du site, `blocs()` / `bloc_rendu()` pour les chapitres) sont définies dans le dossier `functions/`.

## Prérequis

- PHP **8.1 ou supérieur** avec les extensions `pdo_mysql`, `mbstring`, `intl` (recommandée), `curl`, `fileinfo`, `gd`
- MySQL **8** ou MariaDB **10.6+**
- Apache avec `mod_rewrite` (ou Nginx équivalent)
- Git
- [Laragon](https://laragon.org) (recommandé sous Windows)

## Installation en local

1. **Cloner le dépôt** dans le dossier web de Laragon :

   ```bash
   cd C:\laragon\www
   git clone https://github.com/<ton-compte>/<ton-depot>.git rcr_new
   cd rcr_new
   ```

2. **Créer la base de données** :

   ```sql
   CREATE DATABASE rcr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importer le schéma** (fichier SQL fourni hors dépôt ou dans `database/`, à adapter) :

   ```bash
   mysql -u root -p rcr < database/schema.sql
   ```

4. **Configurer la connexion** (voir [Configuration](#configuration)).

5. **Créer les dossiers de téléversement** s'ils n'existent pas, avec droits d'écriture :
   `media/`, `admin/media/`.

6. Démarrer Laragon, puis ouvrir `http://rcr_new.test` (Laragon crée l'hôte virtuel automatiquement).

## Configuration

Les identifiants ne doivent **jamais** être écrits en dur dans un fichier versionné.

1. Copie le modèle de configuration :

   ```bash
   cp config.example.php config.php
   ```

2. Renseigne dans `config.php` :

   ```php
   <?php
   return [
       'db' => [
           'host'    => '127.0.0.1',
           'name'    => 'rcr',
           'user'    => 'root',
           'pass'    => '',
           'charset' => 'utf8mb4',
       ],
       'site' => [
           'url' => 'http://rcr_new.test',
       ],
       'flexpay' => [
           'merchant' => '',
           'token'    => '',
           'callback' => 'http://rcr_new.test/adhere/callback.php',
       ],
   ];
   ```

3. `config.php` est exclu de Git (voir `.gitignore`). Seul `config.example.php`, sans aucun secret, est versionné.

## Paiements (FlexPay)

- Les paiements (adhésion, cotisation, dons) passent par FlexPay : Mobile Money et carte bancaire.
- Les clés (`merchant`, `token`) se placent uniquement dans `config.php`, jamais dans le code ni dans l'historique Git.
- Utilise les identifiants de **test** en local et les identifiants **de production** uniquement sur le serveur.
- L'URL de rappel (callback) doit être joignable publiquement : en local, utilise un tunnel (ngrok, Cloudflare Tunnel) pour tester la confirmation de paiement.

## Sécurité

- Requêtes SQL **préparées** (PDO) : aucune concaténation de variables dans les requêtes.
- Échappement systématique des sorties HTML avec `e()`.
- Mots de passe stockés **hachés** (`password_hash` / `password_verify`).
- Jetons **CSRF** sur tous les formulaires qui modifient des données.
- Contrôle des téléversements : type MIME, taille maximale, nom de fichier régénéré.
- Dossier `admin/` protégé par authentification ; envisage une restriction supplémentaire (IP ou HTTP Auth).
- Ne jamais versionner : `config.php`, sauvegardes `.sql` contenant des données réelles, fichiers téléversés, clés FlexPay.

Une faille de sécurité s'écarte du suivi public : signale-la en privé à l'équipe technique plutôt que d'ouvrir une *issue* publique.

## Déploiement

1. Sur le serveur, mettre le site en maintenance ou planifier une fenêtre courte.
2. Récupérer la dernière version :

   ```bash
   git pull origin main
   ```

3. Appliquer les éventuelles modifications de base de données (scripts du dossier `database/migrations/`).
4. Vérifier `config.php` de production et les droits d'écriture sur `media/` et `admin/media/`.
5. Vider le cache navigateur / CDN si nécessaire, puis contrôler : accueil, `?pages=apropos`, adhésion, un paiement de test.

Sauvegarde avant chaque mise en production :

```bash
mysqldump -u <user> -p rcr > sauvegarde-$(date +%F).sql
```

## Workflow Git

Branches :

| Branche | Rôle |
|---|---|
| `main` | Version en production, toujours stable |
| `develop` | Intégration des évolutions en cours |
| `feature/<sujet>` | Une fonctionnalité ou une modification |
| `fix/<sujet>` | Correction de bug |

Messages de commit (convention *Conventional Commits*) :

```text
feat(accueil): ajoute la légende sous la photo du Maître
fix(evenements): corrige le compte à rebours du rendez-vous phare
docs(readme): complète l'installation locale
```

Cycle de travail :

```bash
git checkout develop
git pull
git checkout -b feature/idees-forces
# ... modifications ...
git add .
git commit -m "feat(accueil): affiche les 5 idées forces"
git push -u origin feature/idees-forces
# puis ouvrir une Pull Request vers develop
```

## Licence et contact

Projet **propriétaire** : tous droits réservés © Rassemblement des Chrétiens Républicains (R.C.R). Toute reproduction ou réutilisation sans autorisation écrite est interdite.

Contact technique : *à compléter (adresse e-mail de l'équipe technique)*.
