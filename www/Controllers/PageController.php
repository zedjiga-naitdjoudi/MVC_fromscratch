<?php
namespace App\Controller;

use App\Core\SessionManager;
use App\Service\PageManager;
use App\Model\Page;
use App\Service\UserRepository;

class PageController extends Base
{
    private PageManager $manager;
    private UserRepository $userRepo;

    public function __construct()
    {
        SessionManager::start();
        $this->manager  = new PageManager();
        $this->userRepo = new UserRepository();
    }

    

    private function hydrateAuthorEmails(array $pages): array
    {
        foreach ($pages as $page) {
            $user = $this->userRepo->findById($page->getAuthorId());
            $page->setAuthorEmail($user ? $user->getEmail() : 'Inconnu');
        }
        return $pages;
    }

  

    public function index(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->renderPage('auth/login', 'frontoffice', [
                'error' => 'Connexion requise'
            ]);
            return;
        }

        $pages = $this->hydrateAuthorEmails(
            $this->manager->findAll()
        );

        $flash = SessionManager::get('flash_success')
              ?: SessionManager::get('flash_error');

        SessionManager::set('flash_success', null);
        SessionManager::set('flash_error', null);

        $this->renderPage('pages', 'backoffice', [
            'pages' => $pages,
            'flash' => $flash
        ]);
    }

 

    public function createForm(): void
    {
        $this->requireLogin();

        $this->renderPage('create', 'backoffice', [
            'csrf_token' => SessionManager::generateCsrfToken(),
            'error' => SessionManager::get('flash_error')
        ]);

        SessionManager::set('flash_error', null);
    }

    public function create(): void
    {
        $this->requireLogin();

        if (!$this->isValidPost()) {
            SessionManager::set('flash_error', 'Erreur CSRF.');
            $this->index();
            return;
        }

        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $slug    = $this->slugify($_POST['slug'] ?? $title);
        $published = isset($_POST['is_published']);

        if ($title === '' || $content === '') {
            SessionManager::set('flash_error', 'Titre et contenu requis.');
            $this->createForm();
            return;
        }

        if ($this->manager->findBySlug($slug)) {
            SessionManager::set('flash_error', 'Slug déjà utilisé.');
            $this->createForm();
            return;
        }

        $page = (new Page())
            ->setTitle($title)
            ->setContent($content)
            ->setSlug($slug)
            ->setIsPublished($published)
            ->setAuthorId(SessionManager::get('user_id'));

        $ok = $this->manager->create($page);

        SessionManager::set(
            $ok ? 'flash_success' : 'flash_error',
            $ok ? 'Page créée.' : 'Erreur création.'
        );

        $this->index();
    }

   
    public function editForm(int $id): void
    {
        $this->requireLogin();

        $page = $this->manager->findById($id);
        if (!$page || !$this->canEdit($page)) {
            SessionManager::set('flash_error', 'Accès refusé.');
            $this->index();
            return;
        }

        $this->renderPage('edit', 'backoffice', [
            'page' => $page,
            'csrf_token' => SessionManager::generateCsrfToken()
        ]);
    }

    public function update(): void
    {
        $this->requireLogin();

        if (!$this->isValidPost()) {
            $this->index();
            return;
        }

        $page = $this->manager->findById((int)$_POST['id']);
        if (!$page || !$this->canEdit($page)) {
            $this->index();
            return;
        }

        $title   = trim($_POST['title']);
        $content = trim($_POST['content']);
        $slug    = $this->slugify($_POST['slug'] ?? $title);
        $published = isset($_POST['is_published']);

        if ($title === '' || $content === '') {
            $this->renderPage('edit', 'backoffice', [
                'page' => $page,
                'errors' => ['Titre et contenu requis.'],
                'csrf_token' => SessionManager::generateCsrfToken()
            ]);
            return;
        }

        $other = $this->manager->findBySlug($slug);
        if ($other && $other->getId() !== $page->getId()) {
            $this->renderPage('edit', 'backoffice', [
                'page' => $page,
                'errors' => ['Slug déjà utilisé.'],
                'csrf_token' => SessionManager::generateCsrfToken()
            ]);
            return;
        }

        $page->setTitle($title)
             ->setContent($content)
             ->setSlug($slug)
             ->setIsPublished($published);

        $ok = $this->manager->update($page);

        SessionManager::set(
            $ok ? 'flash_success' : 'flash_error',
            $ok ? 'Page mise à jour.' : 'Erreur mise à jour.'
        );

        $this->index();
    }

    
   

    public function delete(): void
    {
        $this->requireLogin();

        if (!$this->isValidPost()) {
            $this->index();
            return;
        }

        $page = $this->manager->findById((int)$_POST['id']);
        if (!$page || !$this->canEdit($page)) {
            $this->index();
            return;
        }

        $ok = $this->manager->delete($page->getId());

        SessionManager::set(
            $ok ? 'flash_success' : 'flash_error',
            $ok ? 'Page supprimée.' : 'Erreur suppression.'
        );

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

   

    private function requireLogin(): void
    {
        if (!SessionManager::get('is_logged_in')) {
            $this->index();
            exit;
        }
    }

    private function isValidPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST'
            && SessionManager::verifyCsrfToken($_POST['csrf_token'] ?? '');
    }

    private function canEdit(Page $page): bool
    {
        return SessionManager::get('user_role') === 'ROLE_ADMIN'
            || $page->getAuthorId() === SessionManager::get('user_id');
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = strtolower($text);

        return $text ?: 'page-' . bin2hex(random_bytes(4));
    }
}
