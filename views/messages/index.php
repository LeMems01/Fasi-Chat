<?php define('_FASICHAT_', true); ?>
<?php
$utilisateur   = $utilisateur ?? null;
$contacts      = $contacts ?? [];
$interlocuteur = $interlocuteur ?? null;
$conversation  = $conversation ?? [];
$flashes       = $flashes ?? [];
$csrfToken     = $csrfToken ?? Session::getInstance()->getCsrfToken();
?>
<?php include dirname(__DIR__) . '/layout/header.php'; ?>
<?php include dirname(__DIR__) . '/layout/sidebar.php'; ?>

<main class="main-content no-pad">
  <div class="messenger">

    <!-- Liste des contacts -->
    <div class="contacts-panel">
      <div class="contacts-header">
        <h2><i class="fas fa-comments"></i> Messages</h2>
        <span class="contacts-count"><?= count($contacts) ?> contact(s)</span>
      </div>
      <div class="contacts-search">
        <input type="text" id="contactSearch" placeholder="Rechercher..." oninput="filtrerContacts(this.value)">
        <i class="fas fa-search"></i>
      </div>
      <div class="contacts-list" id="contactsList">
        <?php if (empty($contacts)): ?>
          <div class="contacts-empty"><i class="fas fa-user-slash"></i><p>Aucun contact disponible</p></div>
        <?php else: ?>
          <?php foreach ($contacts as $c):
            $u = $c['objet'];
            $isActive = ($interlocuteur && $interlocuteur->getId() === $u->getId());
          ?>
          <a href="index.php?page=messages&with=<?= $u->getId() ?>"
             class="contact-item <?= $isActive ? 'active' : '' ?>"
             data-name="<?= strtolower(htmlspecialchars($u->getNomComplet())) ?>">
            <div class="contact-avatar" style="background:<?= htmlspecialchars($u->getRoleCouleur()) ?>">
              <?= htmlspecialchars($u->getInitiales()) ?>
            </div>
            <div class="contact-info">
              <div class="contact-name"><?= htmlspecialchars($u->getNomComplet()) ?></div>
              <div class="contact-role" style="color:<?= htmlspecialchars($u->getRoleCouleur()) ?>">
                <?= htmlspecialchars($u->getRoleLabel()) ?>
              </div>
            </div>
            <?php if ((int)($c['non_lus'] ?? 0) > 0): ?>
            <span class="contact-badge"><?= (int)$c['non_lus'] ?></span>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Zone de conversation -->
    <div class="chat-panel">
      <?php if (!$interlocuteur): ?>
        <div class="chat-empty">
          <div class="chat-empty-icon"><i class="fas fa-paper-plane"></i></div>
          <h3>Sélectionnez une conversation</h3>
          <p>Choisissez un contact dans la liste pour commencer à échanger.</p>
        </div>
      <?php else: ?>
        <!-- Header de la conversation -->
        <div class="chat-header">
          <div class="chat-header-left">
            <div class="chat-avatar" style="background:<?= htmlspecialchars($interlocuteur->getRoleCouleur()) ?>">
              <?= htmlspecialchars($interlocuteur->getInitiales()) ?>
            </div>
            <div>
              <div class="chat-name"><?= htmlspecialchars($interlocuteur->getNomComplet()) ?></div>
              <div class="chat-role" style="color:<?= htmlspecialchars($interlocuteur->getRoleCouleur()) ?>">
                <?= htmlspecialchars($interlocuteur->getRoleLabel()) ?>
              </div>
            </div>
          </div>
          <?php
            $rExp  = $utilisateur->getRole();
            $rDest = $interlocuteur->getRole();
            $isPublic = ($rExp === 'etudiant' && in_array($rDest, ['enseignant','assistant']))
                     || ($rDest === 'etudiant' && in_array($rExp, ['enseignant','assistant']));
            $isConfidentiel = in_array($rExp, ['doyen','vice_doyen']) && in_array($rDest, ['doyen','vice_doyen']);
          ?>
          <?php if ($isPublic): ?>
          <div class="chat-visibility-badge public">
            <i class="fas fa-eye"></i> Visible par la promotion
          </div>
          <?php elseif ($isConfidentiel): ?>
          <div class="chat-visibility-badge confidentiel">
            <i class="fas fa-lock"></i> Confidentiel
          </div>
          <?php else: ?>
          <div class="chat-visibility-badge prive">
            <i class="fas fa-user-shield"></i> Privé
          </div>
          <?php endif; ?>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chatMessages">
          <?php foreach ($flashes as $f): ?>
          <div class="alert alert-<?= htmlspecialchars($f['type']) ?>"><?= htmlspecialchars($f['message']) ?></div>
          <?php endforeach; ?>

          <?php if (empty($conversation)): ?>
            <div class="no-messages"><i class="fas fa-comment-slash"></i><p>Aucun message encore. Commencez la conversation !</p></div>
          <?php else: ?>
            <?php
            $lastDate = '';
            foreach ($conversation as $msg):
              $msgDate = date('d/m/Y', strtotime($msg['created_at']));
              $isMine  = ((int)$msg['expediteur_id'] === $utilisateur->getId());
              $heure   = date('H:i', strtotime($msg['created_at']));
              $isPublicMsg = ($msg['type_message'] === 'public_promotion');
            ?>
              <?php if ($msgDate !== $lastDate): $lastDate = $msgDate; ?>
              <div class="date-separator"><span><?= htmlspecialchars($msgDate) ?></span></div>
              <?php endif; ?>
              <div class="message-wrap <?= $isMine ? 'mine' : 'theirs' ?>">
                <?php if (!$isMine): ?>
                <div class="msg-avatar" style="background:<?= htmlspecialchars($interlocuteur->getRoleCouleur()) ?>">
                  <?= htmlspecialchars($interlocuteur->getInitiales()) ?>
                </div>
                <?php endif; ?>
                <div class="message-bubble <?= $isMine ? 'sent' : 'received' ?> <?= $isPublicMsg ? 'public-msg' : '' ?>">
                  <?php if ($isPublicMsg): ?>
                  <div class="msg-public-tag"><i class="fas fa-users"></i> Message public</div>
                  <?php endif; ?>
                  <div class="msg-content"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></div>
                  <!-- Fichiers joints -->
                  <?php if (!empty($msg['fichiers'])): ?>
                  <div class="msg-files">
                    <?php foreach ($msg['fichiers'] as $f): ?>
                    <a href="<?= htmlspecialchars($f->getUrl()) ?>" target="_blank" class="msg-file-link">
                      <i class="fas <?= htmlspecialchars($f->getIcone()) ?>"></i>
                      <span><?= htmlspecialchars($f->getNomOriginal()) ?></span>
                      <small><?= htmlspecialchars($f->getTailleFormatee()) ?></small>
                    </a>
                    <?php endforeach; ?>
                  </div>
                  <?php endif; ?>
                  <div class="msg-time"><?= htmlspecialchars($heure) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Formulaire d'envoi -->
        <div class="chat-input-area">
          <form method="POST" action="index.php?page=messages&action=send"
                enctype="multipart/form-data" class="chat-form" id="chatForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="destinataire_id" value="<?= (int)$interlocuteur->getId() ?>">
            <div class="chat-input-row">
              <label class="file-attach-btn" title="Joindre un fichier">
                <i class="fas fa-paperclip"></i>
                <input type="file" name="fichier" id="fileInput" style="display:none"
                       onchange="afficherFichier(this)" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
              </label>
              <div class="chat-input-wrapper">
                <textarea name="contenu" id="messageInput" placeholder="Écrivez votre message…"
                          rows="1" required oninput="autoResize(this)"
                          onkeydown="submitOnEnter(event)"></textarea>
                <div id="filePreview" class="file-preview" style="display:none"></div>
              </div>
              <button type="submit" class="send-btn" title="Envoyer">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
// Scroll au bas des messages
document.addEventListener('DOMContentLoaded', function() {
  var cm = document.getElementById('chatMessages');
  if (cm) cm.scrollTop = cm.scrollHeight;
});
function filtrerContacts(q) {
  document.querySelectorAll('.contact-item').forEach(function(el) {
    el.style.display = el.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
  });
}
function afficherFichier(input) {
  var prev = document.getElementById('filePreview');
  if (input.files && input.files[0]) {
    prev.style.display = 'flex';
    prev.innerHTML = '<i class="fas fa-file"></i><span>' + input.files[0].name + '</span>'
      + '<button type="button" onclick="clearFile()"><i class="fas fa-times"></i></button>';
  }
}
function clearFile() {
  document.getElementById('fileInput').value = '';
  document.getElementById('filePreview').style.display = 'none';
}
function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}
function submitOnEnter(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    document.getElementById('chatForm').submit();
  }
}
</script>
<?php include dirname(__DIR__) . '/layout/footer.php'; ?>
