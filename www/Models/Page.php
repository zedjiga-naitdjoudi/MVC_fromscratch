<?php 
namespace App\Model; 

class Page {
    private ?int $id = null;
    private string $title ='';
    private string $slug ='';
    private string $content ='';
    private bool $isPublished = false; 
    private ?int $authorId = null; 
    private ?string $createdAt = null; 
    private ?string $updatedAt = null; 
    private ?string $authorEmail = null;

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): self { $this->id = $id; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): self { $this->slug = $slug; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }

    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $pub): self { $this->isPublished = $pub; return $this; }

    public function getAuthorId(): ?int { return $this->authorId; }
    public function setAuthorId(?int $id): self { $this->authorId = $id; return $this; }

    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function setCreatedAt(?string $ts): self { $this->createdAt = $ts; return $this; }

    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function setUpdatedAt(?string $ts): self { $this->updatedAt = $ts; return $this; }

     public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }
    public function setAuthorEmail(?string $email): self
    {
        $this->authorEmail = $email;
        return $this;
    }
}