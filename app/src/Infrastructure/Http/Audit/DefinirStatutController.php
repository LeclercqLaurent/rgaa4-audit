<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Command\DefinirStatutCritere;
use App\Application\Audit\Command\DefinirStatutCritereHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\StatutConformite;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PUT /api/projets/{projetId}/constats : surcharge manuelle du statut d'un
 * critère pour une page (corps JSON : pageUrl, critereNumero, statut, commentaire).
 */
final readonly class DefinirStatutController
{
    public function __construct(private DefinirStatutCritereHandler $definir)
    {
    }

    #[Route('/api/projets/{projetId}/constats', name: 'api_projets_constat_definir', methods: ['PUT'])]
    public function __invoke(string $projetId, Request $request): JsonResponse
    {
        $payload = $this->payload($request);
        $pageUrl = is_string($payload['pageUrl'] ?? null) ? $payload['pageUrl'] : '';
        $critere = is_string($payload['critereNumero'] ?? null) ? $payload['critereNumero'] : '';
        $statut = StatutConformite::tryFrom(is_string($payload['statut'] ?? null) ? $payload['statut'] : '');
        $commentaire = is_string($payload['commentaire'] ?? null) ? $payload['commentaire'] : null;
        $referentiel = Referentiel::tryFrom(is_string($payload['referentiel'] ?? null) ? $payload['referentiel'] : '') ?? Referentiel::Rgaa;

        if ('' === $pageUrl || '' === $critere || null === $statut) {
            return new JsonResponse(['error' => 'Champs requis : pageUrl, critereNumero, statut (valeur valide).'], 400);
        }

        try {
            ($this->definir)(new DefinirStatutCritere($projetId, $pageUrl, $critere, $statut, $commentaire, $referentiel));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
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
