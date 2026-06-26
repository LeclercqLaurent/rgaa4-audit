<?php

declare(strict_types=1);

namespace App\State\Scan;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Scan\ScanResource;
use App\Application\Scan\Query\ListerScansProjet;
use App\Application\Scan\Query\ListerScansProjetHandler;
use App\Application\Scan\Query\ObtenirScan;
use App\Application\Scan\Query\ObtenirScanHandler;

/**
 * @implements ProviderInterface<ScanResource>
 */
final readonly class ScanProvider implements ProviderInterface
{
    public function __construct(
        private ObtenirScanHandler $obtenir,
        private ListerScansProjetHandler $lister,
        private ScanResourceMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $projetId = is_string($uriVariables['projetId'] ?? null) ? $uriVariables['projetId'] : '';

            return array_map($this->mapper->toResource(...), ($this->lister)(new ListerScansProjet($projetId)));
        }

        $id = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        $scan = ($this->obtenir)(new ObtenirScan($id));

        return null === $scan ? null : $this->mapper->toResource($scan);
    }
}
