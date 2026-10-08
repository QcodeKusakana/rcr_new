<?php
// Faux FlexPay : état par orderNumber dans state.json {order:{status,reference,amount,currency}} ; "__mode" = ok|http500|nonjson
$st = json_decode(@file_get_contents(__DIR__.'/state.json') ?: '{}', true);
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!preg_match('#/check/([A-Za-z0-9]+)$#', $p, $m)) { http_response_code(404); exit; }
$mode = $st['__mode'] ?? 'ok';
if ($mode === 'http500') { http_response_code(500); echo 'Internal error'; exit; }
if ($mode === 'nonjson') { echo '<html>maintenance</html>'; exit; }
header('Content-Type: application/json');
$o = $st[$m[1]] ?? null;
if (!$o) { echo json_encode(['code'=>'1','message'=>'Aucune transaction trouvée','transaction'=>null]); exit; }
echo json_encode(['code'=>'0','message'=>'Une transaction trouvée','transaction'=>['orderNumber'=>$m[1],'reference'=>$m[1],'amount'=>(string)$o['amount'],'amountCustomer'=>(string)$o['amount'],'currency'=>$o['currency'],'createdAt'=>'07-10-2026 10:00:00','status'=>(string)$o['status']]]);
