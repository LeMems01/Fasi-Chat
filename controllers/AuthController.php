<?php
/**
 * AuthController — Authentification et gestion de session
 */
class AuthController
{
    private Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    public function login(): void
    {
        if ($this->session->isAuthenticated()) {
            header('Location: index.php?page=dashboard');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation CSRF
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!$this->session->verifyCsrfToken($token)) {
                $error = 'Token de sécurité invalide. Veuillez réessayer.';
            } else {
                $email    = filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '';
                $password = $_POST['password'] ?? '';

                if (empty($email) || empty($password)) {
                    $error = 'Veuillez renseigner votre email et votre mot de passe.';
                } else {
                    $utilisateur = Utilisateur::trouverParEmail($email);

                    if ($utilisateur && $utilisateur->verifierMotDePasse($password)) {
                        session_regenerate_id(true);
                        $this->session->set('user_id',   $utilisateur->getId());
                        $this->session->set('user_role', $utilisateur->getRole());
                        $this->session->set('user_nom',  $utilisateur->getNomComplet());
                        $this->session->addFlash('success', 'Bienvenue, ' . htmlspecialchars($utilisateur->getPrenom()) . ' !');
                        header('Location: index.php?page=dashboard');
                        exit;
                    } else {
                        $error = 'Email ou mot de passe incorrect.';
                    }
                }
            }
        }

        $csrfToken = $this->session->getCsrfToken();
        include dirname(__DIR__) . '/views/auth/login.php';
    }

    public function logout(): void
    {
        $this->session->destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
