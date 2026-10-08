-- PHASE 7 — Fiabilisation FlexPay. Idempotent via migrations/phase7_paiements.php (recommandé : vérifie l'existant).
-- Sauvegarde AVANT : php tools/backup.php
-- Version SQL pure (phpMyAdmin) : exécuter chaque ligne ; une erreur "Duplicate column/key" = déjà appliquée, ignorer.

ALTER TABLE payments ADD COLUMN flexpay_status  VARCHAR(20) NULL,
                     ADD COLUMN derniere_verif  DATETIME NULL,
                     ADD COLUMN nb_verifs       INT UNSIGNED NOT NULL DEFAULT 0,
                     ADD COLUMN dernier_callback DATETIME NULL,
                     ADD COLUMN derniere_erreur VARCHAR(250) NULL;
ALTER TABLE dons     ADD COLUMN flexpay_status  VARCHAR(20) NULL,
                     ADD COLUMN derniere_verif  DATETIME NULL,
                     ADD COLUMN nb_verifs       INT UNSIGNED NOT NULL DEFAULT 0,
                     ADD COLUMN dernier_callback DATETIME NULL,
                     ADD COLUMN derniere_erreur VARCHAR(250) NULL;

-- UNIQUE(order_number) : plusieurs NULL autorisés (transactions pas encore initialisées)
-- Vérifier d'abord : SELECT order_number, COUNT(*) FROM payments WHERE order_number IS NOT NULL GROUP BY 1 HAVING COUNT(*) > 1;
ALTER TABLE payments ADD UNIQUE KEY uq_payments_order (order_number),
                     ADD KEY idx_payments_transaction (transaction_id(100)),
                     ADD KEY idx_payments_adhesion_id (adhesion_id);
ALTER TABLE dons     ADD UNIQUE KEY uq_dons_order (order_number),
                     ADD KEY idx_dons_transaction (transaction_id(100));
ALTER TABLE payment_logs ADD KEY idx_pl_ref_date (reference, cree_le);
