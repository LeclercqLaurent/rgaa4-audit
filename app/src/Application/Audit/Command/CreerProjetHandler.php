<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\ProjetRepository;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\Url;
use App\Domain\Shared\Port\IdGenerator;
use DateTimeImmutable;

final readonly class CreerProjetHandler
{
    public function __construct(
        private ProjetRepository $projets,
        private IdGenerator $ids,
    ) {
    }

    /**
     * @return string Identifiant du projet créé
     */
    public function __invoke(CreerProjet $command): string
    {
        if (Referentiel::Rgaa === $command->type) {
            // Valide que la cible est une URL http(s) (lève UrlInvalide sinon).
            new Url($command->cible);
        }

        $projet = new Projet(
            $this->ids->generate(),
            $command->nom,
            $command->client,
            $command->type,
            $command->cible,
            new DateTimeImmutable(),
        );

        $this->projets->save($projet);

        return $projet->id();
    }
}
