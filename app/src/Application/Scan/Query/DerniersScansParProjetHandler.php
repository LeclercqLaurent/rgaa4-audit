<?php

declare(strict_types=1);

namespace App\Application\Scan\Query;

use App\Domain\Scan\Port\ScanRepository;
use App\Domain\Scan\ValueObject\ResumeScan;

final readonly class DerniersScansParProjetHandler
{
    public function __construct(private ScanRepository $scans)
    {
    }

    /**
     * @return array<string, ResumeScan>
     */
    public function __invoke(DerniersScansParProjet $query): array
    {
        return $this->scans->dernierResumeParProjet();
    }
}
