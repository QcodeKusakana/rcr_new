<?php
/**
 * Formulaire de contact : chaque message est ENREGISTRÉ (table messages_contact, consultable en administration),
 * puis transmis par e-mail si le SMTP est configuré (config/mail.php). Jeton CSRF, champ piège anti-robot,
 * limite de 3 messages / 10 min par session, redirection après envoi (pas de double envoi au rechargement).
 */
require_once __DIR__ . '/../includes/mailer.php';

$errors = null;
$sms    = $_SESSION['contact_ok'] ?? null;
unset($_SESSION['contact_ok']);
$contactSaisie = ['name' => '', 'mail' => '', 'objet' => '', 'message' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['send'])) {
    foreach ($contactSaisie as $k => $_) {
        $contactSaisie[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    $nom     = mb_substr(str_replace(["\r", "\n"], ' ', $contactSaisie['name']), 0, 120);
    $mail    = mb_substr(str_replace(["\r", "\n"], '', $contactSaisie['mail']), 0, 150);
    $objet   = mb_substr(str_replace(["\r", "\n"], ' ', $contactSaisie['objet']), 0, 200);
    $message = mb_substr($contactSaisie['message'], 0, 5000);
    $hist    = array_filter((array) ($_SESSION['contact_envois'] ?? []), fn($t) => $t > time() - 600);

    if (!csrf_verify()) {
        $errors = "Session expirée, merci de recharger la page et réessayer.";
    } elseif (trim((string) ($_POST['site_web'] ?? '')) !== '') {
        $errors = "Votre message n'a pas pu être envoyé.";
    } elseif ($nom === '' || $mail === '' || $objet === '' || $message === '') {
        $errors = "Tous les champs doivent être complétés.";
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $errors = "Merci de saisir une adresse e-mail valide.";
    } elseif (count($hist) >= 3) {
        $errors = "Vous avez déjà envoyé plusieurs messages. Merci de réessayer dans quelques minutes.";
    } else {
        try {
            $bdd->prepare('INSERT INTO messages_contact (nom, email, objet, message, ip) VALUES (?, ?, ?, ?, ?)')
                ->execute([$nom, $mail, $objet, $message, substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)]);
        } catch (Throwable $e) {
            error_log('[contact] enregistrement : ' . $e->getMessage()); // table absente : migration phase 5 à lancer
        }
        $dest = reglage('contact_email', 'contact@rcr.cd');
        if (mail_configure() && filter_var($dest, FILTER_VALIDATE_EMAIL)) {
            $html = '<p><strong>' . e($nom) . '</strong> (' . e($mail) . ') a écrit via le site :</p>'
                  . '<p><strong>Objet :</strong> ' . e($objet) . '</p><p>' . nl2br(e($message)) . '</p>';
            [$ok, $err] = mail_envoyer($dest, 'Contact site RCR : ' . $objet, $html, "$nom ($mail)\n\n$message");
            if (!$ok) { error_log('[contact] e-mail non envoyé : ' . $err); }
        }
        $hist[] = time();
        $_SESSION['contact_envois'] = $hist;
        $_SESSION['contact_ok'] = 'Merci ' . $nom . ', votre message a bien été transmis. Nous vous répondrons rapidement.';
        header('Location: ?pages=contact#contact');
        exit;
    }
}
