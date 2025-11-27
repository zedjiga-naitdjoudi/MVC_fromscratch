<?php

namespace App\Service;

use App\Core\Database;
use App\Model\User;

class UserRepository
{
    private Database $db;
    private string $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->query($sql, ['email' => $email]);
        
        $data = $stmt->fetch();
        return $data ? $this->hydrate($data) : null;
    }

    public function save(User $user): int
    {
        $sql = "INSERT INTO {$this->table} (name, email, password, confirmation_token, is_confirmed)
                VALUES (:name, :email, :password, :confirmation_token, :is_confirmed)";
        
        $params = [
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'confirmation_token' => $user->getConfirmationToken(),
            'is_confirmed' => (int) $user->isConfirmed()

        ];
       
        $this->db->query($sql, $params);

        return (int) $this->db->getPdo()->lastInsertId("users_id_seq");
    }

    private function hydrate(array $data): User
    {
        return (new User())
            ->setId($data['id'])
            ->setName($data['name'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setIsConfirmed($data['is_confirmed'])
            ->setConfirmationToken($data['confirmation_token'] ?? null)
            ->setResetToken($data['reset_token'] ?? null)
            ->setResetTokenExpiresAt($data['reset_token_expires_at'] ?? null);
    }
}
