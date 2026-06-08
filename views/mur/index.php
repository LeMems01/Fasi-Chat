<?php define('_FASICHAT_', true); ?>
<?php
$cours = $cours ?? null;
$utilisateur = $utilisateur ?? null;
$publications = $publications ?? [];
$flashes = $flashes ?? [];
$csrfToken = $csrfToken ?? Session::getInstance()->getCsrfToken();
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title"><i class="fas fa-chalkboard"></i> Mur pédagogique</h1>
      <p class="page-sub"><?= htmlspecialchars($cours ? $cours->getTitre() : 'Mur pédagogique') ?></p>
    </div>
    <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant'])): ?>
    <button class="btn btn-primary" onclick="toggleForm()">
      <i class="fas fa-plus"></i> Nouvelle publication
    </button>
    <?php endif; ?>
  </div>

  <?php foreach ($flashes as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <i class="fas <?= $flash['type']==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Formulaire de publication (Enseignant/Assistant) -->
  <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant'])): ?>
  <div class="mur-form-wrap" id="murForm" style="display:none">
    <div class="card">
      <div class="card-header"><i class="fas fa-edit"></i> Nouvelle publication</div>
      <div class="card-body">
        <form method="POST" action="index.php?page=mur&action=post&cours=<?= (int)($cours ? $cours->getId() : 0) ?>">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
          <div class="form-group">
            <label>Message / Question</label>
            <textarea name="contenu" rows="4" placeholder="Rédigez votre message pour les étudiants…" required></textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn btn-ghost" onclick="toggleForm()">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publier</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Publications -->
  <div class="mur-publications">
    <?php if (empty($publications)): ?>
    <div class="empty-state">
      <i class="fas fa-chalkboard"></i>
      <h3>Aucune publication</h3>
      <p>Le mur pédagogique de ce cours est vide pour l'instant.</p>
    </div>
    <?php else: ?>
    <?php foreach ($publications as $pub): ?>
    <div class="mur-post card">
      <div class="mur-post-header">
        <div class="mur-author">
          <div class="mur-avatar" style="background:#059669">
            <?= strtoupper(mb_substr($pub['exp_prenom'],0,1) . mb_substr($pub['exp_nom'],0,1)) ?>
          </div>
          <div>
            <div class="mur-author-name">
              <?= htmlspecialchars($pub['exp_prenom'] . ' ' . $pub['exp_nom']) ?>
            </div>
            <div class="mur-author-role">
              <?= htmlspecialchars(ucfirst($pub['exp_role'])) ?>
            </div>
          </div>
        </div>
        <div class="mur-date">
          <i class="fas fa-clock"></i>
          <?= date('d/m/Y à H:i', strtotime($pub['created_at'])) ?>
        </div>
      </div>
      <div class="mur-post-body">
        <?= nl2br(htmlspecialchars($pub['contenu'])) ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>
<script>
function toggleForm() {
  var f = document.getElementById('murForm');
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
</script>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
