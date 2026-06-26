<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\Url;

final readonly class ModifierProjetHandler
{
    public function __construct(private ProjetRepository $projets)
    {
    }

    public function __invoke(ModifierProjet $command): void
    {
        $projet = $this->projets->get($command->id);

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->id);
        }

        if (Referentiel::Rgaa === $projet->type()) {
            // Valide que la cible reste une URL http(s) pour un projet RGAA.
            new Url($command->cible);
        }

        $projet->modifier($command->nom, $command->client, $command->cible);
        $this->projets->save($projet);
    }
}
