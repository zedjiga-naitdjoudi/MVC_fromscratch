<?php

namespace App\Controller;

use App\Core\Render;
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

    public function signupForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();

        $this->renderPage("signup", "frontoffice", [
            "csrf_token" => $csrfToken,
            "errors" => SessionManager::get("signup_errors")
        ]);
    }

    public function signup(): void
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')
        ) {
            $this->renderPage("signup", "frontoffice", [
                "errors" => ["Erreur CSRF."]
            ]);
            return;
        }

        if (
            !isset($_POST['name'], $_POST['email'], $_POST['pwd'], $_POST['pwdConfirm']) ||
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['pwd']) ||
            empty($_POST['pwdConfirm'])
        ) {
            $this->renderPage("signup", "frontoffice", [
                "errors" => ["Champs manquants ou tentative XSS."]
            ]);
            return;
        }

        $name = ucwords(strtolower(trim($_POST['name'])));
        $email = strtolower(trim($_POST['email']));
        $password = $_POST["pwd"];
        $passwordConfirm = $_POST["pwdConfirm"];

        if (strlen($name) < 2) {
            $errors[] = "Votre prénom doit faire au minimum 2 caractères.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email incorrect.";
        } elseif ($this->repo->emailExists($email)) {
            $errors[] = "Email déjà utilisé.";
        }

        if (
            strlen($password) < 8 ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            $errors[] = "Mot de passe trop faible.";
        }

        if ($password !== $passwordConfirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!empty($errors)) {
            $this->renderPage("signup", "frontoffice", ["errors" => $errors]);
            return;
        }

        try {
            $this->authService->registerUser($name, $email, $password);

            $this->renderPage("login", "frontoffice", [
                "message" => "Votre compte a été créé. Vous pouvez maintenant vous connecter."
            ]);
            return;
        } catch (\Exception $e) {
            $this->renderPage("signup", "frontoffice", [
                "errors" => ["Erreur interne : " . $e->getMessage()]
            ]);
            return;
        }
    }

    public function activation(): void
    {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            $this->renderPage("login", "frontoffice", [
                "error" => "Lien d'activation invalide."
            ]);
            return;
        }

        if ($this->authService->confirm($token)) {
            $this->renderPage("login", "frontoffice", [
                "message" => "Votre compte a été activé !"
            ]);
            return;
        }

        $this->renderPage("login", "frontoffice", [
            "error" => "Lien invalide ou déjà utilisé."
        ]);
    }

    public function loginForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();

        $this->renderPage("login", "frontoffice", [
            "csrf_token" => $csrfToken,
            "error" => SessionManager::get("login_error")
        ]);
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')
        ) {
            $this->renderPage("login", "frontoffice", ["error" => "Erreur CSRF."]);
            return;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderPage("login", "frontoffice", ["error" => "Champs invalides."]);
            return;
        }

        $user = $this->authService->authenticate($email, $password);

        if (!$user) {
            $this->renderPage("login", "frontoffice", [
                "error" => "Identifiants incorrects."
            ]);
            return;
        }

        SessionManager::regenerateId();
        SessionManager::set("user_id", $user->getId());
        SessionManager::set("is_logged_in", true);

        $this->renderPage("dashboard", "backoffice");
    }

    public function logout(): void
    {
        SessionManager::destroy();
        $this->renderPage("home");
    }
}
