<?php
namespace App\Controller;

use App\Core\SessionManager;
use App\Service\PageManager;
use App\Model\Page;

class PageController extends Base   
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
            $this->renderPage('auth/login', 'frontoffice', [
                'error' => 'Connexion requise'
            ]);
            return;
        }

        $pages = $this->manager->findAll();

        $flash = SessionManager::get('flash_success') ?: SessionManager::get('flash_error');
        if ($flash) {
            SessionManager::set('flash_success', null);
            SessionManager::set('flash_error', null);
        }

        $this->renderPage('pages', 'backoffice', [
            'pages' => $pages,
            'flash' => $flash
        ]);
    }

    public function createForm(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index();
            return;
        }

        $csrf = SessionManager::generateCsrfToken();
        $this->renderPage('create', 'backoffice', [
            'csrf_token' => $csrf
        ]);
    }

    public function create(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionManager::set('flash_error', 'Erreur CSRF ou méthode invalide.');
            $this->index();
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $isPublished = isset($_POST['is_published']) && $_POST['is_published'] === '1';

        $errors = [];
        if ($title === '' || $content === '') {
            $errors[] = 'Titre et contenu requis.';
        }
        $slug = $slug === '' ? $this->slugify($title) : $this->slugify($slug);

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
        SessionManager::set($id ? 'flash_success' : 'flash_error', $id ? 'Page créée avec succès.' : 'Erreur lors de la création.');
        $this->index();
    }

    public function editForm(int $id): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index();
            return;
        }

        $page = $this->manager->findById($id);
        if (!$page) {
            SessionManager::set('flash_error', 'Page introuvable.');
            $this->index();
            return;
        }

        $csrf = SessionManager::generateCsrfToken();
        $this->renderPage('edit', 'backoffice', [
            'page' => $page,
            'csrf_token' => $csrf
        ]);
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

        if ($page->getAuthorId() !== SessionManager::get('user_id')) {
            SessionManager::set('flash_error', 'Vous ne pouvez pas modifier cette page.');
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
        SessionManager::set($ok ? 'flash_success' : 'flash_error', $ok ? 'Page mise à jour.' : 'Erreur mise à jour.');
        $this->index();
    }

    public function delete(): void
    {
        if (!SessionManager::get('is_logged_in')) { $this->index(); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            SessionManager::set('flash_error', 'Erreur CSRF.');
            $this->index(); return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) {
            SessionManager::set('flash_error', 'ID manquant pour suppression.');
            $this->index(); return;
        }

        $page = $this->manager->findById($id);
        if (!$page) {
            SessionManager::set('flash_error', 'Page introuvable.');
            $this->index(); return;
        }

        if ($page->getAuthorId() !== SessionManager::get('user_id')) {
            SessionManager::set('flash_error', 'Vous ne pouvez pas supprimer cette page.');
            $this->index(); return;
        }

        $ok = $this->manager->delete($id);
        SessionManager::set($ok ? 'flash_success' : 'flash_error', $ok ? 'Page supprimée.' : 'Erreur suppression.');
        $this->index();
    }

    public function view(string $slug): void
    {
        $page = $this->manager->findBySlug($slug);
        if (!$page || !$page->isPublished()) {
            $this->renderPage('404', 'frontoffice');
            return;
        }

        $this->renderPage('view', 'frontoffice', [
            'page' => $page
        ]);
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return empty($text) ? 'page-' . bin2hex(random_bytes(4)) : $text;
    }
}
