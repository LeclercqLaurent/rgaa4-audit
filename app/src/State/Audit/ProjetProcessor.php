<?php

declare(strict_types=1);

namespace App\State\Audit;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Audit\Input\CreerProjetInput;
use App\ApiResource\Audit\ProjetResource;
use App\Application\Audit\Command\CreerProjet;
use App\Application\Audit\Command\CreerProjetHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use RuntimeException;

/**
 * POST /api/projets : crée un projet d'audit et renvoie son DTO.
 *
 * @implements ProcessorInterface<CreerProjetInput, ProjetResource>
 */
final readonly class ProjetProcessor implements ProcessorInterface
{
    public function __construct(
        private CreerProjetHandler $creer,
        private ObtenirProjetHandler $obtenir,
        private ProjetResourceMapper $mapper,
    ) {
    }

    /**
     * @param CreerProjetInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjetResource
    {
        $id = ($this->creer)(new CreerProjet($data->nom, $data->client, $data->urlReference));
        $projet = ($this->obtenir)(new ObtenirProjet($id));

        return $this->mapper->toResource($projet ?? throw new RuntimeException('Projet créé introuvable.'));
    }
}
