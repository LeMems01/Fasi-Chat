<?php define('_FASICHAT_', true); ?>
<?php
$csrfToken = $csrfToken ?? Session::getInstance()->getCsrfToken();
$flashes   = $flashes ?? [];
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title"><i class="fas fa-calendar-plus"></i> Nouvelle convocation</h1>
      <p class="page-sub">Envoyer une convocation à tous les enseignants et assistants</p>
    </div>
    <a href="index.php?page=convocations" class="btn btn-ghost">
      <i class="fas fa-arrow-left"></i> Retour
    </a>
  </div>

  <?php foreach ($flashes ?? [] as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
  <?php endforeach; ?>

  <div class="form-page-wrap">
    <div class="card">
      <div class="card-header">
        <i class="fas fa-edit"></i> Rédiger la convocation
        <span class="card-header-badge">
          <i class="fas fa-users"></i> Tous les enseignants &amp; assistants
        </span>
      </div>
      <div class="card-body">
        <form method="POST" action="index.php?page=convocations&action=envoyer" class="convoc-form">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">

          <div class="form-row">
            <div class="form-group full">
              <label><i class="fas fa-heading"></i> Objet de la réunion *</label>
              <input type="text" name="objet" required
                     placeholder="Ex: Réunion pédagogique du département"
                     value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row two-col">
            <div class="form-group">
              <label><i class="fas fa-calendar-day"></i> Date et heure de la réunion *</label>
              <input type="datetime-local" name="date_reunion" required
                     value="<?= htmlspecialchars($_POST['date_reunion'] ?? '') ?>"
                     min="<?= date('Y-m-d\TH:i') ?>">
            </div>
            <div class="form-group">
              <label><i class="fas fa-map-marker-alt"></i> Lieu ou lien de réunion *</label>
              <input type="text" name="lieu" required
                     placeholder="Ex: Salle A201 ou https://meet.google.com/..."
                     value="<?= htmlspecialchars($_POST['lieu'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group full">
              <label><i class="fas fa-comment-alt"></i> Message explicatif (optionnel)</label>
              <textarea name="message_explicatif" rows="5"
                        placeholder="Détails supplémentaires, ordre du jour, documents à préparer…"><?= htmlspecialchars($_POST['message_explicatif'] ?? '') ?></textarea>
            </div>
          </div>

          <div class="form-info-box">
            <i class="fas fa-info-circle"></i>
            <span>Cette convocation sera envoyée automatiquement à <strong>tous les enseignants et assistants</strong> enregistrés sur la plateforme.</span>
          </div>

          <div class="form-actions">
            <a href="index.php?page=convocations" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Envoyer la convocation
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
