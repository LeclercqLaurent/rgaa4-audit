<?php

declare(strict_types=1);

namespace App\Domain\Reporting\ValueObject;

use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;

/**
 * Une ligne du rapport : l'état d'un critère pour une page, avec ses preuves.
 */
final readonly class LigneRapport
{
    /**
     * @param list<Preuve> $preuves
     */
    public function __construct(
        public string $critereNumero,
        public string $intitule,
        public string $pageUrl,
        public StatutConformite $statut,
        public SourceConstat $source,
        public array $preuves,
    ) {
    }
}
