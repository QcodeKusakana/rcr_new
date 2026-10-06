<?php
/**
 * Envoi d'e-mails par SMTP (sans bibliothèque externe). Ne lève jamais d'exception : retourne [ok, message].
 * Configuration : config/mail.php (voir config/mail.example.php). Sans configuration, mail_configure() = false
 * et rien n'est envoyé (les messages restent en file).
 */
if (is_file(__DIR__ . '/../config/mail.php')) {
    require_once __DIR__ . '/../config/mail.php';
}

if (!function_exists('mail_configure')) {
    function mail_configure(): bool
    {
        return defined('MAIL_HOST') && MAIL_HOST !== '' && defined('MAIL_USER') && defined('MAIL_PASS') && defined('MAIL_FROM')
            && stripos((string) MAIL_PASS, 'MOT_DE_PASSE') === false;
    }
}

if (!function_exists('mail_encode_header')) {
    function mail_encode_header(string $s): string
    {
        return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
    }
}

if (!function_exists('mail_envoyer')) {
    /**
     * @return array{0:bool,1:string} [succès, message d'erreur éventuel]
     */
    function mail_envoyer(string $to, string $sujet, string $html, string $texte = ''): array
    {
        if (!mail_configure()) {
            return [false, 'e-mail non configuré (config/mail.php)'];
        }
        // Anti injection d'en-têtes : aucune fin de ligne dans les champs d'en-tête
        $to = trim(str_replace(["\r", "\n"], '', $to));
        $sujet = str_replace(["\r", "\n"], ' ', $sujet);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return [false, 'adresse destinataire invalide'];
        }
        $host = (string) MAIL_HOST;
        $port = defined('MAIL_PORT') ? (int) MAIL_PORT : 465;
        $secure = defined('MAIL_SECURE') ? strtolower((string) MAIL_SECURE) : 'ssl';
        $fromName = defined('MAIL_FROM_NAME') ? (string) MAIL_FROM_NAME : '';

        $errno = 0; $errstr = '';
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
        $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 12, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            error_log("[mail] connexion SMTP impossible $host:$port ($errno $errstr)");
            return [false, "connexion SMTP impossible ($errno)"];
        }
        stream_set_timeout($fp, 15);

        $lire = function () use ($fp): array {
            $code = 0; $texteRep = '';
            while (($l = fgets($fp, 1024)) !== false) {
                $texteRep .= $l;
                $code = (int) substr($l, 0, 3);
                if (isset($l[3]) && $l[3] === ' ') { break; }
            }
            return [$code, trim($texteRep)];
        };
        $cmd = function (string $c, array $ok) use ($fp, $lire): array {
            fwrite($fp, $c . "\r\n");
            [$code, $rep] = $lire();
            return [in_array($code, $ok, true), $code . ' ' . $rep];
        };
        $fin = function (string $msg) use ($fp): array {
            @fwrite($fp, "QUIT\r\n"); @fclose($fp);
            error_log('[mail] ' . $msg);
            return [false, $msg];
        };

        [$c, ] = $lire();
        if ($c !== 220) { return $fin('bannière SMTP inattendue'); }
        $ehlo = (string) ($_SERVER['SERVER_NAME'] ?? 'localhost');
        [$ok, $r] = $cmd('EHLO ' . preg_replace('/[^A-Za-z0-9.\-]/', '', $ehlo), [250]);
        if (!$ok) { return $fin('EHLO refusé : ' . $r); }
        if ($secure === 'tls') {
            [$ok, $r] = $cmd('STARTTLS', [220]);
            if (!$ok || !@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { return $fin('STARTTLS échoué'); }
            [$ok, $r] = $cmd('EHLO ' . preg_replace('/[^A-Za-z0-9.\-]/', '', $ehlo), [250]);
            if (!$ok) { return $fin('EHLO (TLS) refusé'); }
        }
        [$ok, $r] = $cmd('AUTH LOGIN', [334]);
        if (!$ok) { return $fin('AUTH non supporté : ' . $r); }
        [$ok, $r] = $cmd(base64_encode((string) MAIL_USER), [334]);
        if (!$ok) { return $fin('identifiant refusé'); }
        [$ok, $r] = $cmd(base64_encode((string) MAIL_PASS), [235]);
        if (!$ok) { return $fin('authentification SMTP refusée (vérifier MAIL_USER / MAIL_PASS)'); }
        [$ok, $r] = $cmd('MAIL FROM:<' . MAIL_FROM . '>', [250]);
        if (!$ok) { return $fin('expéditeur refusé : ' . $r); }
        [$ok, $r] = $cmd('RCPT TO:<' . $to . '>', [250, 251]);
        if (!$ok) { return $fin('destinataire refusé : ' . $r); }
        [$ok, $r] = $cmd('DATA', [354]);
        if (!$ok) { return $fin('DATA refusé'); }

        $bord = 'rcr_' . bin2hex(random_bytes(8));
        if ($texte === '') { $texte = trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>#i', "\n", $html)), ENT_QUOTES, 'UTF-8')); }
        $msg  = 'Date: ' . date('r') . "\r\n";
        $msg .= 'From: ' . ($fromName !== '' ? mail_encode_header($fromName) . ' ' : '') . '<' . MAIL_FROM . ">\r\n";
        $msg .= 'To: <' . $to . ">\r\n";
        $msg .= 'Subject: ' . mail_encode_header($sujet) . "\r\n";
        $msg .= 'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . preg_replace('/[^A-Za-z0-9.\-]/', '', $ehlo) . ">\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= 'Content-Type: multipart/alternative; boundary="' . $bord . "\"\r\n\r\n";
        $msg .= '--' . $bord . "\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($texte)) . "\r\n";
        $msg .= '--' . $bord . "\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n";
        $msg .= '--' . $bord . "--\r\n";
        fwrite($fp, $msg . "\r\n.\r\n");
        [$code, $rep] = $lire();
        if ($code !== 250) { return $fin('message refusé : ' . $code . ' ' . $rep); }
        @fwrite($fp, "QUIT\r\n"); @fclose($fp);
        return [true, ''];
    }
}
