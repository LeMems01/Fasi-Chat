<?php
/**
 * DashboardController — Tableau de bord selon le rôle
 */
class DashboardController
{
    private Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    public function index(): void
    {
        $userId      = $this->session->getUserId();
        $utilisateur = Utilisateur::trouverParId($userId);

        if (!$utilisateur) {
            $this->session->destroy();
            header('Location: index.php?page=login');
            exit;
        }

        $dashData = $utilisateur->getDashboardData();
        $flashes  = $this->session->getFlashes();

        // Compteur de notifications global
        $notifs = $this->getNotifications($utilisateur);

        include dirname(__DIR__) . '/views/dashboard/index.php';
    }

    public function getNotifications(Utilisateur $utilisateur): array
    {
        $pdo   = BaseDeDonnees::getInstance();
        $notifs = ['messages' => 0, 'convocations' => 0];

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM messages
             WHERE destinataire_id = :uid AND lu = 0"
        );
        $stmt->execute([':uid' => $utilisateur->getId()]);
        $notifs['messages'] = (int)$stmt->fetchColumn();

        if (in_array($utilisateur->getRole(), ['enseignant', 'assistant'])) {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM convocation_destinataires
                 WHERE user_id = :uid AND lu = 0"
            );
            $stmt->execute([':uid' => $utilisateur->getId()]);
            $notifs['convocations'] = (int)$stmt->fetchColumn();
        }

        return $notifs;
    }
}
