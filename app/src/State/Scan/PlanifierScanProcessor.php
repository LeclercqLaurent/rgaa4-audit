<?php

declare(strict_types=1);

namespace App\State\Scan;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Scan\ScanResource;
use App\Application\Scan\Command\PlanifierScan;
use App\Application\Scan\Command\PlanifierScanHandler;
use App\Application\Scan\Query\ObtenirScan;
use App\Application\Scan\Query\ObtenirScanHandler;
use RuntimeException;

/**
 * POST /api/projets/{projetId}/scans : planifie un scan et renvoie son état initial.
 *
 * @implements ProcessorInterface<mixed, ScanResource>
 */
final readonly class PlanifierScanProcessor implements ProcessorInterface
{
    public function __construct(
        private PlanifierScanHandler $planifier,
        private ObtenirScanHandler $obtenir,
        private ScanResourceMapper $mapper,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ScanResource
    {
        $projetId = is_string($uriVariables['projetId'] ?? null) ? $uriVariables['projetId'] : '';
        $scanId = ($this->planifier)(new PlanifierScan($projetId));
        $scan = ($this->obtenir)(new ObtenirScan($scanId));

        return $this->mapper->toResource($scan ?? throw new RuntimeException('Scan introuvable après planification.'));
    }
}
