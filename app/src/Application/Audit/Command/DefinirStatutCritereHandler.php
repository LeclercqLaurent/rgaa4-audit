<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\ValueObject\SourceConstat;

final readonly class DefinirStatutCritereHandler
{
    public function __construct(
        private ConstatRepository $constats,
        private ObtenirProjetHandler $obtenirProjet,
    ) {
    }

    public function __invoke(DefinirStatutCritere $command): void
    {
        if (null === ($this->obtenirProjet)(new ObtenirProjet($command->projetId))) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        $this->constats->enregistrerManuel((new Constat(
            $command->projetId,
            $command->referentiel,
            $command->pageUrl,
            $command->critereNumero,
            $command->statut,
            SourceConstat::Manuel,
        ))->avecCommentaire($command->commentaire));
    }
}
