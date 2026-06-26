<?php

declare(strict_types=1);

namespace App\Application\Audit\Moteur;

use App\Application\Scan\Command\PlanifierScan;
use App\Application\Scan\Command\PlanifierScanHandler;
use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\MoteurAudit;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\ResultatAudit;

/**
 * Moteur « RGAA 4 » : planifie un scan axe-core asynchrone (Messenger) sur
 * l'échantillon de pages ; les constats sont produits par le worker.
 */
final readonly class MoteurRgaa implements MoteurAudit
{
    public function __construct(private PlanifierScanHandler $planifier)
    {
    }

    public function referentiel(): Referentiel
    {
        return Referentiel::Rgaa;
    }

    public function auditer(Projet $projet): ResultatAudit
    {
        ($this->planifier)(new PlanifierScan($projet->id()));

        return ResultatAudit::asynchrone();
    }
}
