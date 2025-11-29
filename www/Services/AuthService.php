<?php

namespace App\Service;

use App\Model\User;
use Exception;

class AuthService
{

    
    private UserRepository $repo;
    private MailerService $mailer;
    
  

    public function __construct(UserRepository $repo, MailerService $mailer)
    {
        $this->repo = $repo;
        $this->mailer = $mailer;
        

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

        $user->setId($this->repo->save($user));

        $activationLink = sprintf('http://localhost:8080/activation?token=%s&email=%s', $token, urlencode($email));
        $this->mailer->sendActivation($email, $name, $activationLink);


        return $user;
    }
    public function confirm(string $token): bool
    {
        $user = $this->repo->findByConfirmationToken($token);
        if (!$user) return false;
        $this->repo->markConfirmed($user->getId());
        return true;
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->repo->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user->getPassword())) return null;
        if (!$user->isConfirmed()) return null; 
        if (password_needs_rehash($user->getPassword(), PASSWORD_ARGON2ID)) {
            $this->repo->updatePassword($user->getId(), password_hash($password, PASSWORD_ARGON2ID));
        }
        return $user;
    }

    public function requestPasswordReset(string $email): bool
    {
        $user = $this->repo->findByEmail($email);
        if (!$user) return false;
        $token = bin2hex(random_bytes(32));
        $expires = (new \DateTime('+1 hour'))->format('Y-m-d H:i:s');
        $this->repo->setResetToken($user->getId(), $token, $expires);
        $resetLink = sprintf('http://localhost:8080/reset-password?token=%s&email=%s', $token, urlencode($email));
        $this->mailer->sendPasswordReset($email, $user->getName(), $resetLink);
        return true;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = $this->repo->findByResetToken($token);
        if (!$user) return false;
        // check expiry
        $expires = $user->getResetTokenExpiresAt();
        if ($expires && new \DateTime($expires) < new \DateTime()) return false;
        $hashed = password_hash($newPassword, PASSWORD_ARGON2ID);
        $this->repo->updatePassword($user->getId(), $hashed);
        return true;
    }

    

}
