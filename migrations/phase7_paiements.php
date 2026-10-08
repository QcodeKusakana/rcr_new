<?php
/**
 * PHASE 7 — Fiabilisation du paiement FlexPay (suivi de vérification + contraintes d'unicité).
 *   php migrations/phase7_paiements.php            -> simulation
 *   php migrations/phase7_paiements.php --apply    -> exécute
 * Idempotent, non destructif. Sauvegarde recommandée : php tools/backup.php
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$apply = in_array('--apply', $argv ?? [], true);
require_once dirname(__DIR__) . '/includes/db.php';
$bdd = rcr_db_connect();
$bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$say = fn(string $m) => print(($apply ? '[OK ] ' : '[SIM] ') . $m . "\n");
echo $apply ? "=== PHASE 7 : APPLICATION ===\n" : "=== PHASE 7 : SIMULATION ===\n";

$col = function (string $t, string $c) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
    $s->execute([$t, $c]); return (int) $s->fetchColumn() > 0;
};
$idx = function (string $t, string $i) use ($bdd): bool {
    $s = $bdd->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?');
    $s->execute([$t, $i]); return (int) $s->fetchColumn() > 0;
};
$run = function (string $sql, string $label) use ($bdd, $apply, $say) {
    if ($apply) { $bdd->exec($sql); }
    $say($label);
};

foreach (['payments', 'dons'] as $t) {
    foreach ([
        'flexpay_status'   => 'VARCHAR(20) NULL',
        'derniere_verif'   => 'DATETIME NULL',
        'nb_verifs'        => 'INT UNSIGNED NOT NULL DEFAULT 0',
        'dernier_callback' => 'DATETIME NULL',
        'derniere_erreur'  => 'VARCHAR(250) NULL',
    ] as $c => $def) {
        if (!$col($t, $c)) { $run("ALTER TABLE `$t` ADD COLUMN `$c` $def", "$t.$c ajoutée"); } else { echo "[ -- ] $t.$c existe\n"; }
    }
    $uq = $t === 'payments' ? 'uq_payments_order' : 'uq_dons_order';
    if (!$idx($t, $uq)) {
        $dup = (int) $bdd->query("SELECT COUNT(*) FROM (SELECT order_number FROM `$t` WHERE order_number IS NOT NULL GROUP BY order_number HAVING COUNT(*) > 1) x")->fetchColumn();
        if ($dup > 0) {
            echo "[WARN] $t : $dup order_number en double — UNIQUE non posé. Corrigez les doublons (SELECT order_number, COUNT(*) FROM $t GROUP BY 1 HAVING COUNT(*)>1) puis relancez.\n";
        } else {
            $run("ALTER TABLE `$t` ADD UNIQUE KEY `$uq` (order_number)", "$t : UNIQUE(order_number)");
        }
    } else { echo "[ -- ] $t.$uq existe\n"; }
}
foreach ([['payments', 'idx_payments_transaction', 'transaction_id(100)'], ['payments', 'idx_payments_adhesion_id', 'adhesion_id'],
          ['dons', 'idx_dons_transaction', 'transaction_id(100)'], ['payment_logs', 'idx_pl_ref_date', 'reference, cree_le']] as [$t, $i, $cols]) {
    if (!$idx($t, $i)) { $run("ALTER TABLE `$t` ADD KEY `$i` ($cols)", "$t : index $i"); } else { echo "[ -- ] $t.$i existe\n"; }
}
echo $apply ? "Terminé.\n" : "Simulation terminée — relancez avec --apply.\n";
