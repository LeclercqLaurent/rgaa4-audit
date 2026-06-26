<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Command\ModifierPage;
use App\Application\Audit\Command\ModifierPageHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PATCH /api/projets/{projetId}/pages/{pageId} : modifie l'URL et le titre d'une page.
 */
final readonly class ModifierPageController
{
    public function __construct(private ModifierPageHandler $modifier)
    {
    }

    #[Route('/api/projets/{projetId}/pages/{pageId}', name: 'api_projets_page_modifier', methods: ['PATCH'])]
    public function __invoke(string $projetId, string $pageId, Request $request): JsonResponse
    {
        $payload = $this->payload($request);
        $url = is_string($payload['url'] ?? null) ? $payload['url'] : '';
        $titre = is_string($payload['titre'] ?? null) ? $payload['titre'] : '';

        if ('' === $url || '' === $titre) {
            return new JsonResponse(['error' => 'Champs requis : url, titre.'], 400);
        }

        try {
            ($this->modifier)(new ModifierPage($projetId, $pageId, $url, $titre));
        } catch (DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], $e instanceof ProjetIntrouvable ? 404 : 400);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $decoded = json_decode($request->getContent(), true);

        return is_array($decoded) ? $decoded : [];
    }
}
