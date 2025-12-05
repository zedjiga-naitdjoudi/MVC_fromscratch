<?php

namespace App\Controller;

use App\Core\SessionManager;
use App\Service\AuthService;
use App\Service\UserRepository;
use App\Service\MailerService;

class Auth extends Base
{
    private AuthService $authService;
    private UserRepository $repo;
    private array $errors = [];

    public function __construct()
    {
        $this->repo = new UserRepository();
        $mailer = new MailerService();
        $this->authService = new AuthService($this->repo, $mailer);

        SessionManager::start();
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function signupForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();

        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => $csrfToken,
            "errors" => []
        ]);
    }

    /**
     * Traite l'inscription d'un nouvel utilisateur
     */
public function signup(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Méthode non autorisée."]
        ]);
        return;
    }

    if (!SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Erreur CSRF. Veuillez réessayer."]
        ]);
        return;
    }

    $name = ucwords(strtolower(trim($_POST['name'] ?? '')));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST["pwd"] ?? '';
    $passwordConfirm = $_POST["pwdConfirm"] ?? '';

    if (!$name || !$email || !$password || !$passwordConfirm) {
        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Tous les champs sont obligatoires."]
        ]);
        return;
    }

    if (strlen($name) < 2) {
        $this->errors[] = "Votre prénom doit faire au minimum 2 caractères.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->errors[] = "Email incorrect.";
    } else {
        try {
            if ($this->repo->emailExists($email)) {
                $this->errors[] = "Email déjà utilisé.";
            }
        } catch (\Exception $e) {
            $this->errors[] = "Erreur lors de la vérification de l'email.";
        }
    }

    if (
        strlen($password) < 8 ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        $this->errors[] = "Mot de passe trop faible (min 8 caractères, majuscules, minuscules, chiffres).";
    }

    if ($password !== $passwordConfirm) {
        $this->errors[] = "Les mots de passe ne correspondent pas.";
    }

    if (!empty($this->errors)) {
        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => $this->errors
        ]);
        return;
    }

    try {
        $this->authService->registerUser($name, $email, $password);

        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "message" => "Votre compte a été créé. Un email de confirmation vous a été envoyé. Veuillez activer votre compte avant de vous connecter."
        ]);
        return;
    } catch (\PDOException $e) {
        if ($e->getCode() === '23505') {
            $this->renderPage("signup", "frontoffice", [
                "csrf_token" => SessionManager::generateCsrfToken(),
                "errors" => ["Email déjà utilisé."]
            ]);
            return;
        }
        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Erreur lors de l'inscription."]
        ]);
        return;
    }
}


    /**
     * Active le compte utilisateur via le token reçu par email
     */
public function activation(): void
{
    $token = $_GET['token'] ?? null;

    if (!$token) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "error" => "Lien d'activation invalide."
        ]);
        return; // <-- stoppe ici
    }

    $confirmed = $this->authService->confirm($token);
    
    if ($confirmed) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "message" => "Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter."
        ]);
        return; // <-- stoppe ici
    }

    $this->renderPage("login", "frontoffice", [
        "csrf_token" => SessionManager::generateCsrfToken(),
        "error" => "Lien d'activation invalide ou déjà utilisé."
    ]);
    return; // <-- stoppe ici
}


    /**
     * Affiche le formulaire de connexion
     */
public function loginForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();

        $this->renderPage("login", "frontoffice", [
            "csrf_token" => $csrfToken,
            "error" => null,
            "message" => null
        ]);
    }

    /**
     * Traite la connexion de l'utilisateur
     */
public function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')
    ) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "error" => "Erreur CSRF."
        ]);
        return;
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "error" => "Champs invalides."
        ]);
        return;
    }

    $user = $this->authService->authenticate($email, $password);

    if (!$user) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "error" => "Identifiants incorrects ou compte non activé. Veuillez vérifier vos emails."
        ]);
        return;
    }

    // Connexion réussie
    SessionManager::regenerateId();
    SessionManager::set("user_id", $user->getId());
    SessionManager::set("user_name", $user->getName());
    SessionManager::set("user_email", $user->getEmail());
    SessionManager::set("is_logged_in", true);
    SessionManager::set("is_active", true);

    // 👉 Appeler directement PageController::index()
    $pageController = new \App\Controller\PageController();
    $pageController->index();
}


    /**
     * Déconnexion de l'utilisateur
     */
public function logout(): void
{
    // Détruire la session proprement
    SessionManager::start();
    SessionManager::destroy();

    // Optionnel : message flash
    SessionManager::set('flash_success', 'Vous avez été déconnecté.');

    // Réutiliser la logique de Base::index()
    $base = new \App\Controller\Base();
    $base->index();
}

    private function isAuth(): void
    {
        if (!SessionManager::get("is_logged_in") || 
            !SessionManager::get("is_active")) {
            $this->renderPage("login", "frontoffice", [
                "csrf_token" => SessionManager::generateCsrfToken(),
                "error" => "Vous devez être connecté pour accéder à cette page."
            ]);
        }
    }

    /**
     * Affiche le dashboard (protégé)
     */
   
    public function forgotForm(): void{    
        $csrfToken = SessionManager::generateCsrfToken();
        $this->renderPage("forgot", "frontoffice", [
        "csrf_token" => $csrfToken,
        "errors" => []
    ]);
    }
    public function forgot(): void{
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')
        ) {
            $this->renderPage("forgot", "backoffice", [
                "csrf_token" => SessionManager::generateCsrfToken(),
                "error" => "Erreur CSRF."
            ]);
            return;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderPage("forgot", "frontoffice", [
                "csrf_token" => SessionManager::generateCsrfToken(),
                "errors" => ["Email invalide."]
            ]);
            return;
    }
    
    if ($this->authService->forgotPassword($email)) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "message" => "Un lien de réinitialisation a été envoyé (simulation)."
        ]);
        return;
    }

    $this->renderPage("forgot", "frontoffice", [
        "csrf_token" => SessionManager::generateCsrfToken(),
        "errors" => ["Email introuvable."]
    ]);    





    }
    public function resetForm() : void {
        $token = $_GET['token'] ?? null;
        $csrfToken = SessionManager::generateCsrfToken();

        $this->renderPage("reset", "frontoffice", [
            "csrf_token" => $csrfToken,
            "token" => $token,
            "errors" => []
        ]);  
        
}
public function reset(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
        !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')
    ) {
        $this->renderPage("reset", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Erreur CSRF."]
        ]);
        return;
    }

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['passwordConfirm'] ?? '';

    if ($password !== $confirm) {
        $this->renderPage("reset", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "errors" => ["Les mots de passe ne correspondent pas."]
        ]);
        return;
    }

    if ($this->authService->resetPassword($token, $password)) {
        $this->renderPage("login", "frontoffice", [
            "csrf_token" => SessionManager::generateCsrfToken(),
            "message" => "Votre mot de passe a été réinitialisé avec succès."
        ]);
        return;
    }

    $this->renderPage("reset", "frontoffice", [
        "csrf_token" => SessionManager::generateCsrfToken(),
        "errors" => ["Lien invalide ou expiré."]
    ]);

}
}