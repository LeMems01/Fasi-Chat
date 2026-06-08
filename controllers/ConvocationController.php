<?php
/**
 * ConvocationController — Gestion des convocations de réunion
 */
class ConvocationController
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
            'envoyer' => $this->envoyer(),
            'lire'    => $this->marquerLue(),
            default   => $this->index(),
        };
    }

    /** Liste des convocations reçues (Enseignants/Assistants) */
    public function index(): void
    {
        $utilisateur = $this->moi;
        $role = $utilisateur->getRole();

        $convocations = [];
        $envoyees     = [];

        if (in_array($role, ['enseignant', 'assistant']) && method_exists($utilisateur, 'getConvocationsRecues')) {
            $convocations = $utilisateur->getConvocationsRecues();
        }

        if ($utilisateur instanceof Convocable && $utilisateur->peutEnvoyerConvocation()) {
            $envoyees = $utilisateur->getConvocationsEnvoyees();
        }

        $flashes   = $this->session->getFlashes();
        $csrfToken = $this->session->getCsrfToken();
        include dirname(__DIR__) . '/views/convocations/index.php';
    }

    /** Formulaire + traitement d'envoi de convocation */
    public function envoyer(): void
    {
        if (!$this->moi instanceof Convocable || !$this->moi->peutEnvoyerConvocation()) {
            http_response_code(403);
            $utilisateur = $this->moi;
            $notifs = [];
            include dirname(__DIR__) . '/views/errors/403.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $utilisateur = $this->moi;
            $csrfToken   = $this->session->getCsrfToken();
            $flashes     = $this->session->getFlashes();
            include dirname(__DIR__) . '/views/convocations/nouvelle.php';
            return;
        }

        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!$this->session->verifyCsrfToken($token)) {
            $this->session->addFlash('error', 'Token CSRF invalide.');
            header('Location: index.php?page=convocations&action=envoyer');
            exit;
        }

        $objet     = trim($_POST['objet']     ?? '');
        $dateR     = trim($_POST['date_reunion'] ?? '');
        $lieu      = trim($_POST['lieu']      ?? '');
        $msgExpl   = trim($_POST['message_explicatif'] ?? '') ?: null;

        if (!$objet || !$dateR || !$lieu) {
            $this->session->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            header('Location: index.php?page=convocations&action=envoyer');
            exit;
        }

        try {
            if (!$this->moi instanceof Convocable) {
                throw new RuntimeException('Cet utilisateur ne peut pas envoyer de convocation.');
            }

            $id = $this->moi->convoquer([
                'objet'             => $objet,
                'date_reunion'      => $dateR,
                'lieu'              => $lieu,
                'message_explicatif'=> $msgExpl,
            ]);
            $this->session->addFlash('success', 'Convocation envoyée avec succès à tous les enseignants et assistants.');
        } catch (Exception $e) {
            $this->session->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        header('Location: index.php?page=convocations');
        exit;
    }

    public function marquerLue(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        Convocation::marquerLue($id, $this->moi->getId());
        header('Location: index.php?page=convocations');
        exit;
    }
}
