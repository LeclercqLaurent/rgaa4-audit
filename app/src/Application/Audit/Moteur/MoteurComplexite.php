<?php

declare(strict_types=1);

namespace App\Application\Audit\Moteur;

use App\Application\Audit\Command\AnalyserComplexite;
use App\Application\Audit\Command\AnalyserComplexiteHandler;
use App\Domain\Audit\Entity\Projet;
use App\Domain\Audit\Port\MoteurAudit;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\ResultatAudit;

/**
 * Moteur « Complexité PHP » : analyse synchrone (phpx-complexity), rapide, qui
 * produit immédiatement les constats — pas d'asynchrone (UX : résultat direct).
 */
final readonly class MoteurComplexite implements MoteurAudit
{
    public function __construct(private AnalyserComplexiteHandler $analyser)
    {
    }

    public function referentiel(): Referentiel
    {
        return Referentiel::ComplexitePhp;
    }

    public function auditer(Projet $projet): ResultatAudit
    {
        return ResultatAudit::synchrone(($this->analyser)(new AnalyserComplexite($projet->id())));
    }
}
