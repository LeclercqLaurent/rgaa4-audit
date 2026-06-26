<?php

declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\ValueObject\Email;
use App\Domain\Identity\ValueObject\RoleUtilisateur;

/**
 * Utilisateur de la plateforme. Le mot de passe n'est jamais stocké en clair :
 * seul son haché (Argon2id) est porté ici.
 */
final readonly class Utilisateur
{
    /**
     * @param non-empty-list<RoleUtilisateur> $roles
     */
    public function __construct(
        public string $id,
        public Email $email,
        public string $motDePasseHache,
        public array $roles,
    ) {
    }
}
