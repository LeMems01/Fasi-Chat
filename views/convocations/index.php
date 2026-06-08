<?php define('_FASICHAT_', true); ?>
<?php
$utilisateur = $utilisateur ?? null;
$flashes = $flashes ?? [];
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header"> 
    <div>
      <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Convocations</h1>
      <p class="page-sub">Réunions et convocations officielles</p>
    </div>
    <?php if ($utilisateur && $utilisateur->peutEnvoyerConvocation()): ?>
    <a href="index.php?page=convocations&action=envoyer" class="btn btn-primary">
      <i class="fas fa-paper-plane"></i> Envoyer une convocation
    </a>
    <?php endif; ?> 
  </div>

  <?php foreach ($flashes as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <i class="fas <?= $flash['type']==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Convocations reçues -->
  <?php if (!empty($convocations)): ?>
  <div class="section-block">
    <h2 class="section-title"><i class="fas fa-inbox"></i> Mes convocations reçues</h2>
    <div class="convoc-list">
      <?php foreach ($convocations as $c): ?>
      <div class="convoc-card <?= $c['lu'] ? '' : 'unread' ?>">
        <div class="convoc-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="convoc-body">
          <div class="convoc-header">
            <h3 class="convoc-objet"><?= htmlspecialchars($c['objet']) ?></h3>
            <?php if (!$c['lu']): ?>
            <span class="convoc-new-badge">Nouvelle</span>
            <?php endif; ?>
          </div>
          <div class="convoc-details">
            <div class="convoc-detail"><i class="fas fa-user-tie"></i>
              De : <strong><?= htmlspecialchars($c['exp_prenom'] . ' ' . $c['exp_nom']) ?></strong>
              (<?= htmlspecialchars(ucfirst(str_replace('_',' ',$c['exp_role']))) ?>)
            </div>
            <div class="convoc-detail"><i class="fas fa-clock"></i>
              Réunion le : <strong><?= date('d/m/Y à H\hi', strtotime($c['date_reunion'])) ?></strong>
            </div>
            <div class="convoc-detail"><i class="fas fa-map-marker-alt"></i>
              Lieu : <strong><?= htmlspecialchars($c['lieu']) ?></strong>
            </div>
            <?php if (!empty($c['message_explicatif'])): ?>
            <div class="convoc-detail convoc-msg">
              <i class="fas fa-comment-alt"></i>
              <?= nl2br(htmlspecialchars($c['message_explicatif'])) ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="convoc-meta">
            Reçue le <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
            <?php if (!$c['lu']): ?>
            &nbsp;·&nbsp;
            <a href="index.php?page=convocations&action=lire&id=<?= (int)$c['id'] ?>" class="link-mark-read">
              <i class="fas fa-check-double"></i> Marquer comme lue
            </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php elseif ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant'])): ?>
  <div class="empty-state">
    <i class="fas fa-calendar-times"></i>
    <h3>Aucune convocation reçue</h3>
    <p>Vous n'avez pas encore reçu de convocation de réunion.</p>
  </div>
  <?php endif; ?>

  <!-- Convocations envoyées (Doyen / Vice-Doyen) -->
  <?php if (!empty($envoyees)): ?>
  <div class="section-block">
    <h2 class="section-title"><i class="fas fa-paper-plane"></i> Convocations envoyées</h2>
    <div class="convoc-list">
      <?php foreach ($envoyees as $c): ?>
      <div class="convoc-card sent">
        <div class="convoc-icon sent-icon"><i class="fas fa-calendar-plus"></i></div>
        <div class="convoc-body">
          <h3 class="convoc-objet"><?= htmlspecialchars($c['objet']) ?></h3>
          <div class="convoc-details">
            <div class="convoc-detail"><i class="fas fa-clock"></i>
              Réunion le : <strong><?= date('d/m/Y à H\hi', strtotime($c['date_reunion'])) ?></strong>
            </div>
            <div class="convoc-detail"><i class="fas fa-map-marker-alt"></i>
              <?= htmlspecialchars($c['lieu']) ?>
            </div>
          </div>
          <div class="convoc-meta">
            Envoyée le <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</main>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
