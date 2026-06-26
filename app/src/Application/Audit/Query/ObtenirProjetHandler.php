<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\ProjetRepository;

final readonly class ObtenirProjetHandler
{
    public function __construct(private ProjetRepository $projets)
    {
    }

    public function __invoke(ObtenirProjet $query): ?Projet
    {
        return $this->projets->get($query->id);
    }
}
