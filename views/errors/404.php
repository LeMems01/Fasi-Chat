<?php define('_FASICHAT_', true); ?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>
<main class="main-content">
  <div class="error-page">
    <div class="error-code">404</div>
    <h2>Page introuvable</h2>
    <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
    <a href="index.php?page=dashboard" class="btn btn-primary"><i class="fas fa-home"></i> Retour au tableau de bord</a>
  </div>
</main>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
