<?php

declare(strict_types=1);

namespace App\State\Audit;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Audit\Command\SupprimerProjet;
use App\Application\Audit\Command\SupprimerProjetHandler;

/**
 * DELETE /api/projets/{id} : supprime le projet et ses données dépendantes.
 *
 * @implements ProcessorInterface<mixed, null>
 */
final readonly class SupprimerProjetProcessor implements ProcessorInterface
{
    public function __construct(private SupprimerProjetHandler $supprimer)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $id = is_string($uriVariables['id'] ?? null) ? $uriVariables['id'] : '';
        ($this->supprimer)(new SupprimerProjet($id));

        return null;
    }
}
