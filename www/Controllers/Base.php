<?php

namespace App\Controller;

use App\Core\Render;
use App\Core\SessionManager;

class Base
{
    private Render $view;

    public function __construct()
    {
        $this->view = new Render('home'); 
        SessionManager::start();
    }

    public function index(): void
   {
        
        $this->view->assign('title', 'Accueil');
        $this->view->assign('content', 'page d\'accueil');
        $this->view->assign('is_logged_in', SessionManager::get('is_logged_in'));
        $this->view->render();
   }


    public function dashboard(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            header('Location: /login');
            exit;
        }
        $this->view->render('dashboard.php', ['title' => 'Tableau de Bord']);
    }
}
