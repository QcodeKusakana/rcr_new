<?php
require '/tmp/fpmock/pre.php'; require '/home/claude/rcr/includes/bootstrap.php'; require '/home/claude/rcr/includes/payment_helpers.php';
echo payment_flexpay_check($bdd,'adhesion',(int)$argv[1]);
