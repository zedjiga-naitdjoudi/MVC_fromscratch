<?php

namespace App\Service;

use App\Core\Database;
use App\Model\User;
use PDO;




class UserRepository
{
    private PDO $pdo;
    private string $table = '"users"';

    public function __construct()
    {
       $this->pdo = Database::getInstance()->getPdo();
      
    }
    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute (['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }
    public function emailExists(string $email): bool    
    {
    return $this->findByEmail($email) !== null;
    }
    public function findByConfirmationToken(string $token): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE confirmation_token = :token LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute (['token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }
    public function findByResetToken(string $token): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute (['token' => $token]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrate($data) : null;
    }
    

    public function save(User $user): int
    {
        $sql = "INSERT INTO {$this->table} (name, email, password, confirmation_token, is_confirmed)
                VALUES (:name, :email, :password, :confirmation_token, :is_confirmed)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'confirmation_token' => $user->getConfirmationToken(),
            'is_confirmed' => (int)$user->isConfirmed()
        ]);
       
        return (int) $this->pdo->lastInsertId();
        
    }
  

    public function markConfirmed(int $userId): void
    {
        $sql = "UPDATE {$this->table} SET is_confirmed = true, confirmation_token = NULL WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $userId]);
    }

    public function setResetToken(int $userId, string $token, string $expiresAt): void
    {
        $sql = "UPDATE {$this->table} 
                SET reset_token = :token, reset_token_expires_at = :expires 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'token' => $token,
            'expires' => $expiresAt,
            'id' => $userId
        ]);
    }

    public function updatePassword(int $userId, string $hashedPassword): void
    {
        $sql = "UPDATE {$this->table} 
                SET password = :pwd, reset_token = NULL, reset_token_expires_at = NULL 
             WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'pwd' => $hashedPassword,
            'id' => $userId
        ]);
}
    private function hydrate(array $data): User
    {
        $u = new User();
        $u->setId((int)$data['id'])
          ->setName($data['name'] ?? null)
          ->setEmail($data['email'])
          ->setPassword($data['password'])
          ->setIsConfirmed((bool)$data['is_confirmed'])
          ->setConfirmationToken($data['confirmation_token'] ?? null)
          ->setResetToken($data['reset_token'] ?? null)
          ->setResetTokenExpiresAt($data['reset_token_expires_at'] ?? null);
        return $u;
    }
}
