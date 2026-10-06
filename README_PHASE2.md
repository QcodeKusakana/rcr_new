# RCR — Phase 2 (site public + espace membre) — archive CUMULATIVE (Phase 1 + Phase 2)

Cette archive contient tous les fichiers nouveaux ou modifiés par rapport à l'archive d'origine rcr.zip.
Elle remplace celle de la Phase 1 : copier par-dessus le site, puis lancer les migrations.

## Ordre d'installation
1. Sauvegarde complète (mysqldump + copie des fichiers).
2. Copier l'archive sur le site (config/flexpay.php et config/database.php ne sont pas touchés).
3. `php migrations/phase1_migrate.php`            (simulation)   puis   `--apply`
4. `php migrations/create_admin.php`              (la table admin est vidée par la phase 1)
5. `php migrations/phase2_migrate.php --apply`   (menu public piloté par la base)
6. Chez FlexPay : callback = https://rcr.cd/api/flexpay_callback.php

## Ce que contient la Phase 2
- Textes en base : apropos, mention, politique-confidentialite, accueil lisent site_blocs / site_reglages ; menu public lu dans site_menu (ancien menu conservé en secours).
- Adhésion pas à pas (adhere/adhesion.php + adhesion.funct.php) : catégorie → grade → cotisation (mensuelle à annuelle) → informations → localisation → documents → parrain/encadreur → récapitulatif et mentions légales ; montants recalculés côté serveur depuis la base ; mot de passe du compte (password_hash).
- Paiement : adhere/paiement.php (Mobile Money ou carte), retour carte adhere/carte_retour.php, vérification serveur systématique.
- Soutien (pages/soutenir.php + adhere/don_paiement.php) : montant libre en USD, ponctuel ou régulier, fréquence.
- Espace membre : connexion code ou e-mail + mot de passe, tableau de bord (statut, échéance, paiements), carte membre PDF avec QR (member/carte.php), reçu PDF (member/recu.php), vérification publique (pages=verifier).

## À tester absolument (rien n'a pu être exécuté : PHP/MySQL indisponibles pendant la rédaction)
1. Simulation des migrations, puis application sur une copie de la base.
2. Adhésion complète avec un paiement réel de 1 USD en Mobile Money, puis par carte.
3. Don ponctuel et don régulier.
4. Connexion membre, carte PDF, reçu PDF, page de vérification via le QR.
5. Menu, pages « Qui sommes-nous », mentions légales, politique de confidentialité.

## Limites connues
- Don « régulier » : FlexPay ne propose pas de prélèvement automatique ; le système enregistre l'engagement et la fréquence, chaque versement se paie manuellement. Les rappels d'échéance relèvent de la couche notifications (Phase 3/4).
- Pas de « mot de passe oublié » : il demande l'envoi d'e-mails (à brancher en Phase 3).
- Éditeur des textes en administration : Phase 3 (en attendant, modification directe des tables site_blocs / site_reglages).
