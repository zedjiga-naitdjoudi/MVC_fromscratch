<?php

namespace App\Controller;

use App\Service\UserRepository;
use App\Core\SessionManager;

class AdminUserController extends Base
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
        SessionManager::start();
    }

    private function denyAccess(): void
    {
        $this->renderPage('home', 'frontoffice', [
            'title' => 'Accès interdit',
            'content' => 'Vous devez être administrateur pour accéder à cette page.'
        ]);
    }

    public function index(): void
    {
        if (SessionManager::get('user_role') !== 'ROLE_ADMIN') {
            $this->denyAccess();
            return;
        }

        $users = $this->repo->findAll();

        $this->renderPage('users', 'backoffice', [
            'users' => $users,
            'flash' => SessionManager::get('flash_success') ?: SessionManager::get('flash_error')
        ]);

        SessionManager::set('flash_success', null);
        SessionManager::set('flash_error', null);
    }

    public function updateRole(): void
    {
        if (SessionManager::get('user_role') !== 'ROLE_ADMIN') {
            $this->denyAccess();
            return;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id === SessionManager::get('user_id')) {
            SessionManager::set('flash_error', 'Action interdite.');
            $this->index();
            return;
        }

        $role = $_POST['role'] ?? null;
        $allowedRoles = ['ROLE_USER', 'ROLE_EDITOR', 'ROLE_ADMIN'];

        if (!in_array($role, $allowedRoles, true)) {
            SessionManager::set('flash_error', 'Rôle invalide.');
            $this->index();
            return;
        }

        if ($this->repo->updateRole($id, $role)) {
            SessionManager::set('flash_success', 'Rôle mis à jour.');
        } else {
            SessionManager::set('flash_error', 'Échec de la mise à jour.');
        }

        $this->index();
    }
}
