<?php

declare(strict_types=1);

namespace App\State\Audit;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Audit\Input\ModifierProjetInput;
use App\ApiResource\Audit\ProjetResource;
use App\Application\Audit\Command\ModifierProjet;
use App\Application\Audit\Command\ModifierProjetHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use RuntimeException;

/**
 * PATCH /api/projets/{id} : modifie un projet et renvoie son DTO à jour.
 *
 * @implements ProcessorInterface<ModifierProjetInput, ProjetResource>
 */
final readonly class ModifierProjetProcessor implements ProcessorInterface
{
    public function __construct(
        private ModifierProjetHandler $modifier,
        private ObtenirProjetHandler $obtenir,
        private ProjetResourceMapper $mapper,
    ) {
    }

    /**
     * @param ModifierProjetInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjetResource
    {
        $id = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        ($this->modifier)(new ModifierProjet($id, $data->nom, $data->client, $data->cible));
        $projet = ($this->obtenir)(new ObtenirProjet($id));

        return $this->mapper->toResource($projet ?? throw new RuntimeException('Projet introuvable après modification.'));
    }
}
