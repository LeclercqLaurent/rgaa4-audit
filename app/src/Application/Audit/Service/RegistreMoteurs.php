<?php

declare(strict_types=1);

namespace App\Application\Audit\Service;

use App\Domain\Audit\Port\MoteurAudit;
use App\Domain\Audit\ValueObject\Referentiel;
use RuntimeException;

/**
 * Aiguille un projet vers le moteur d'audit de son référentiel.
 */
final readonly class RegistreMoteurs
{
    /**
     * @var array<string, MoteurAudit>
     */
    private array $parReferentiel;

    /**
     * @param iterable<MoteurAudit> $moteurs
     */
    public function __construct(iterable $moteurs)
    {
        $map = [];
        foreach ($moteurs as $moteur) {
            $map[$moteur->referentiel()->value] = $moteur;
        }

        $this->parReferentiel = $map;
    }

    public function pour(Referentiel $referentiel): MoteurAudit
    {
        return $this->parReferentiel[$referentiel->value]
            ?? throw new RuntimeException(sprintf('Aucun moteur d\'audit pour le référentiel « %s ».', $referentiel->value));
    }
}
