# RCR — Phase 1 (base de données, tarifs, rôles, paiement FlexPay, contenus en BDD)

## Contenu de l'archive (même arborescence que le site : copier par-dessus)
- migrations/phase1_migrate.php, create_admin.php, seed_contenu.json
- includes/ : payment_helpers.php (remplace l'existant), flexpay_client.php, tarifs.php, rbac.php, audit.php, contenu.php
- api/flexpay_callback.php, adhere/webhook_flexpay.php (renvoie vers le callback)
- .htaccess (racine) + .htaccess dans config/, includes/, storage/, migrations/, api/

## Installation (dans cet ordre)
1. Sauvegarde complète : `mysqldump rcrcd_rcr > sauvegarde.sql` + copie des fichiers.
2. Copier l'archive sur le site (config/flexpay.php et config/database.php ne sont PAS touchés).
3. Simulation : `php migrations/phase1_migrate.php`
4. Application : `php migrations/phase1_migrate.php --apply`
5. Créer l'administrateur principal : `php migrations/create_admin.php`
6. Chez FlexPay, la callback est : https://rcr.cd/api/flexpay_callback.php (l'ancienne URL reste valide).

## Points à valider
- Endpoint carte : https://cardpayment.flexpay.cd/v1.1/pay (fourni par FlexPay) ; le corps suit la doc "Payment Service V2".
- Vérification : https://apicheck.flexpaie.com/... (domaine différent de backend.flexpay.cd, comme indiqué par FlexPay).
- La table admin est vidée par la migration : l'étape 5 est obligatoire pour se reconnecter.
- Non testé en exécution : PHP/MySQL ne sont pas disponibles dans l'environnement de rédaction. Lancer la simulation d'abord, puis tester un paiement réel de 1 USD.
