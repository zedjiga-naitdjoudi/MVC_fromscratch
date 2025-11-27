<?php

namespace App\Service;

use App\Model\User;

class AuthService
{

    
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerUser(string $name, string $email, string $password): User
    {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $token = bin2hex(random_bytes(32));

        $user = (new User())
            ->setName($name)
            ->setEmail($email)
            ->setPassword($hashedPassword)
            ->setConfirmationToken($token)
            ->setIsConfirmed(false);

        $user->setId($this->userRepository->save($user));

        return $user;
    }
    public function emailExists(string $email): bool    
    {
        return $this->userRepository->findByEmail($email) !== null;
    }


    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return null;
        }

        if (password_needs_rehash($user->getPassword(), PASSWORD_ARGON2ID)) {
            // À implémenter : mettre à jour le hash en DB
        }

        return $user;
    }
}
