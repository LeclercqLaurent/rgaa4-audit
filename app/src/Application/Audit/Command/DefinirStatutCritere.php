<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\StatutConformite;

/**
 * Surcharge manuelle, par l'auditeur, du statut d'un critère pour une page.
 */
final readonly class DefinirStatutCritere
{
    public function __construct(
        public string $projetId,
        public string $pageUrl,
        public string $critereNumero,
        public StatutConformite $statut,
        public ?string $commentaire = null,
        public Referentiel $referentiel = Referentiel::Rgaa,
    ) {
    }
}
