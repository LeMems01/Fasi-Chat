<?php define('_FASICHAT_', true); ?>
<?php
$utilisateur = $utilisateur ?? null;
$annonces    = $annonces ?? [];
$flashes     = $flashes ?? [];
$csrfToken   = $csrfToken ?? Session::getInstance()->getCsrfToken();
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title"><i class="fas fa-bullhorn"></i> Valve institutionnelle</h1>
      <p class="page-sub">Annonces et informations officielles de l'établissement</p>
    </div>
    <?php if ($utilisateur && $utilisateur->peutGererValve()): ?>
    <button class="btn btn-primary" onclick="toggleModal('addModal')">
      <i class="fas fa-plus"></i> Nouvelle annonce
    </button>
    <?php endif; ?>
  </div>

  <?php foreach ($flashes as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <i class="fas <?= $flash['type']==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Grille d'annonces -->
  <div class="valve-grid">
    <?php if (empty($annonces)): ?>
    <div class="empty-state full-width">
      <i class="fas fa-bullhorn"></i>
      <h3>Aucune annonce</h3>
      <p>La Valve est vide pour l'instant.</p>
    </div>
    <?php else: ?>
    <?php foreach ($annonces as $annonce): ?>
    <div class="valve-card card <?= !$annonce->estActive() ? 'expired' : '' ?>">
      <div class="valve-card-header">
        <div class="valve-card-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="valve-card-meta">
          <h3 class="valve-titre"><?= htmlspecialchars($annonce->getTitre()) ?></h3>
          <div class="valve-date">
            <i class="fas fa-calendar"></i>
            <?= date('d/m/Y', strtotime($annonce->getCreatedAt())) ?>
            <?php if ($annonce->getDateExpiration()): ?>
            &nbsp;— expire le <?= date('d/m/Y', strtotime($annonce->getDateExpiration())) ?>
            <?php endif; ?>
          </div>
        </div>
        <?php if (!$annonce->estActive()): ?>
        <span class="badge-expired">Expirée</span>
        <?php endif; ?>
      </div>
      <div class="valve-card-body">
        <?= nl2br(htmlspecialchars($annonce->getContenu())) ?>
      </div>
      <?php if ($utilisateur && $utilisateur->peutGererValve()): ?>
      <div class="valve-card-footer">
        <button class="btn btn-sm btn-warning"
          onclick="ouvrirEdition(<?= $annonce->getId() ?>, '<?= addslashes(htmlspecialchars($annonce->getTitre())) ?>', '<?= addslashes(htmlspecialchars($annonce->getContenu())) ?>', '<?= htmlspecialchars($annonce->getDateExpiration() ?? '') ?>')">
          <i class="fas fa-edit"></i> Modifier
        </button>
        <form method="POST" action="index.php?page=valve&action=supprimer" style="display:inline"
              onsubmit="return confirm('Supprimer cette annonce ?')">
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="id" value="<?= $annonce->getId() ?>">
          <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

<!-- Modal Ajouter -->
<?php if ($utilisateur && $utilisateur->peutGererValve()): ?>
<div class="modal-backdrop" id="addModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-plus"></i> Nouvelle annonce</h3>
      <button class="modal-close" onclick="toggleModal('addModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="index.php?page=valve&action=ajouter">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
      <div class="modal-body">
        <div class="form-group">
          <label>Titre *</label>
          <input type="text" name="titre" required placeholder="Ex: Inscriptions pédagogiques ouvertes">
        </div>
        <div class="form-group">
          <label>Contenu *</label>
          <textarea name="contenu" rows="5" required placeholder="Contenu de l'annonce…"></textarea>
        </div>
        <div class="form-group">
          <label>Date d'expiration (optionnel)</label>
          <input type="date" name="date_expiration" min="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="toggleModal('addModal')">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Publier</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Modifier -->
<div class="modal-backdrop" id="editModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-edit"></i> Modifier l'annonce</h3>
      <button class="modal-close" onclick="toggleModal('editModal')"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" action="index.php?page=valve&action=modifier">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="id" id="editId">
      <div class="modal-body">
        <div class="form-group">
          <label>Titre *</label>
          <input type="text" name="titre" id="editTitre" required>
        </div>
        <div class="form-group">
          <label>Contenu *</label>
          <textarea name="contenu" id="editContenu" rows="5" required></textarea>
        </div>
        <div class="form-group">
          <label>Date d'expiration (optionnel)</label>
          <input type="date" name="date_expiration" id="editExp" min="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="toggleModal('editModal')">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function toggleModal(id) {
  var m = document.getElementById(id);
  m.style.display = m.style.display === 'none' ? 'flex' : 'none';
}
function ouvrirEdition(id, titre, contenu, exp) {
  document.getElementById('editId').value    = id;
  document.getElementById('editTitre').value = titre;
  document.getElementById('editContenu').value = contenu;
  document.getElementById('editExp').value   = exp;
  toggleModal('editModal');
}
document.querySelectorAll('.modal-backdrop').forEach(function(m) {
  m.addEventListener('click', function(e) { if (e.target === m) m.style.display = 'none'; });
});
</script>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
