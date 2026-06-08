<?php define('_FASICHAT_', true); ?>
<?php
$utilisateur = $utilisateur ?? null;
$flashes     = $flashes ?? [];
$promotions  = $promotions ?? [];
$enseignants = $enseignants ?? [];
$csrfToken   = $csrfToken ?? Session::getInstance()->getCsrfToken();
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title"><i class="fas fa-book-open"></i> Cours</h1>
      <p class="page-sub">Liste des cours par promotion</p>
    </div>
  </div>

  <?php foreach ($flashes as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <i class="fas <?= $flash['type']==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Tableau des cours -->
  <div class="card">
    <div class="card-header"><i class="fas fa-list"></i> Tous les cours</div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Cours</th>
            <th>Promotion</th>
            <th>Enseignants affectés</th>
            <?php if ($utilisateur && $utilisateur->getRole() !== 'apparitaire'): ?>
            <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($cours)): ?>
          <tr><td colspan="4" class="table-empty">Aucun cours enregistré.</td></tr>
          <?php else: ?>
          <?php foreach ($cours as $c): ?>
          <?php $ensOfCours = $c->getEnseignants(); ?>
          <tr>
            <td>
              <div class="table-titre"><?= htmlspecialchars($c->getTitre()) ?></div>
              <?php if ($c->getDescription()): ?>
              <div class="table-sub"><?= htmlspecialchars(mb_strimwidth($c->getDescription(),0,80,'…')) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php
                $promo = null;
                foreach ($promotions as $p) { if ($p->getId() === $c->getPromotionId()) { $promo = $p; break; } }
                echo $promo ? htmlspecialchars($promo->getNomComplet()) : '—';
              ?>
            </td>
            <td>
              <?php if (empty($ensOfCours)): ?>
                <span class="text-muted">Non assigné</span>
              <?php else: ?>
                <?php foreach ($ensOfCours as $ens): ?>
                <div class="ens-chip" style="background:<?= htmlspecialchars($ens->getRoleCouleur()) ?>20;color:<?= htmlspecialchars($ens->getRoleCouleur()) ?>">
                  <?= htmlspecialchars($ens->getNomComplet()) ?>
                  <?php if ($utilisateur && $utilisateur->peutAffecter()): ?>
                  <form method="POST" action="index.php?page=cours&action=retirer" style="display:inline">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="enseignant_id" value="<?= $ens->getId() ?>">
                    <input type="hidden" name="cours_id" value="<?= $c->getId() ?>">
                    <button type="submit" class="ens-remove" title="Retirer" onclick="return confirm('Retirer cet enseignant ?')">
                      <i class="fas fa-times"></i>
                    </button>
                  </form>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <?php if ($utilisateur && $utilisateur->getRole() !== 'apparitaire'): ?>
            <td>
              <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant','etudiant'])): ?>
              <a href="index.php?page=mur&cours=<?= (int)$c->getId() ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-chalkboard"></i> Mur
              </a>
              <?php endif; ?>
              <?php if ($utilisateur && $utilisateur->peutAffecter() && !empty($enseignants)): ?>
              <button class="btn btn-sm btn-success"
                onclick="ouvrirAffectation(<?= (int)$c->getId() ?>, '<?= addslashes(htmlspecialchars($c->getTitre())) ?>')">
                <i class="fas fa-user-plus"></i> Affecter
              </button>
              <?php endif; ?>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Modal affectation (Vice-Doyen) -->
<?php if ($utilisateur && $utilisateur->peutAffecter() && !empty($enseignants)): ?>
<div class="modal-backdrop" id="affectModal" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-user-plus"></i> Affecter un enseignant</h3>
      <button class="modal-close" onclick="document.getElementById('affectModal').style.display='none'">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <form method="POST" action="index.php?page=cours&action=affecter">
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
      <input type="hidden" name="cours_id" id="affectCoursId">
      <div class="modal-body">
        <p>Cours : <strong id="affectCoursNom"></strong></p>
        <div class="form-group">
          <label>Sélectionner un enseignant / assistant</label>
          <select name="enseignant_id" required>
            <option value="">— Choisir —</option>
            <?php foreach ($enseignants as $ens): ?>
            <option value="<?= $ens->getId() ?>">
              <?= htmlspecialchars($ens->getNomComplet()) ?> (<?= htmlspecialchars($ens->getRoleLabel()) ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost"
          onclick="document.getElementById('affectModal').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-check"></i> Affecter
        </button>
      </div>
    </form>
  </div>
</div>
<script>
function ouvrirAffectation(id, nom) {
  document.getElementById('affectCoursId').value = id;
  document.getElementById('affectCoursNom').textContent = nom;
  document.getElementById('affectModal').style.display = 'flex';
}
document.getElementById('affectModal').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>
<?php endif; ?>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
