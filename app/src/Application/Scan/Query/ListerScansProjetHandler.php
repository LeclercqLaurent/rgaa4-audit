<?php

declare(strict_types=1);

namespace App\Application\Scan\Query;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\Port\ScanRepository;

final readonly class ListerScansProjetHandler
{
    public function __construct(private ScanRepository $scans)
    {
    }

    /**
     * @return list<Scan>
     */
    public function __invoke(ListerScansProjet $query): array
    {
        return $this->scans->findByProjet($query->projetId);
    }
}
