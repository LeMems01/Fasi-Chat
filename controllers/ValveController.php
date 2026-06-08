<?php
/**
 * ValveController — Gestion de la Valve (annonces institutionnelles)
 */
class ValveController
{
    private Session     $session;
    private Utilisateur $moi;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $u = Utilisateur::trouverParId($this->session->getUserId());
        if (!$u) { header('Location: index.php?page=login'); exit; }
        $this->moi = $u;
    }

    public function handle(string $action): void
    {
        match($action) {
            'ajouter'   => $this->ajouter(),
            'modifier'  => $this->modifier(),
            'supprimer' => $this->supprimer(),
            default     => $this->index(),
        };
    }

    public function index(): void
    {
        $utilisateur = $this->moi;
        $annonces    = AnnonceValve::toutes();
        $flashes     = $this->session->getFlashes();
        $csrfToken   = $this->session->getCsrfToken();
        include dirname(__DIR__) . '/views/valve/index.php';
    }

    public function ajouter(): void
    {
        $this->verifierApparitaire();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=valve');
            exit;
        }
        $this->verifierCsrf();

        try {
            Valve::publier($this->moi, [
                'titre'          => trim($_POST['titre']  ?? ''),
                'contenu'        => trim($_POST['contenu'] ?? ''),
                'date_expiration'=> trim($_POST['date_expiration'] ?? '') ?: null,
            ]);
            $this->session->addFlash('success', 'Annonce publiée avec succès.');
        } catch (Exception $e) {
            $this->session->addFlash('error', $e->getMessage());
        }
        header('Location: index.php?page=valve');
        exit;
    }

    public function modifier(): void
    {
        $this->verifierApparitaire();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=valve');
            exit;
        }
        $this->verifierCsrf();
        $id = (int)($_POST['id'] ?? 0);

        try {
            Valve::modifier($this->moi, $id, [
                'titre'          => trim($_POST['titre']  ?? ''),
                'contenu'        => trim($_POST['contenu'] ?? ''),
                'date_expiration'=> trim($_POST['date_expiration'] ?? '') ?: null,
            ]);
            $this->session->addFlash('success', 'Annonce modifiée.');
        } catch (Exception $e) {
            $this->session->addFlash('error', $e->getMessage());
        }
        header('Location: index.php?page=valve');
        exit;
    }

    public function supprimer(): void
    {
        $this->verifierApparitaire();
        $this->verifierCsrf();
        $id = (int)($_POST['id'] ?? 0);

        try {
            Valve::supprimer($this->moi, $id);
            $this->session->addFlash('success', 'Annonce supprimée.');
        } catch (Exception $e) {
            $this->session->addFlash('error', $e->getMessage());
        }
        header('Location: index.php?page=valve');
        exit;
    }

    private function verifierApparitaire(): void
    {
        if (!$this->moi->peutGererValve()) {
            http_response_code(403);
            $utilisateur = $this->moi;
            $notifs = [];
            include dirname(__DIR__) . '/views/errors/403.php';
            exit;
        }
    }

    private function verifierCsrf(): void
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_GET[CSRF_TOKEN_NAME] ?? '';
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->addFlash('error', 'Token CSRF invalide.');
            header('Location: index.php?page=valve');
            exit;
        }
    }
}
