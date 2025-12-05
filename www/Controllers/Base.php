<?php

namespace App\Controller;

use App\Core\Render;
use App\Core\SessionManager;

class Base
{
    private Render $view;

public function __construct()
    {
        
        SessionManager::start();
    }

public function index(): void
{
    $data = [
        'title' => 'Accueil',
        'content' => 'Ceci est la page d\'accueil.',
        'is_logged_in' => SessionManager::get('is_logged_in')
    ];

    $flash = SessionManager::get('flash_success') ?: SessionManager::get('flash_error');
    if ($flash) {
        $data['flash'] = $flash;
        SessionManager::set('flash_success', null);
        SessionManager::set('flash_error', null);
    }

    $this->renderPage('home', 'frontoffice', $data);
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





}
