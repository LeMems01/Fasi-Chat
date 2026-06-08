<?php
/**
 * AjaxController — Endpoints JSON pour interactions dynamiques
 */
class AjaxController
{
    private Session     $session;
    private Utilisateur $moi;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $u = Utilisateur::trouverParId($this->session->getUserId());
        if (!$u) { $this->jsonError('Non authentifié', 401); }
        $this->moi = $u;
    }

    public function handle(string $action): void
    {
        header('Content-Type: application/json; charset=utf-8');
        match($action) {
            'notifs'   => $this->getNotifications(),
            'mark-read'=> $this->markRead(),
            default    => $this->jsonError('Action inconnue', 400),
        };
    }

    private function getNotifications(): void
    {
        $pdo  = BaseDeDonnees::getInstance();
        $uid  = $this->moi->getId();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM messages WHERE destinataire_id = :uid AND lu = 0"
        );
        $stmt->execute([':uid' => $uid]);
        $msgs = (int)$stmt->fetchColumn();

        $convocs = 0;
        if (in_array($this->moi->getRole(), ['enseignant','assistant'])) {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM convocation_destinataires WHERE user_id = :uid AND lu = 0"
            );
            $stmt->execute([':uid' => $uid]);
            $convocs = (int)$stmt->fetchColumn();
        }

        echo json_encode(['messages' => $msgs, 'convocations' => $convocs]);
    }

    private function markRead(): void
    {
        $msgId = (int)($_POST['message_id'] ?? 0);
        if ($msgId > 0) {
            Message::marquerLu($msgId, $this->moi->getId());
        }
        echo json_encode(['ok' => true]);
    }

    private function jsonError(string $msg, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['error' => $msg]);
        exit;
    }
}
