<?php
namespace App\Controller;

use App\Core\Render;
use App\Core\SessionManager;
use App\Service\PageManager;
use App\Model\Page;

class PageController
{
    private PageManager $manager;

    public function __construct()
    {
        SessionManager::start();
        $this->manager = new PageManager();
    }

public function index(): void
{
    if (!SessionManager::get('is_logged_in')) {
        $render = new Render('auth/login');
        $render->assign('error', 'Connexion requise');
        $render->render();
        return;
    }

    $userId = SessionManager::get('user_id');
    $pages = $this->manager->findByAuthorId($userId);

    $render = new Render('list');
    $render->assign('pages', $pages);

    $flash = SessionManager::get('flash_success') ?: SessionManager::get('flash_error');
    if ($flash) {
        $render->assign('flash', $flash);
        SessionManager::set('flash_success', null);
        SessionManager::set('flash_error', null);
    }
    $render->render();
}

    
    public function createForm(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index(); return;
        }

        $csrf = SessionManager::generateCsrfToken();
        $render = new Render('create');
        $render->assign('csrf_token', $csrf);
        $render->render();
    }

   
    public function create(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index(); return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionManager::set('flash_error', 'Erreur CSRF ou méthode invalide.');
            $this->index(); return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '1' ? true : false;

        // basic validation
        $errors = [];
        if ($title === '' || $content === '') {
            $errors[] = 'Titre et contenu requis.';
        }
        if ($slug === '') {
            // generate slug from title
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }

        // slug uniqueness
        if ($this->manager->findBySlug($slug)) {
            $errors[] = 'Le slug existe déjà, choisissez-en un autre.';
        }

        if (!empty($errors)) {
            SessionManager::set('flash_error', implode(' ', $errors));
            $this->createForm();
            return;
        }

        $page = (new Page())
            ->setTitle($title)
            ->setSlug($slug)
            ->setContent($content)
            ->setIsPublished($isPublished)
            ->setAuthorId(SessionManager::get('user_id') ?? null);

        $id = $this->manager->create($page);
        if ($id) {
            SessionManager::set('flash_success', 'Page créée avec succès.');
        } else {
            SessionManager::set('flash_error', 'Erreur lors de la création.');
        }
        $this->index();
    }

    
    public function editForm(int $id): void
    {
        if (!SessionManager::get('is_logged_in')) { $this->index(); return; }

        $page = $this->manager->findById($id);
        if (!$page) {
            SessionManager::set('flash_error', 'Page introuvable.');
            $this->index(); return;
        }

        $csrf = SessionManager::generateCsrfToken();
        $render = new Render('edit');
        $render->assign('page', $page);
        $render->assign('csrf_token', $csrf);
        $render->render();
    }

    public function update(): void
    {
        if (!SessionManager::get('is_logged_in')) { $this->index(); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionManager::set('flash_error', 'Erreur CSRF.');
            $this->index(); return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $page = $this->manager->findById($id);
        if (!$page) {
            SessionManager::set('flash_error', 'Page introuvable.');
            $this->index(); return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = $this->slugify(trim($_POST['slug'] ?? $title));
        $content = trim($_POST['content'] ?? '');
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '1';

        if ($title === '' || $content === '') {
            SessionManager::set('flash_error', 'Titre et contenu requis.');
            $this->editForm($id);
            return;
        }

        
        $existing = $this->manager->findBySlug($slug);
        if ($existing && $existing->getId() !== $id) {
            SessionManager::set('flash_error', 'Le slug est utilisé par une autre page.');
            $this->editForm($id);
            return;
        }

        $page->setTitle($title)->setSlug($slug)->setContent($content)->setIsPublished($isPublished);
        $ok = $this->manager->update($page);
        if ($ok) SessionManager::set('flash_success', 'Page mise à jour.');
        else SessionManager::set('flash_error', 'Erreur mise à jour.');
        $this->index();
    }

    
public function delete(): void
{
    if (!SessionManager::get('is_logged_in')) { 
        $this->index(); 
        return; 
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        SessionManager::set('flash_error', 'Erreur CSRF.');
        $this->index(); 
        return;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id === 0) {
        SessionManager::set('flash_error', 'ID manquant pour suppression.');
        $this->index(); 
        return;
    }

    $ok = $this->manager->delete($id);
    if ($ok) {
        SessionManager::set('flash_success', 'Page supprimée.');
    } else {
        SessionManager::set('flash_error', 'Erreur suppression.');
    }
    $this->index();
}


   
    public function view(string $slug): void
    {
        $page = $this->manager->findBySlug($slug);
        if (!$page || !$page->isPublished()) {
            // page 404: render a view (no header redirect)
            $render = new Render('404');
            $render->render();
            return;
        }
        $render = new Render('view');
        $render->assign('page', $page);
        $render->render();
    }

    
    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        if (empty($text)) return 'page-' . bin2hex(random_bytes(4));
        return $text;
    }
}
