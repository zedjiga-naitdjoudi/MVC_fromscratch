<?php 
namespace App\Service;

use App\Core\Database;
use App\Model\Page;
use PDO;

class PageManager {
    private PDO $pdo;
    private string $table = 'pages';

    public function __construct(){
        $this->pdo = Database::getInstance()->getPdo();
    }

    
    public function hydrate(array $d):Page {
        $p = new Page();
        $p->setId((int)$d['id'])
          ->setTitle($d['title'])
          ->setSlug($d['slug'])
          ->setContent($d['content'])
          ->setIsPublished((bool)$d['is_published'])
          ->setAuthorId(isset($d['author_id']) ? (int)$d['author_id'] : null)
          ->setCreatedAt($d['created_at'] ?? null)
          ->setUpdatedAt($d['updated_at'] ?? null);
        return $p;
    }
    

public function findAll(): array
{
    $sql = "SELECT * FROM {$this->table} ORDER BY updated_at DESC";
    $stmt = $this->pdo->query($sql);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $pages = [];
    foreach ($rows as $row) {
        $page = (new Page())
            ->setId($row['id'])
            ->setTitle($row['title'])
            ->setSlug($row['slug'])
            ->setContent($row['content'])
            ->setIsPublished((bool)$row['is_published'])
            ->setAuthorId($row['author_id'])
            ->setUpdatedAt($row['updated_at']);
        $pages[] = $page;
    }
    return $pages;
}


    public function findById(int $id): ?Page{
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;

    }
    public function findBySlug(string $slug): ?Page{
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;

    }

    /* public function findBySlug(string $slugOrId): ?Page {
    if (is_numeric($slugOrId)) {
        return $this->findById((int)$slugOrId);
    }
    $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['slug' => $slugOrId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    return $data ? $this->hydrate($data) : null;
}
**/

    public function create (Page $page): int//retourne id
    {
        $sql = "INSERT INTO {$this->table} (title, slug, content, is_published, author_id, created_at)
                VALUES (:title, :slug, :content, :is_published, :author_id, NOW()) RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'content' => $page->getContent(),
            'is_published'=> $page->isPublished() ? 1 : 0, // ✅is_published' => (bool)$page->isPublished(),
            'author_id' => $page->getAuthorId()

        ]);
        $res = $stmt->fetch(PDO::FETCH_NUM);
        return isset($res[0]) ? (int)$res[0] : 0;

    } 
    public function update(Page $page): bool
    {
        $sql = "UPDATE {$this->table} SET title = :title, slug = :slug, content = :content, is_published = :is_published, author_id = :author_id WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'content' => $page->getContent(),
            'is_published'=> $page->isPublished() ? 1 : 0, // ✅
            'author_id' => $page->getAuthorId(),
            'id' => $page->getId()
        ]);
    }

    public function delete (int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
//ok je crois que la on est bons 


}