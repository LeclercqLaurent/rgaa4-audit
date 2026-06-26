<?php

declare(strict_types=1);

namespace App\Domain\Audit\Port;

use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\ResultatAudit;

/**
 * Moteur d'audit : déclenche l'audit d'un projet selon son référentiel. Chaque
 * moteur (RGAA via scan, complexité via phpx…) gère son propre mode d'exécution
 * (asynchrone ou synchrone).
 */
interface MoteurAudit
{
    public function referentiel(): Referentiel;

    public function auditer(Projet $projet): ResultatAudit;
}
