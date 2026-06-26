<?php

declare(strict_types=1);

namespace App\State\Audit;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Audit\ProjetResource;
use App\Application\Audit\Query\ListerProjets;
use App\Application\Audit\Query\ListerProjetsHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;

/**
 * @implements ProviderInterface<ProjetResource>
 */
final readonly class ProjetProvider implements ProviderInterface
{
    public function __construct(
        private ListerProjetsHandler $lister,
        private ObtenirProjetHandler $obtenir,
        private ProjetResourceMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return array_map($this->mapper->toResource(...), ($this->lister)(new ListerProjets()));
        }

        $id = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        $projet = ($this->obtenir)(new ObtenirProjet($id));

        return null === $projet ? null : $this->mapper->toResource($projet);
    }
}
