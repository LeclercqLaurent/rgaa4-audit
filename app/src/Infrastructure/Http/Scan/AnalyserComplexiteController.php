<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Scan;

use App\Application\Audit\Command\AnalyserComplexite;
use App\Application\Audit\Command\AnalyserComplexiteHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Exception\TypeProjetIncompatible;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * POST /api/projets/{projetId}/analyse : lance l'analyse de complexité d'un
 * projet « Complexité PHP » et (re)génère ses constats (synchrone, phpx étant rapide).
 */
final readonly class AnalyserComplexiteController
{
    public function __construct(private AnalyserComplexiteHandler $analyser)
    {
    }

    #[Route('/api/projets/{projetId}/analyse', name: 'api_projets_analyse', methods: ['POST'])]
    public function __invoke(string $projetId): JsonResponse
    {
        try {
            $traites = ($this->analyser)(new AnalyserComplexite($projetId));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (TypeProjetIncompatible $e) {
            return new JsonResponse(['error' => $e->getMessage()], 409);
        }

        return new JsonResponse(['traites' => $traites]);
    }
}
