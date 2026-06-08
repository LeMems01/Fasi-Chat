/**
 * FasiChat Classroom — JavaScript principal
 */
'use strict';

/* ---- Polling notifications (toutes les 30s) ---- */
function pollNotifications() {
  fetch('index.php?ajax=1&action=notifs')
    .then(r => r.json())
    .then(data => {
      updateBadge('[data-notif="messages"]',     data.messages     || 0);
      updateBadge('[data-notif="convocations"]', data.convocations || 0);
    })
    .catch(() => {});
}

function updateBadge(selector, count) {
  var els = document.querySelectorAll(selector);
  els.forEach(function(el) {
    if (count > 0) {
      el.textContent = count;
      el.style.display = '';
    } else {
      el.style.display = 'none';
    }
  });
}

/* ---- Auto-disparition des alertes après 5s ---- */
document.addEventListener('DOMContentLoaded', function () {
  // Alertes auto-hide
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      alert.style.transition = 'opacity .5s ease';
      alert.style.opacity = '0';
      setTimeout(function() { alert.remove(); }, 500);
    }, 5000);
  });

  // Démarrer le polling si authentifié
  if (document.querySelector('.sidebar')) {
    pollNotifications();
    setInterval(pollNotifications, 30000);
  }

  // Confirmation de suppression
  document.querySelectorAll('[data-confirm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // Tooltips simples
  document.querySelectorAll('[title]').forEach(function(el) {
    el.setAttribute('data-title', el.getAttribute('title'));
  });
});

/* ---- Filtrage des contacts (messagerie) ---- */
window.filtrerContacts = function(q) {
  var items = document.querySelectorAll('.contact-item');
  items.forEach(function(item) {
    var name = (item.dataset.name || '').toLowerCase();
    item.style.display = name.includes(q.toLowerCase()) ? '' : 'none';
  });
};

/* ---- Affichage du fichier sélectionné ---- */
window.afficherFichier = function(input) {
  var prev = document.getElementById('filePreview');
  if (!prev) return;
  if (input.files && input.files[0]) {
    var f = input.files[0];
    var size = f.size > 1048576
      ? (f.size / 1048576).toFixed(1) + ' Mo'
      : (f.size / 1024).toFixed(0) + ' Ko';
    prev.style.display = 'flex';
    prev.innerHTML =
      '<i class="fas fa-file"></i>' +
      '<span>' + escHtml(f.name) + '</span>' +
      '<small>' + size + '</small>' +
      '<button type="button" onclick="clearFile()"><i class="fas fa-times"></i></button>';
  }
};

window.clearFile = function() {
  var fi = document.getElementById('fileInput');
  var prev = document.getElementById('filePreview');
  if (fi)   fi.value = '';
  if (prev) prev.style.display = 'none';
};

/* ---- Redimensionnement auto textarea ---- */
window.autoResize = function(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 140) + 'px';
};

/* ---- Envoi avec Entrée (sans Shift) ---- */
window.submitOnEnter = function(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    var form = document.getElementById('chatForm');
    if (form) form.submit();
  }
};

/* ---- Toggle formulaire mur ---- */
window.toggleForm = function() {
  var f = document.getElementById('murForm');
  if (!f) return;
  f.style.display = f.style.display === 'none' ? 'block' : 'none';
};

/* ---- Modales Valve ---- */
window.toggleModal = function(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = m.style.display === 'none' ? 'flex' : 'none';
};

window.ouvrirEdition = function(id, titre, contenu, exp) {
  document.getElementById('editId').value      = id;
  document.getElementById('editTitre').value   = titre;
  document.getElementById('editContenu').value = contenu;
  document.getElementById('editExp').value     = exp;
  toggleModal('editModal');
};

/* ---- Affectation cours ---- */
window.ouvrirAffectation = function(id, nom) {
  var cid  = document.getElementById('affectCoursId');
  var cnom = document.getElementById('affectCoursNom');
  if (cid)  cid.value      = id;
  if (cnom) cnom.textContent = nom;
  var m = document.getElementById('affectModal');
  if (m) m.style.display = 'flex';
};

/* ---- Toggle mot de passe ---- */
window.togglePwd = function() {
  var p = document.getElementById('password');
  var i = document.getElementById('eyeIcon');
  if (!p) return;
  if (p.type === 'password') {
    p.type = 'text';
    if (i) i.className = 'fas fa-eye-slash';
  } else {
    p.type = 'password';
    if (i) i.className = 'fas fa-eye';
  }
};

/* ---- Utilitaires ---- */
function escHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ---- Fermeture modales au clic extérieur ---- */
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.modal-backdrop').forEach(function(m) {
    m.addEventListener('click', function(e) {
      if (e.target === m) m.style.display = 'none';
    });
  });
});
