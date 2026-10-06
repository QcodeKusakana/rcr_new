<?php include('code_paiement.php'); ?>

<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement adhésion</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    background:
        linear-gradient(rgba(8,15,30,.88), rgba(8,15,30,.88)),
        url('../assets/img/bg.jpg');
    background-size: cover;
    background-position: center;
    min-height: 100vh;
    font-family: 'Segoe UI', sans-serif;
}

.payment-wrapper{
    min-height: 100vh;
    display:flex;
    align-items:center;
}

.payment-card{
    border:none;
    border-radius:28px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
}

.card-header-custom{
    background:linear-gradient(135deg,#dc3545,#b02a37);
    color:#fff;
    padding:40px 30px;
    text-align:center;
}

.logo{
    width:90px;
    height:90px;
    object-fit:cover;
    border-radius:50%;
    background:#fff;
    padding:6px;
}

.payment-title{
    font-size:30px;
    font-weight:800;
    margin-top:15px;
}

.payment-subtitle{
    opacity:.9;
}

.info-box{
    border:1px solid #eee;
    border-radius:18px;
    overflow:hidden;
}

.info-box .table{
    margin-bottom:0;
}

.info-box th{
    background:#f8f9fa;
    width:40%;
    font-weight:600;
}

.total-box{
    background:linear-gradient(135deg,#198754,#20c997);
    color:#fff;
    border-radius:20px;
    padding:25px;
    text-align:center;
    margin-top:25px;
}

.total-box h1{
    font-size:45px;
    font-weight:900;
}

.form-control{
    height:58px;
    border-radius:14px;
}

.btn-pay{
    height:60px;
    border:none;
    border-radius:16px;
    font-size:18px;
    font-weight:700;
    background:linear-gradient(135deg,#dc3545,#bb2d3b);
    transition:.3s;
}

.btn-pay:hover{
    transform:translateY(-2px);
}

.secure-box{
    background:#f8f9fa;
    border-left:5px solid #198754;
    border-radius:12px;
    padding:15px;
    font-size:14px;
    color:#555;
}

.loading-box{
    display:none;
}

</style>

</head>

<body>
<div class="container payment-wrapper">
  <div class="row justify-content-center w-100">
    <div class="col-lg-7">
      <div class="card payment-card">
        <div class="card-header-custom">
          <img loading="lazy" decoding="async" src="../media/lo/logorcr.png" class="logo" alt="logo">
          <div class="payment-title"><?= $typeTransaction === 'adhesion' ? 'Paiement de l\'adhésion' : 'Paiement de la cotisation' ?></div>
          <div class="payment-subtitle">Paiement sécurisé via FlexPay</div>
        </div>
        <div class="card-body p-4 p-md-5">
          <?= $message ?>
          <div class="loading-box text-center mb-4" id="loadingBox">
            <div class="spinner-border text-danger mb-3"></div>
            <h5>Initialisation du paiement...</h5>
            <p class="text-muted">Veuillez patienter, ne fermez pas cette page.</p>
          </div>
          <div class="info-box mb-4">
            <table class="table table-bordered align-middle">
              <tr><th>Code membre</th><td><?= htmlspecialchars($user['codes']) ?></td></tr>
              <tr><th>Nom complet</th><td><?= htmlspecialchars($user['nom'] . ' ' . $user['postnom'] . ' ' . $user['prenom']) ?></td></tr>
              <tr><th>Catégorie</th><td>Membre <?= htmlspecialchars($user['designation']) ?></td></tr>
              <tr><th>Grade</th><td><?= htmlspecialchars($user['nom_gd']) ?></td></tr>
              <tr><th>Mode de cotisation</th><td><?= htmlspecialchars((string) $user['nom_cot']) ?><?= $tarif ? ' (' . (int) $tarif['mois'] . ' mois)' : '' ?></td></tr>
              <tr><th>Montant à payer</th><td><strong><?= number_format($montant, 2, ',', ' ') ?> USD</strong></td></tr>
            </table>
          </div>
          <?php if ($tarif): ?>
          <form method="POST" onsubmit="showLoading()">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label fw-bold">Moyen de paiement</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="canal" id="canal_mm" value="mobile_money" checked>
                <label class="form-check-label" for="canal_mm">Mobile Money (M-Pesa, Airtel Money, Orange Money…)</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="canal" id="canal_carte" value="carte">
                <label class="form-check-label" for="canal_carte">Carte bancaire (Visa / MasterCard)</label>
              </div>
            </div>
            <div class="mb-4" id="blocTel">
              <label class="form-label fw-bold">Numéro Mobile Money</label>
              <input type="tel" name="telephone" id="telephone" class="form-control form-control-lg" placeholder="0812345678"
                     value="<?= htmlspecialchars($_POST['telephone'] ?? $user['telephone']) ?>" inputmode="tel" autocomplete="tel">
              <small class="text-muted">Vous recevrez une demande de confirmation sur ce téléphone.</small>
            </div>
            <button type="submit" name="btn_payer" class="btn btn-danger btn-lg w-100">
              Payer <?= number_format($montant, 2, ',', ' ') ?> USD
            </button>
          </form>
          <?php endif; ?>
          <div class="text-center text-muted mt-4 small">Vos données sont protégées. Le montant est calculé par le serveur.</div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function showLoading(){ document.getElementById('loadingBox').style.display = 'block'; }
(function(){
  var mm=document.getElementById('canal_mm'), card=document.getElementById('canal_carte'), bloc=document.getElementById('blocTel'), tel=document.getElementById('telephone');
  function sync(){ var m=mm&&mm.checked; if(bloc){bloc.style.display=m?'block':'none';} if(tel){tel.required=!!m;} }
  if(mm){mm.addEventListener('change',sync);} if(card){card.addEventListener('change',sync);} sync();
})();
</script>
</body>
</html>
