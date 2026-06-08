<?php
/**
 * MessageController — Gestion de la messagerie (règles de visibilité)
 */
class MessageController
{
    private Session    $session;
    private Utilisateur $moi;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $userId = $this->session->getUserId();
        $u = Utilisateur::trouverParId($userId);
        if (!$u) { header('Location: index.php?page=login'); exit; }
        $this->moi = $u;
    }

    public function handle(string $action): void
    {
        match($action) {
            'send'  => $this->send(),
            default => $this->index(),
        };
    }

    /** Liste des conversations */
    public function index(): void
    {
        $utilisateur  = $this->moi;
        $contacts     = $this->getContacts();
        $withId       = (int)($_GET['with'] ?? 0);
        $conversation = [];
        $interlocuteur = null;

        if ($withId > 0) {
            $interlocuteur = Utilisateur::trouverParId($withId);
            if ($interlocuteur) {
                $conversation = $this->getConversation($withId);
                // Marquer comme lus
                $pdo = BaseDeDonnees::getInstance();
                $stmt = $pdo->prepare(
                    "UPDATE messages SET lu = 1
                     WHERE expediteur_id = :eid AND destinataire_id = :uid"
                );
                $stmt->execute([':eid' => $withId, ':uid' => $this->moi->getId()]);
            }
        }

        $flashes     = $this->session->getFlashes();
        $csrfToken   = $this->session->getCsrfToken();
        include dirname(__DIR__) . '/views/messages/index.php';
    }

    /** Envoi d'un message */
    public function send(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=messages');
            exit;
        }

        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->addFlash('error', 'Token CSRF invalide.');
            header('Location: index.php?page=messages');
            exit;
        }

        $destId  = (int)($_POST['destinataire_id'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? '');

        if ($destId <= 0 || $contenu === '') {
            $this->session->addFlash('error', 'Message invalide.');
            header('Location: index.php?page=messages&with=' . $destId);
            exit;
        }

        $destinataire = Utilisateur::trouverParId($destId);
        if (!$destinataire) {
            $this->session->addFlash('error', 'Destinataire introuvable.');
            header('Location: index.php?page=messages');
            exit;
        }

        // Déterminer le type de message selon les rôles
        $type = $this->determinerTypeMessage($this->moi, $destinataire);

        $msgData = [
            'expediteur_id'  => $this->moi->getId(),
            'destinataire_id'=> $destId,
            'contenu'        => $contenu,
            'type_message'   => $type,
        ];

        // Promotion pour messages publics
        if ($type === 'public_promotion') {
            $promo = $this->determinerPromotion($this->moi, $destinataire);
            $msgData['promotion_id'] = $promo;
        }

        $msg = match($type) {
            'prive'             => new MessagePrive($msgData),
            'public_promotion'  => new MessagePublic($msgData),
            'doyen_vice_doyen'  => new MessageDoyenViceDoyen($msgData),
            default             => new MessagePrive($msgData),
        };

        $msgId = $msg->sauvegarder();

        // Traitement du fichier joint si présent
        if (!empty($_FILES['fichier']['name']) && $_FILES['fichier']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                $fichier = GestionFichiers::traiterUpload($_FILES['fichier'], $msgId);
                $fichier->setMessageId($msgId);
                $fichier->sauvegarder();
            } catch (RuntimeException $e) {
                $this->session->addFlash('warning', 'Message envoyé, mais fichier rejeté : ' . $e->getMessage());
            }
        }

        header('Location: index.php?page=messages&with=' . $destId);
        exit;
    }

    /** Mur pédagogique */
    public function mur(string $action): void
    {
        $coursId = (int)($_GET['cours'] ?? 0);

        if ($action === 'post' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!$this->session->verifyCsrfToken($token)) {
                $this->session->addFlash('error', 'Token CSRF invalide.');
                header('Location: index.php?page=mur&cours=' . $coursId);
                exit;
            }

            $contenu = trim($_POST['contenu'] ?? '');
            if ($contenu === '') {
                $this->session->addFlash('error', 'Le message ne peut pas être vide.');
                header('Location: index.php?page=mur&cours=' . $coursId);
                exit;
            }

            try {
                $mur = new MurPedagogique($coursId);
                $mur->publier($this->moi, $contenu);
                $this->session->addFlash('success', 'Publication ajoutée au mur.');
            } catch (RuntimeException $e) {
                $this->session->addFlash('error', $e->getMessage());
            }

            header('Location: index.php?page=mur&cours=' . $coursId);
            exit;
        }

        $cours = Cours::trouverParId($coursId);
        if (!$cours) {
            http_response_code(404);
            $utilisateur = $this->moi;
            $notifs = [];
            include dirname(__DIR__) . '/views/errors/404.php';
            return;
        }

        $mur           = new MurPedagogique($coursId);
        $publications  = $mur->getPublications();
        $utilisateur   = $this->moi;
        $flashes       = $this->session->getFlashes();
        $csrfToken     = $this->session->getCsrfToken();

        include dirname(__DIR__) . '/views/mur/index.php';
    }

    // ---- Helpers privés ----

    private function getContacts(): array
    {
        $role = $this->moi->getRole();
        $pdo  = BaseDeDonnees::getInstance();

        if ($role === 'etudiant' && $this->moi instanceof MembrePedagogique) {
            // Camarades de promotion + enseignants/assistants des cours de la promo
            $stmt = $pdo->prepare(
                "SELECT DISTINCT u.*, 
                    (SELECT COUNT(*) FROM messages m
                     WHERE ((m.expediteur_id = u.id AND m.destinataire_id = :uid1)
                         OR (m.expediteur_id = :uid2 AND m.destinataire_id = u.id))
                     AND m.lu = 0 AND m.destinataire_id = :uid3) AS non_lus
                 FROM users u
                 WHERE (
                   (u.promotion_id = :promo AND u.role = 'etudiant' AND u.id != :uid4)
                   OR (u.role IN ('enseignant','assistant') AND u.id IN (
                       SELECT ce.user_id FROM cours_enseignants ce
                       JOIN cours c ON ce.cours_id = c.id
                       WHERE c.promotion_id = :promo2
                   ))
                 )
                 ORDER BY u.role, u.nom"
            );
            $pid = $this->moi->getPromotionId();
            $stmt->execute([':uid1'=>$this->moi->getId(),':uid2'=>$this->moi->getId(),
                            ':uid3'=>$this->moi->getId(),':uid4'=>$this->moi->getId(),
                            ':promo'=>$pid,':promo2'=>$pid]);

        } elseif (in_array($role, ['enseignant','assistant'])) {
            // Collègues enseignants/assistants + étudiants de leurs cours
            $stmt = $pdo->prepare(
                "SELECT DISTINCT u.*,
                    (SELECT COUNT(*) FROM messages m
                     WHERE m.expediteur_id = u.id AND m.destinataire_id = :uid1 AND m.lu = 0) AS non_lus
                 FROM users u
                 WHERE (u.role IN ('enseignant','assistant') AND u.id != :uid2)
                    OR (u.role = 'etudiant' AND u.promotion_id IN (
                        SELECT c.promotion_id FROM cours c
                        JOIN cours_enseignants ce ON c.id = ce.cours_id
                        WHERE ce.user_id = :uid3
                    ))
                 ORDER BY u.role, u.nom"
            );
            $stmt->execute([':uid1'=>$this->moi->getId(),':uid2'=>$this->moi->getId(),':uid3'=>$this->moi->getId()]);

        } elseif (in_array($role, ['doyen','vice_doyen'])) {
            $stmt = $pdo->prepare(
                "SELECT u.*,
                    (SELECT COUNT(*) FROM messages m
                     WHERE m.expediteur_id = u.id AND m.destinataire_id = :uid1 AND m.lu = 0) AS non_lus
                 FROM users u
                 WHERE u.role IN ('doyen','vice_doyen') AND u.id != :uid2
                 ORDER BY u.nom"
            );
            $stmt->execute([':uid1'=>$this->moi->getId(),':uid2'=>$this->moi->getId()]);
        } else {
            return [];
        }

        return array_map(fn($r) => array_merge($r, ['objet' => UserFactory::creer($r)]), $stmt->fetchAll());
    }

    private function getConversation(int $otherId): array
    {
        $pdo   = BaseDeDonnees::getInstance();
        $myId  = $this->moi->getId();
        $myRole= $this->moi->getRole();
        $other = Utilisateur::trouverParId($otherId);

        if (!$other) return [];

        // Types de messages visibles entre ces deux utilisateurs
        $types = ['prive'];
        if (($myRole === 'etudiant' && in_array($other->getRole(), ['enseignant','assistant']))
         || (in_array($myRole, ['enseignant','assistant']) && $other->getRole() === 'etudiant')) {
            $types[] = 'public_promotion';
        }
        if (in_array($myRole, ['doyen','vice_doyen']) && in_array($other->getRole(), ['doyen','vice_doyen'])) {
            $types[] = 'doyen_vice_doyen';
        }

        $placeholders = implode(',', array_fill(0, count($types), '?'));

        $params = array_merge([$myId, $otherId, $otherId, $myId], $types);
        $stmt = $pdo->prepare(
            "SELECT m.*,
                    u_e.prenom AS exp_prenom, u_e.nom AS exp_nom, u_e.role AS exp_role,
                    u_d.prenom AS dest_prenom, u_d.nom AS dest_nom
             FROM messages m
             LEFT JOIN users u_e ON m.expediteur_id  = u_e.id
             LEFT JOIN users u_d ON m.destinataire_id = u_d.id
             WHERE ((m.expediteur_id = ? AND m.destinataire_id = ?)
                 OR (m.expediteur_id = ? AND m.destinataire_id = ?))
               AND m.type_message IN ($placeholders)
             ORDER BY m.created_at ASC"
        );
        $stmt->execute($params);

        $msgs = $stmt->fetchAll();

        // Enrichir avec les fichiers joints
        foreach ($msgs as &$msg) {
            $msg['fichiers'] = Fichier::getParMessage((int)$msg['id']);
        }

        return $msgs;
    }

    private function determinerTypeMessage(Utilisateur $exp, Utilisateur $dest): string
    {
        $rE = $exp->getRole();
        $rD = $dest->getRole();

        // Doyen <-> Vice-Doyen : confidentiel
        if (($rE === 'doyen' && $rD === 'vice_doyen') || ($rE === 'vice_doyen' && $rD === 'doyen')) {
            return 'doyen_vice_doyen';
        }
        // Etudiant <-> Enseignant/Assistant : public (visible par promotion)
        if (($rE === 'etudiant' && in_array($rD, ['enseignant','assistant']))
         || ($rD === 'etudiant' && in_array($rE, ['enseignant','assistant']))) {
            return 'public_promotion';
        }
        // Tout le reste : privé
        return 'prive';
    }

    private function determinerPromotion(Utilisateur $exp, Utilisateur $dest): ?int
    {
        if ($exp->getRole() === 'etudiant' && method_exists($exp, 'getPromotionId')) {
            return $exp->getPromotionId();
        }
        if ($dest->getRole() === 'etudiant' && method_exists($dest, 'getPromotionId')) {
            return $dest->getPromotionId();
        }
        return null;
    }
}
