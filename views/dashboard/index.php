<?php define('_FASICHAT_', true); ?>
<?php
$utilisateur = $utilisateur ?? null;
$flashes     = $flashes ?? [];
$dashData    = $dashData ?? [];
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <div>
      <h1 class="page-title">Tableau de bord</h1>
      <p class="page-sub">Bienvenue, <?= htmlspecialchars($utilisateur ? $utilisateur->getPrenom() : 'Utilisateur') ?> — <?= date('l d F Y') ?></p>
    </div>
    <div class="header-role-badge" style="background:<?= htmlspecialchars($utilisateur ? $utilisateur->getRoleCouleur() : '#6b7280') ?>">
      <i class="fas fa-id-badge"></i> <?= htmlspecialchars($utilisateur ? $utilisateur->getRoleLabel() : 'Utilisateur') ?>
    </div>
  </div>

  <!-- Flashs -->
  <?php foreach ($flashes as $flash): ?>
  <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
    <i class="fas <?= $flash['type']==='success'?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endforeach; ?>

  <!-- Cartes de stats -->
  <div class="stats-grid">
    <?php if ($utilisateur && $utilisateur->getRole() === 'etudiant'): ?>
      <div class="stat-card" style="--accent:#0891b2">
        <div class="stat-icon"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['messages_non_lus'] ?? 0) ?></div>
          <div class="stat-label">Messages non lus</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#059669">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_camarades'] ?? 0) ?></div>
          <div class="stat-label">Camarades de promo</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#7c3aed">
        <div class="stat-icon"><i class="fas fa-book"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= count($dashData['cours'] ?? []) ?></div>
          <div class="stat-label">Cours suivis</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#d97706">
        <div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_annonces'] ?? 0) ?></div>
          <div class="stat-label">Annonces Valve</div>
        </div>
      </div>

    <?php elseif ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant'])): ?>
      <div class="stat-card" style="--accent:#0891b2">
        <div class="stat-icon"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['messages_non_lus'] ?? 0) ?></div>
          <div class="stat-label">Messages non lus</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#dc2626">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['convocations_non_lues'] ?? 0) ?></div>
          <div class="stat-label">Convocations nouvelles</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#059669">
        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_etudiants'] ?? 0) ?></div>
          <div class="stat-label">Étudiants suivis</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#7c3aed">
        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_cours'] ?? 0) ?></div>
          <div class="stat-label">Mes cours</div>
        </div>
      </div>

    <?php elseif ($utilisateur && $utilisateur->getRole() === 'apparitaire'): ?>
      <div class="stat-card" style="--accent:#0891b2">
        <div class="stat-icon"><i class="fas fa-bullhorn"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['annonces_actives'] ?? 0) ?></div>
          <div class="stat-label">Annonces actives</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#059669">
        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_etudiants'] ?? 0) ?></div>
          <div class="stat-label">Étudiants inscrits</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#7c3aed">
        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_enseignants'] ?? 0) ?></div>
          <div class="stat-label">Enseignants</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#d97706">
        <div class="stat-icon"><i class="fas fa-book"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_cours'] ?? 0) ?></div>
          <div class="stat-label">Cours ouverts</div>
        </div>
      </div>

    <?php else: // Doyen / Vice-Doyen ?>
      <div class="stat-card" style="--accent:#0891b2">
        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_etudiants'] ?? 0) ?></div>
          <div class="stat-label">Étudiants</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#059669">
        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_enseignants'] ?? 0) ?></div>
          <div class="stat-label">Enseignants & Assistants</div>
        </div>
      </div>
      <div class="stat-card" style="--accent:#7c3aed">
        <div class="stat-icon"><i class="fas fa-book-open"></i></div>
        <div class="stat-body">
          <div class="stat-value"><?= (int)($dashData['nb_cours'] ?? 0) ?></div>
          <div class="stat-label">Cours</div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Actions rapides -->
  <div class="quick-actions-section">
    <h2 class="section-title"><i class="fas fa-bolt"></i> Actions rapides</h2>
    <div class="quick-actions">
      <?php if ($utilisateur && $utilisateur->getRole() !== 'apparitaire'): ?>
      <a href="index.php?page=messages" class="quick-card">
        <div class="qc-icon" style="background:#e0f2fe;color:#0284c7"><i class="fas fa-paper-plane"></i></div>
        <div class="qc-body"><div class="qc-title">Envoyer un message</div><div class="qc-sub">Contacter un membre</div></div>
      </a>
      <?php endif; ?>

      <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant'])): ?>
      <a href="index.php?page=convocations" class="quick-card">
        <div class="qc-icon" style="background:#fef9c3;color:#ca8a04"><i class="fas fa-calendar-alt"></i></div>
        <div class="qc-body"><div class="qc-title">Mes convocations</div><div class="qc-sub">Voir les réunions programmées</div></div>
      </a>
      <?php if (!empty($dashData['cours'])): ?>
      <a href="index.php?page=mur&cours=<?= (int)$dashData['cours'][0]['id'] ?>" class="quick-card">
        <div class="qc-icon" style="background:#f0fdf4;color:#16a34a"><i class="fas fa-chalkboard"></i></div>
        <div class="qc-body"><div class="qc-title">Mur pédagogique</div><div class="qc-sub">Publier une annonce cours</div></div>
      </a>
      <?php endif; ?>
      <?php endif; ?>

      <?php if ($utilisateur && $utilisateur->peutEnvoyerConvocation()): ?>
      <a href="index.php?page=convocations&action=envoyer" class="quick-card">
        <div class="qc-icon" style="background:#fff1f2;color:#dc2626"><i class="fas fa-calendar-plus"></i></div>
        <div class="qc-body"><div class="qc-title">Convoquer une réunion</div><div class="qc-sub">Envoyer une convocation officielle</div></div>
      </a>
      <?php endif; ?>

      <?php if ($utilisateur && $utilisateur->peutGererValve()): ?>
      <a href="index.php?page=valve" class="quick-card">
        <div class="qc-icon" style="background:#f0f9ff;color:#0369a1"><i class="fas fa-bullhorn"></i></div>
        <div class="qc-body"><div class="qc-title">Gérer la Valve</div><div class="qc-sub">Publier une annonce institutionnelle</div></div>
      </a>
      <?php endif; ?>

      <?php if ($utilisateur && $utilisateur->peutAffecter()): ?>
      <a href="index.php?page=cours" class="quick-card">
        <div class="qc-icon" style="background:#fdf4ff;color:#7e22ce"><i class="fas fa-user-tag"></i></div>
        <div class="qc-body"><div class="qc-title">Affecter enseignants</div><div class="qc-sub">Gérer les affectations aux cours</div></div>
      </a>
      <?php endif; ?>

      <a href="index.php?page=valve" class="quick-card">
        <div class="qc-icon" style="background:#fff7ed;color:#c2410c"><i class="fas fa-newspaper"></i></div>
        <div class="qc-body"><div class="qc-title">Consulter la Valve</div><div class="qc-sub">Annonces institutionnelles</div></div>
      </a>
    </div>
  </div>

  <!-- Mes cours (pour étudiants / enseignants) -->
  <?php if (!empty($dashData['cours'])): ?>
  <div class="section-block">
    <h2 class="section-title"><i class="fas fa-book-open"></i> Mes cours</h2>
    <div class="cours-grid">
      <?php foreach ($dashData['cours'] as $c): ?>
      <a href="index.php?page=mur&cours=<?= (int)$c['id'] ?>" class="cours-card">
        <div class="cours-icon"><i class="fas fa-book"></i></div>
        <div class="cours-body">
          <div class="cours-titre"><?= htmlspecialchars($c['titre']) ?></div>
          <?php if (!empty($c['description'])): ?>
          <div class="cours-desc"><?= htmlspecialchars(mb_strimwidth($c['description'], 0, 60, '…')) ?></div>
          <?php endif; ?>
        </div>
        <i class="fas fa-chevron-right cours-arrow"></i>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</main>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
