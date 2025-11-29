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

    protected function renderPage(string $view, string $template = "frontoffice", array $data = []):void{
        $render = new Render($view, $template);  
        if(!empty($data)){
            foreach ($data as $key => $value){
            $render->assign($key, $value);
            }
        }
        $render->render();
    }

public function dashboard(): void
{
    if (!SessionManager::get('is_logged_in')) {
        $this->renderPage('login', 'frontoffice', [
            'error' => "Vous devez être connecté."
        ]);
        return;
    }

    $this->renderPage('dashboard', 'backoffice', [
        'title' => 'Tableau de Bord',
        'user_id' => SessionManager::get('user_id')
    ]);
}


}
