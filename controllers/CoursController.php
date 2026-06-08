<?php
/**
 * CoursController — Gestion des cours et des affectations
 */
class CoursController
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
            'affecter' => $this->affecter(),
            'retirer'  => $this->retirer(),
            default    => $this->index(),
        };
    }

    public function index(): void
    {
        $utilisateur = $this->moi;
        $pdo         = BaseDeDonnees::getInstance();

        $cours      = Cours::tous();
        $promotions = Promotion::toutes();

        // Pour ViceDoyen : liste des enseignants pour affectation
        $enseignants = [];
        if ($utilisateur->peutAffecter()) {
            $stmt = $pdo->query(
                "SELECT * FROM users WHERE role IN ('enseignant','assistant') ORDER BY nom, prenom"
            );
            $enseignants = array_map(fn($r) => UserFactory::creer($r), $stmt->fetchAll());
        }

        $flashes   = $this->session->getFlashes();
        $csrfToken = $this->session->getCsrfToken();
        include dirname(__DIR__) . '/views/cours/index.php';
    }

    public function affecter(): void
    {
        if (!$this->moi->peutAffecter()) {
            http_response_code(403);
            $utilisateur = $this->moi;
            $notifs = [];
            include dirname(__DIR__) . '/views/errors/403.php';
            return;
        }
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->addFlash('error', 'Token CSRF invalide.');
            header('Location: index.php?page=cours');
            exit;
        }
        $ensId  = (int)($_POST['enseignant_id'] ?? 0);
        $coursId= (int)($_POST['cours_id']      ?? 0);

        if ($this->moi instanceof ViceDoyen) {
            $this->moi->affecterEnseignant($ensId, $coursId);
            $this->session->addFlash('success', 'Enseignant affecté au cours.');
        }
        header('Location: index.php?page=cours');
        exit;
    }

    public function retirer(): void
    {
        if (!$this->moi->peutAffecter()) {
            http_response_code(403);
            $utilisateur = $this->moi;
            $notifs = [];
            include dirname(__DIR__) . '/views/errors/403.php';
            return;
        }
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->addFlash('error', 'Token CSRF invalide.');
            header('Location: index.php?page=cours');
            exit;
        }
        $ensId  = (int)($_POST['enseignant_id'] ?? 0);
        $coursId= (int)($_POST['cours_id']      ?? 0);

        if ($this->moi instanceof ViceDoyen) {
            $this->moi->retirerEnseignant($ensId, $coursId);
            $this->session->addFlash('success', 'Enseignant retiré du cours.');
        }
        header('Location: index.php?page=cours');
        exit;
    }
}
