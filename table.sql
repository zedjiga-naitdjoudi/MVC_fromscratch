-- Fichier SQL pour créer la table users (PostgreSQL)
-- Ce schéma est parfaitement cohérent avec le code PHP refactorisé.
CREATE TABLE IF NOT EXISTS users (
    -- Clé primaire auto-incrémentée
    id SERIAL PRIMARY KEY,
    
    -- Informations d'identification
    name VARCHAR(100) NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Stockera le hachage Argon2id
    
    -- Confirmation de compte
    is_confirmed BOOLEAN DEFAULT FALSE NOT NULL,
    confirmation_token VARCHAR(64) NULL, -- Jeton pour la confirmation par email
    
    -- Réinitialisation de mot de passe
    reset_token VARCHAR(64) NULL, -- Jeton à usage unique pour le mot de passe oublié
    reset_token_expires_at TIMESTAMP NULL, -- Date d'expiration du jeton
    
    -- Horodatage
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


