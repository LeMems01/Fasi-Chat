<?php
if (!defined('_FASICHAT_')) die('Accès interdit.');

$utilisateur = $utilisateur ?? null;

// Calcul des notifications si non fourni par le controller
if (!isset($notifs)) {
    $notifs = ['messages' => 0, 'convocations' => 0];
    try {
        if ($utilisateur) {
            $__pdo = BaseDeDonnees::getInstance();
            $__uid = $utilisateur->getId();

            $__s = $__pdo->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = :uid AND lu = 0");
            $__s->execute([':uid' => $__uid]);
            $notifs['messages'] = (int)$__s->fetchColumn();

            if (in_array($utilisateur->getRole(), ['enseignant', 'assistant'])) {
                $__s = $__pdo->prepare("SELECT COUNT(*) FROM convocation_destinataires WHERE user_id = :uid AND lu = 0");
                $__s->execute([':uid' => $__uid]);
                $notifs['convocations'] = (int)$__s->fetchColumn();
            }
        }
    } catch (Exception $__e) {
        // silencieux
    }
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
    <div class="brand-text">
      <span class="brand-name">FasiChat</span>
      <span class="brand-sub">Classroom</span>
    </div>
  </div>

  <?php if ($utilisateur): ?>
  <div class="sidebar-user">
    <div class="user-avatar" style="background: <?= htmlspecialchars($utilisateur->getRoleCouleur()) ?>">
      <?= htmlspecialchars($utilisateur->getInitiales()) ?>
    </div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($utilisateur->getPrenom()) ?> <?= htmlspecialchars($utilisateur->getNom()) ?></div>
      <div class="user-role-badge" style="background:<?= htmlspecialchars($utilisateur->getRoleCouleur()) ?>20;color:<?= htmlspecialchars($utilisateur->getRoleCouleur()) ?>">
        <?= htmlspecialchars($utilisateur->getRoleLabel()) ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Navigation</div>
    <a href="index.php?page=dashboard" class="nav-item <?= ($_GET['page']??'dashboard')==='dashboard'?'active':'' ?>">
      <i class="fas fa-th-large"></i><span>Tableau de bord</span>
    </a>

    <?php if ($utilisateur && $utilisateur->getRole() !== 'apparitaire'): ?>
    <a href="index.php?page=messages" class="nav-item <?= ($_GET['page']??'')==='messages'?'active':'' ?>">
      <i class="fas fa-comments"></i><span>Messages</span>
      <?php if (($notifs['messages'] ?? 0) > 0): ?>
        <span class="badge" data-notif="messages"><?= (int)$notifs['messages'] ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>

    <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant','etudiant'])): ?>
    <a href="index.php?page=cours" class="nav-item <?= ($_GET['page']??'')==='cours'?'active':'' ?>">
      <i class="fas fa-book-open"></i><span>Cours</span>
    </a>
    <?php endif; ?>

    <?php if ($utilisateur && in_array($utilisateur->getRole(), ['enseignant','assistant','doyen','vice_doyen'])): ?>
    <a href="index.php?page=convocations" class="nav-item <?= ($_GET['page']??'')==='convocations'?'active':'' ?>">
      <i class="fas fa-calendar-alt"></i><span>Convocations</span>
      <?php if (($notifs['convocations'] ?? 0) > 0): ?>
        <span class="badge" data-notif="convocations"><?= (int)$notifs['convocations'] ?></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>

    <?php if ($utilisateur && in_array($utilisateur->getRole(), ['doyen','vice_doyen'])): ?>
    <a href="index.php?page=cours" class="nav-item <?= ($_GET['page']??'')==='cours'?'active':'' ?>">
      <i class="fas fa-chalkboard-teacher"></i><span>Gestion cours</span>
    </a>
    <?php endif; ?>

    <div class="nav-section-label">Institutionnel</div>
    <a href="index.php?page=valve" class="nav-item <?= ($_GET['page']??'')==='valve'?'active':'' ?>">
      <i class="fas fa-bullhorn"></i><span>Valve</span>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="index.php?page=logout" class="logout-btn">
      <i class="fas fa-sign-out-alt"></i><span>Déconnexion</span>
    </a>
  </div>
</aside>
