<?php
/**
 * Page « adhesions » : la logique est commune avec l'autre liste (admin/menu/adherents_liste.php).
 * L'ancien bouton « Confirmer » (validation manuelle via trans_keys) a été retiré : la validation est
 * automatique dès que FlexPay confirme l'encaissement. L'ancienne suppression par lien GET (qui effaçait
 * aussi l'historique des paiements) est remplacée par une suppression POST limitée aux demandes jamais payées.
 */
$adhMode = 'valides';
