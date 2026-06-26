<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

use DateTimeImmutable;

/**
 * Résumé léger d'un scan (statut + date), sans ses résultats — pour les listes.
 */
final readonly class ResumeScan
{
    public function __construct(
        public StatutScan $statut,
        public DateTimeImmutable $dateCreation,
    ) {
    }
}
