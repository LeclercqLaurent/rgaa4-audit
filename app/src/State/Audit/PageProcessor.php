<?php

declare(strict_types=1);

namespace App\State\Audit;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Audit\Input\AjouterPageInput;
use App\ApiResource\Audit\ProjetResource;
use App\Application\Audit\Command\AjouterPage;
use App\Application\Audit\Command\AjouterPageHandler;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use RuntimeException;

/**
 * POST /api/projets/{id}/pages : ajoute une page à l'échantillon et renvoie le projet à jour.
 *
 * @implements ProcessorInterface<AjouterPageInput, ProjetResource>
 */
final readonly class PageProcessor implements ProcessorInterface
{
    public function __construct(
        private AjouterPageHandler $ajouter,
        private ObtenirProjetHandler $obtenir,
        private ProjetResourceMapper $mapper,
    ) {
    }

    /**
     * @param AjouterPageInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProjetResource
    {
        $projetId = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        ($this->ajouter)(new AjouterPage($projetId, $data->url, $data->titre));
        $projet = ($this->obtenir)(new ObtenirProjet($projetId));

        return $this->mapper->toResource($projet ?? throw new RuntimeException('Projet introuvable après ajout de page.'));
    }
}
