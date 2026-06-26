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
use App\Application\Scan\Query\DerniersScansParProjet;
use App\Application\Scan\Query\DerniersScansParProjetHandler;
use App\Domain\Audit\Entity\Projet;

/**
 * @implements ProviderInterface<ProjetResource>
 */
final readonly class ProjetProvider implements ProviderInterface
{
    public function __construct(
        private ListerProjetsHandler $lister,
        private ObtenirProjetHandler $obtenir,
        private DerniersScansParProjetHandler $derniersScans,
        private ProjetResourceMapper $mapper,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $resumes = ($this->derniersScans)(new DerniersScansParProjet());

            return array_map(
                fn (Projet $projet): ProjetResource => $this->mapper->toResource($projet, $resumes[$projet->id()] ?? null),
                ($this->lister)(new ListerProjets()),
            );
        }

        $id = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        $projet = ($this->obtenir)(new ObtenirProjet($id));

        return null === $projet ? null : $this->mapper->toResource($projet);
    }
}
