<?php
/**
 * Client HTTP FlexPay — aucune logique métier ici, seulement les appels API.
 *
 * Endpoints de PRODUCTION fournis par FlexPay pour le marchand RCR
 * (modifiables ci-dessous sans toucher au reste du code).
 * Le jeton (FLEXPAY_TOKEN) et le code marchand (FLEXPAY_MERCHANT) restent dans
 * config/flexpay.php, jamais ici et jamais dans le HTML/JS public.
 */

// Ce fichier est chargé sur TOUTES les pages (via bootstrap). Si config/flexpay.php est absent (poste de
// développement, installation en cours), le site doit continuer à s'afficher : seuls les paiements sont
// alors indisponibles, avec un message clair, au lieu d'une erreur 500 sur tout le site.
if (is_file(__DIR__ . '/../config/flexpay.php')) {
    require_once __DIR__ . '/../config/flexpay.php';
}
if (!defined('FLEXPAY_TOKEN'))    { define('FLEXPAY_TOKEN', ''); }
if (!defined('FLEXPAY_MERCHANT')) { define('FLEXPAY_MERCHANT', ''); }

// Base publique du site (URL de retour carte). config/flexpay.php peut la définir. À défaut : l'hôte local en
// développement (localhost, *.test, *.local), sinon https://rcr.cd. L'en-tête Host d'un visiteur distant n'est jamais utilisé.
if (!defined('SITE_URL')) {
    $h = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    $local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
          && ($h === 'localhost' || $h === '127.0.0.1' || preg_match('/\.(test|local|localhost)$/', $h));
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    define('SITE_URL', $local ? (($https ? 'https://' : 'http://') . (string) $_SERVER['HTTP_HOST']) : 'https://rcr.cd');
}
if (!defined('FLEXPAY_MOBILE_URL'))  { define('FLEXPAY_MOBILE_URL', 'https://backend.flexpay.cd/api/rest/v1/paymentService'); }
if (!defined('FLEXPAY_CARD_URL'))    { define('FLEXPAY_CARD_URL', 'https://cardpayment.flexpay.cd/v1.1/pay'); }
if (!defined('FLEXPAY_CHECK_URL'))   { define('FLEXPAY_CHECK_URL', 'https://apicheck.flexpaie.com/api/rest/v1/check/'); }
// URL publique appelée par FlexPay en tâche de fond (code "0" = succès). En local elle n'est pas joignable par
// FlexPay : la confirmation passe alors par la vérification active (adhere/check_payment.php).
if (!defined('FLEXPAY_CALLBACK_ENDPOINT')) { define('FLEXPAY_CALLBACK_ENDPOINT', rtrim((string) SITE_URL, '/') . '/api/flexpay_callback.php'); }

if (!function_exists('flexpay_normalize_phone')) {
    /** 0812345678 / +243812345678 / 243812345678 -> 243812345678 ; null si invalide. */
    function flexpay_normalize_phone(string $phone): ?string
    {
        $d = preg_replace('/\D+/', '', $phone);
        if (strpos($d, '00243') === 0) { $d = substr($d, 2); }
        if (strpos($d, '0') === 0 && strlen($d) === 10) { $d = '243' . substr($d, 1); }
        if (strlen($d) === 9) { $d = '243' . $d; }
        return preg_match('/^243\d{9}$/', $d) ? $d : null;
    }
}

if (!function_exists('flexpay_amount')) {
    /** Montant au format attendu par l'API : "10" ou "10.5" (pas de séparateur de milliers). */
    function flexpay_amount(float $amount): string
    {
        return rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
    }
}

if (!function_exists('flexpay_http')) {
    /**
     * @return array{ok:bool, status:int, json:?array, raw:string, error:string}
     */
    function flexpay_http(string $method, string $url, ?array $body = null): array
    {
        if ((string) FLEXPAY_TOKEN === '') {
            return ['ok' => false, 'status' => 0, 'json' => null, 'raw' => '', 'error' => 'FlexPay non configuré (config/flexpay.php absent ou jeton vide)'];
        }
        $headers = [
            'Authorization: Bearer ' . FLEXPAY_TOKEN,
            'Accept: application/json',
        ];
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $errno !== 0) {
            error_log("[flexpay_http] $method $url errno=$errno $error" . ($errno === 60 ? ' — certificats CA absents : renseigner curl.cainfo dans php.ini (Laragon : C:\\laragon\\bin\\php\\...\\extras\\ssl\\cacert.pem)' : ''));
            return ['ok' => false, 'status' => 0, 'json' => null, 'raw' => '', 'error' => $error ?: 'réseau'];
        }
        $json = json_decode((string) $raw, true);
        return [
            'ok'     => $status >= 200 && $status < 300 && is_array($json),
            'status' => $status,
            'json'   => is_array($json) ? $json : null,
            'raw'    => (string) $raw,
            'error'  => is_array($json) ? '' : 'réponse non JSON',
        ];
    }
}

if (!function_exists('flexpay_request_mobile')) {
    /**
     * Déclenche un push Mobile Money (type 1). Réponse attendue : code "0" + orderNumber.
     * @return array{ok:bool, orderNumber:?string, message:string}
     */
    function flexpay_request_mobile(string $reference, string $phone, float $amount, string $currency, ?string $callbackUrl = null): array
    {
        $msisdn = flexpay_normalize_phone($phone);
        if ($msisdn === null) {
            return ['ok' => false, 'orderNumber' => null, 'message' => 'Numéro de téléphone invalide.'];
        }
        $r = flexpay_http('POST', FLEXPAY_MOBILE_URL, [
            'merchant'    => FLEXPAY_MERCHANT,
            'type'        => '1',
            'phone'       => $msisdn,
            'reference'   => $reference,
            'amount'      => flexpay_amount($amount),
            'currency'    => strtoupper($currency),
            'callbackUrl' => $callbackUrl ?: FLEXPAY_CALLBACK_ENDPOINT,
        ]);
        $j = $r['json'] ?? [];
        $ok = $r['ok'] && (string) ($j['code'] ?? '1') === '0' && !empty($j['orderNumber']);
        return [
            'ok'          => $ok,
            'orderNumber' => $ok ? (string) $j['orderNumber'] : null,
            'message'     => (string) ($j['message'] ?? ($r['error'] ?: 'Échec de la demande de paiement.')),
        ];
    }
}

if (!function_exists('flexpay_request_card')) {
    /**
     * Génère l'URL de paiement par carte (Visa/MasterCard) puis il faut REDIRIGER le client.
     * Corps conforme à la doc "Payment Service (V2)" ; l'endpoint est celui fourni par FlexPay (v1.1).
     * @return array{ok:bool, orderNumber:?string, url:?string, message:string}
     */
    function flexpay_request_card(string $reference, float $amount, string $currency, string $description, string $approveUrl, string $cancelUrl, string $declineUrl, ?string $callbackUrl = null): array
    {
        $r = flexpay_http('POST', FLEXPAY_CARD_URL, [
            'authorization' => 'Bearer ' . FLEXPAY_TOKEN,
            'merchant'      => FLEXPAY_MERCHANT,
            'reference'     => $reference,
            'amount'        => flexpay_amount($amount),
            'currency'      => strtoupper($currency),
            'description'   => mb_substr($description, 0, 120),
            'callback_url'  => $callbackUrl ?: FLEXPAY_CALLBACK_ENDPOINT,
            'approve_url'   => $approveUrl,
            'cancel_url'    => $cancelUrl,
            'decline_url'   => $declineUrl,
        ]);
        $j = $r['json'] ?? [];
        $ok = $r['ok'] && (string) ($j['code'] ?? '1') === '0' && !empty($j['url']) && !empty($j['orderNumber']);
        return [
            'ok'          => $ok,
            'orderNumber' => $ok ? (string) $j['orderNumber'] : null,
            'url'         => $ok ? (string) $j['url'] : null,
            'message'     => (string) ($j['message'] ?? ($r['error'] ?: 'Échec de la demande de paiement.')),
        ];
    }
}

if (!function_exists('flexpay_check_order')) {
    /**
     * Vérification serveur d'une transaction (GET .../check/{orderNumber}).
     *
     * IMPORTANT (constaté en conditions réelles) : dans la réponse du check, `transaction.reference` contient
     * le numéro de commande FlexPay (orderNumber) et NON la référence RCR envoyée à l'init. Les deux valeurs
     * sont donc exposées séparément ('reference' brut + 'order') et c'est l'appelant qui décide de ce qui est cohérent.
     * status "0" = réussie, "1" = n'a pas abouti ; toute autre valeur (ex. "4" observé) = non concluant.
     *
     * @return array{found:bool, status:?string, reference:?string, order:?string, amount:?float, currency:?string,
     *               http:int, message:string, error:string}|null  null si FlexPay est injoignable / réponse illisible.
     */
    function flexpay_check_order(string $orderNumber): ?array
    {
        $vide = ['found' => false, 'status' => null, 'reference' => null, 'order' => null, 'amount' => null, 'currency' => null, 'http' => 0, 'message' => '', 'error' => ''];
        if (!preg_match('/^[A-Za-z0-9]{10,60}$/', $orderNumber)) {
            return ['error' => 'format_order_number'] + $vide;
        }
        $r = flexpay_http('GET', FLEXPAY_CHECK_URL . rawurlencode($orderNumber));
        if ($r['json'] === null) {
            return null;
        }
        $base = ['http' => (int) $r['status'], 'message' => mb_substr((string) ($r['json']['message'] ?? ''), 0, 200)] + $vide;
        $t = $r['json']['transaction'] ?? null;
        if (!is_array($t)) {
            return $base; // « transaction non trouvée »
        }
        return [
            'found'     => true,
            'status'    => isset($t['status']) ? (string) $t['status'] : null,
            'reference' => isset($t['reference']) ? (string) $t['reference'] : null,
            'order'     => isset($t['orderNumber']) ? (string) $t['orderNumber'] : null,
            'amount'    => isset($t['amount']) ? (float) $t['amount'] : null,
            'currency'  => isset($t['currency']) ? strtoupper((string) $t['currency']) : null,
        ] + $base;
    }
}
