<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Command\LancerAudit;
use App\Application\Audit\Command\LancerAuditHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * POST /api/projets/{projetId}/auditer : point d'entrée unique de déclenchement
 * d'un audit. Le moteur du référentiel du projet décide du mode : asynchrone
 * (RGAA → scan planifié, 202) ou synchrone (complexité → constats produits, 200).
 */
final readonly class LancerAuditController
{
    public function __construct(private LancerAuditHandler $lancer)
    {
    }

    #[Route('/api/projets/{projetId}/auditer', name: 'api_projets_auditer', methods: ['POST'])]
    public function __invoke(string $projetId): JsonResponse
    {
        try {
            $resultat = ($this->lancer)(new LancerAudit($projetId));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(
            ['synchrone' => $resultat->synchrone, 'constatsGeneres' => $resultat->constatsGeneres],
            $resultat->synchrone ? 200 : 202,
        );
    }
}
