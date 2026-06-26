<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Scan\Port\ScanRepository;

/**
 * Supprime un projet et tout ce qui en dépend : constats, scans, puis le projet
 * (et son échantillon de pages, en cascade).
 */
final readonly class SupprimerProjetHandler
{
    public function __construct(
        private ProjetRepository $projets,
        private ScanRepository $scans,
        private ConstatRepository $constats,
    ) {
    }

    public function __invoke(SupprimerProjet $command): void
    {
        if (null === $this->projets->get($command->id)) {
            throw ProjetIntrouvable::pour($command->id);
        }

        $this->constats->supprimerPourProjet($command->id);
        $this->scans->supprimerPourProjet($command->id);
        $this->projets->supprimer($command->id);
    }
}
