<?php
// Ancien gestionnaire de contact, remplacé par pages/contact.php (qui vérifie le jeton CSRF).
// Neutralisé : il était atteignable directement via ?pages=contactcode, sans CSRF, avec une adresse
// de destination en dur. Toute requête est renvoyée vers le formulaire officiel.
header('Location: ?pages=contact');
exit;
