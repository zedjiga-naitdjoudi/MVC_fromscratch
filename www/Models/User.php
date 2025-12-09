<?php

namespace App\Model;

class User
{
    protected ?int $id = null;
    protected ?string $name = null;
    protected ?string $email = null;
    protected ?string $password = null;
    protected ?bool $isConfirmed = false;
    protected ?string $confirmationToken = null;
    protected ?string $resetToken = null;
    protected ?string $resetTokenExpiresAt = null;
    protected string $role = 'ROLE_USER';


    // Getters et Setters (omission pour la concision)
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }
    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function getPassword(): ?string { return $this->password; }
    public function setPassword(?string $password): self { $this->password = $password; return $this; }
    public function isConfirmed(): bool { return $this->isConfirmed;}
    public function setIsConfirmed(bool $confirmed): self{$this->isConfirmed = $confirmed;return $this;}
    public function getConfirmationToken(): ?string { return $this->confirmationToken; }
    public function setConfirmationToken(?string $confirmationToken): self { $this->confirmationToken = $confirmationToken; return $this; }
    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $resetToken): self { $this->resetToken = $resetToken; return $this; }
    public function getResetTokenExpiresAt(): ?string { return $this->resetTokenExpiresAt; }
    public function setResetTokenExpiresAt(?string $resetTokenExpiresAt): self { $this->resetTokenExpiresAt = $resetTokenExpiresAt; return $this; }
    public function getRole(): string { return $this->role; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function isAdmin(): bool { return $this->role === 'ROLE_ADMIN';}
    public function isEditor(): bool { return $this->role === 'ROLE_EDITOR' || $this->isAdmin();}
}
