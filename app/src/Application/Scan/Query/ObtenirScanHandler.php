<?php

declare(strict_types=1);

namespace App\Application\Scan\Query;

use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\Port\ScanRepository;

final readonly class ObtenirScanHandler
{
    public function __construct(private ScanRepository $scans)
    {
    }

    public function __invoke(ObtenirScan $query): ?Scan
    {
        return $this->scans->get($query->id);
    }
}
