<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Command\SupprimerPage;
use App\Application\Audit\Command\SupprimerPageHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * DELETE /api/projets/{projetId}/pages/{pageId} : retire une page de l'échantillon.
 */
final readonly class SupprimerPageController
{
    public function __construct(private SupprimerPageHandler $supprimer)
    {
    }

    #[Route('/api/projets/{projetId}/pages/{pageId}', name: 'api_projets_page_supprimer', methods: ['DELETE'])]
    public function __invoke(string $projetId, string $pageId): JsonResponse
    {
        try {
            ($this->supprimer)(new SupprimerPage($projetId, $pageId));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(null, 204);
    }
}
