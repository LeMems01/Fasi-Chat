<?php
$error = $error ?? null;
$csrfToken = $csrfToken ?? Session::getInstance()->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — FasiChat Classroom</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
<div class="login-bg">
  <div class="login-shapes">
    <div class="shape shape-1"></div> 
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="login-container">
    <div class="login-left">
      <div class="login-brand">
        <i class="fas fa-graduation-cap brand-icon-lg"></i>
        <h1>FasiChat<br><span>Classroom</span></h1>
      </div>
      <p class="login-tagline">Plateforme de messagerie académique sécurisée pour votre établissement d'enseignement supérieur.</p>
      <div class="login-features">
        <div class="feat"><i class="fas fa-lock"></i> Messagerie chiffrée par rôle</div>
        <div class="feat"><i class="fas fa-bell"></i> Convocations officielles</div>
        <div class="feat"><i class="fas fa-bullhorn"></i> Valve institutionnelle</div>
      </div>
    </div>
    <div class="login-right">
      <div class="login-card">
        <div class="login-card-header">
          <h2>Connexion</h2>
          <p>Accédez à votre espace académique</p>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=login" class="login-form">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
          <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Adresse email</label>
            <input type="email" id="email" name="email" 
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                   placeholder="votre@email.edu" required autocomplete="email">
          </div>
          <div class="form-group">
            <label for="password"><i class="fas fa-key"></i> Mot de passe</label>
            <div class="input-eye">
              <input type="password" id="password" name="password" 
                     placeholder="••••••••" required autocomplete="current-password">
              <button type="button" class="eye-toggle" onclick="togglePwd()">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn-login">
            <span>Se connecter</span><i class="fas fa-arrow-right"></i>
          </button>
        </form>
        <div class="login-hint">
          <details>
            <summary><i class="fas fa-info-circle"></i> Comptes de démonstration</summary>
            <div class="demo-accounts">
              <div class="demo-row"><b>Doyen :</b> doyen@fasichat.edu</div>
              <div class="demo-row"><b>Vice-Doyen :</b> vdoyen@fasichat.edu</div>
              <div class="demo-row"><b>Apparitaire :</b> apparitaire@fasichat.edu</div>
              <div class="demo-row"><b>Enseignant :</b> enseignant1@fasichat.edu</div>
              <div class="demo-row"><b>Assistant :</b> assistant@fasichat.edu</div>
              <div class="demo-row"><b>Étudiant :</b> etudiant1@fasichat.edu</div>
              <div class="demo-row"><b>Mot de passe :</b> password123</div>
            </div>
          </details>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
function togglePwd(){
  var p=document.getElementById('password');
  var i=document.getElementById('eyeIcon');
  if(p.type==='password'){p.type='text';i.className='fas fa-eye-slash';}
  else{p.type='password';i.className='fas fa-eye';}
}
</script>
</body>
</html>
