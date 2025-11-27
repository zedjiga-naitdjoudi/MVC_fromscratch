<?php

namespace App\Controller;

use App\Core\View;
use App\Core\SessionManager;
use App\Service\AuthService;
use App\Service\UserRepository;

class Auth
{
    private View $view;
    private AuthService $authService;

    public function __construct()
    {
        $this->view = new View();
        $userRepository = new UserRepository(); // Utilise automatiquement le Singleton DB
        $this->authService = new AuthService($userRepository);
        SessionManager::start();
    }

    /*************   FORMULAIRE INSCRIPTION   *************/
    public function signupForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();
        $this->view->render('signup.php', [
            'csrf_token' => $csrfToken,
            'errors' => SessionManager::get('signup_errors')
        ]);
        SessionManager::set('signup_errors', null);
    }

    /*************   ENREGISTREMENT INSCRIPTION   *************/
    public function signup(): void
    {
        $errors = [];

        /* 1) Sécurité CSRF + Vérification méthode */
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $errors[] = "Erreur de sécurité: Jeton CSRF invalide.";
            SessionManager::set('signup_errors', $errors);
            header('Location: /signup');
            exit;
        }

        /* 2) Vérification existence des champs */
        if (
            !isset($_POST['name'], $_POST['email'], $_POST['pwd'], $_POST['pwdConfirm']) ||
            empty($_POST['name']) ||
            empty($_POST['email']) ||
            empty($_POST['pwd']) ||
            empty($_POST['pwdConfirm']) ||
            count($_POST) !== 5
        ) {
            $errors[] = "Tentative de XSS ou champs manquants.";
            SessionManager::set('signup_errors', $errors);
            header('Location: /signup');
            exit;
        }

        /* 3) Nettoyage manuel */
        $name = ucwords(strtolower(trim($_POST['name'])));
        $email = strtolower(trim($_POST['email']));
        $password = $_POST["pwd"];
        $passwordConfirm = $_POST["pwdConfirm"];

        /******** 4) VALIDATIONS ********/

        // Nom
        if (strlen($name) < 2) {
            $errors[] = "Votre prénom doit faire au minimum 2 caractères.";
        }

        // Email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Votre email n'est pas correct.";
        } else {
            if ($this->authService->emailExists($email)) {
                $errors[] = "Votre email existe déjà en base de données.";
            }

        }

        // Mot de passe
        if (
            strlen($password) < 8 ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            $errors[] = "Votre mot de passe doit faire au minimum 8 caractères avec min, maj, chiffres.";
        }

        // Confirmation
        if ($password !== $passwordConfirm) {
            $errors[] = "Votre mot de passe de confirmation ne correspond pas.";
        }

        /******** 5) SI PAS D’ERREURS → INSCRIPTION ********/
        if (empty($errors)) {
            try {
                $this->authService->registerUser($name, $email, $password);
                header('Location: /login');
                exit;
            } catch (\Exception $e) {
                $errors[] = "Erreur lors de l'inscription: " . $e->getMessage();
            }
        }

        SessionManager::set('signup_errors', $errors);
        header('Location: /signup');
        exit;
    }

    /************* FORMULAIRE LOGIN *************/
    public function loginForm(): void
    {
        $csrfToken = SessionManager::generateCsrfToken();
        $this->view->render('login.php', [
            'csrf_token' => $csrfToken,
            'error' => SessionManager::get('login_error')
        ]);
        SessionManager::set('login_error', null);
    }

    /************* CONNEXION *************/
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
            !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionManager::set('login_error', "Erreur de sécurité: Jeton CSRF invalide.");
            header('Location: /login');
            exit;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            SessionManager::set('login_error', "Veuillez remplir tous les champs correctement.");
            header('Location: /login');
            exit;
        }

        $user = $this->authService->authenticate($email, $password);

        if ($user) {
            SessionManager::regenerateId();
            SessionManager::set('user_id', $user->getId());
            SessionManager::set('is_logged_in', true);
            
            header('Location: /dashboard');
            exit;
        }

        SessionManager::set('login_error', "Identifiants invalides.");
        header('Location: /login');
        exit;
    }

    /************* LOGOUT *************/
    public function logout(): void
    {
        SessionManager::destroy();
        header('Location: /');
        exit;
    }
}
