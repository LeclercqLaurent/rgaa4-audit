<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Application\Audit\Service\RegistreMoteurs;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\ValueObject\ResultatAudit;

final readonly class LancerAuditHandler
{
    public function __construct(
        private ObtenirProjetHandler $obtenirProjet,
        private RegistreMoteurs $moteurs,
    ) {
    }

    public function __invoke(LancerAudit $command): ResultatAudit
    {
        $projet = ($this->obtenirProjet)(new ObtenirProjet($command->projetId));

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        return $this->moteurs->pour($projet->type())->auditer($projet);
    }
}
