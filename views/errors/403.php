<?php define('_FASICHAT_', true); ?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>
<main class="main-content">
  <div class="error-page">
    <div class="error-code">403</div>
    <h2>Accès refusé</h2>
    <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <a href="index.php?page=dashboard" class="btn btn-primary"><i class="fas fa-home"></i> Retour au tableau de bord</a>
  </div>
</main>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
