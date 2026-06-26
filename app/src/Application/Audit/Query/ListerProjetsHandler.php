<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\ProjetRepository;

final readonly class ListerProjetsHandler
{
    public function __construct(private ProjetRepository $projets)
    {
    }

    /**
     * @return list<Projet>
     */
    public function __invoke(ListerProjets $query): array
    {
        return $this->projets->findAll();
    }
}
