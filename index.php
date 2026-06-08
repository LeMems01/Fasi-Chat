<?php
/**
 * FasiChat Classroom — Point d'entrée unique (Front Controller)
 * Concept POO : Toute la logique passe par des classes
 */

// Chargement de la configuration et de l'autoloader
require_once __DIR__ . '/config/config.php';

// Démarrage de la session sécurisée
$session = Session::getInstance();

// Récupération sécurisée de la page et de l'action
$page   = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_GET['page']   ?? 'dashboard'));
$action = preg_replace('/[^a-z0-9\-_]/', '', strtolower($_GET['action'] ?? 'index'));
$isAjax = !empty($_GET['ajax']) && $_GET['ajax'] === '1';

// Pages publiques (sans authentification)
$pagesPubliques = ['login', 'logout'];

// Vérification de l'authentification
if (!$session->isAuthenticated() && !in_array($page, $pagesPubliques)) {
    header('Location: index.php?page=login');
    exit;
}

//  Endpoints AJAX 
if ($isAjax) {
    try {
        $controller = new AjaxController();
        $controller->handle($action);
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

//  Routage principal 
try {
    switch ($page) {

        case 'login':
            (new AuthController())->login();
            break;

        case 'logout':
            (new AuthController())->logout();
            break;

        case 'dashboard':
            (new DashboardController())->index();
            break;

        case 'messages':
            (new MessageController())->handle($action);
            break;

        case 'mur':
            (new MessageController())->mur($action);
            break;

        case 'valve':
            (new ValveController())->handle($action);
            break;

        case 'convocations':
            (new ConvocationController())->handle($action);
            break;

        case 'cours':
            (new CoursController())->handle($action);
            break;

        default:
            http_response_code(404);
            // Mini-affichage 404 sans layout complet (page inconnue)
            $userId      = $session->getUserId();
            $utilisateur = $userId ? Utilisateur::trouverParId($userId) : null;
            $notifs      = [];
            if ($utilisateur) {
                define('_FASICHAT_', true);
                include __DIR__ . '/views/errors/404.php';
            } else {
                echo '<h1>404 — Page introuvable</h1><a href="index.php">Accueil</a>';
            }
            break;
    }
} catch (Throwable $e) {
    // Affichage d'erreur développement (à désactiver en production)
    http_response_code(500);
    echo '<div style="font-family:monospace;padding:20px;background:#fff1f2;border:1px solid #fca5a5;border-radius:8px;margin:20px">';
    echo '<strong style="color:#dc2626">Erreur FasiChat :</strong><br>';
    echo htmlspecialchars($e->getMessage()) . '<br>';
    echo '<small style="color:#64748b">' . htmlspecialchars($e->getFile() . ' ligne ' . $e->getLine()) . '</small>';
    echo '</div>';
}
