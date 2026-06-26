<?php

declare(strict_types=1);

namespace App\Domain\Identity\Port;

use App\Domain\Identity\Entity\Utilisateur;

interface UtilisateurRepository
{
    public function save(Utilisateur $utilisateur): void;

    public function parEmail(string $email): ?Utilisateur;
}
