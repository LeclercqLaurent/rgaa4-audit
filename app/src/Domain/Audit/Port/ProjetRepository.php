<?php

declare(strict_types=1);

namespace App\Domain\Audit\Port;

use App\Domain\Audit\Entity\Projet;

interface ProjetRepository
{
    public function save(Projet $projet): void;

    /**
     * @return list<Projet>
     */
    public function findAll(): array;

    public function get(string $id): ?Projet;

    public function supprimer(string $id): void;
}
