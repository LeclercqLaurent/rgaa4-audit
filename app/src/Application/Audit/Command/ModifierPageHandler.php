<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Audit\ValueObject\Url;

final readonly class ModifierPageHandler
{
    public function __construct(private ProjetRepository $projets)
    {
    }

    public function __invoke(ModifierPage $command): void
    {
        $projet = $this->projets->get($command->projetId);

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        $projet->modifierPage($command->pageId, new Url($command->url), $command->titre);
        $this->projets->save($projet);
    }
}
