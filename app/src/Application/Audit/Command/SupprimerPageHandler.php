<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ProjetRepository;

final readonly class SupprimerPageHandler
{
    public function __construct(private ProjetRepository $projets)
    {
    }

    public function __invoke(SupprimerPage $command): void
    {
        $projet = $this->projets->get($command->projetId);

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        $projet->supprimerPage($command->pageId);
        $this->projets->save($projet);
    }
}
