<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Query\ObtenirTaux;
use App\Application\Audit\Query\ObtenirTauxHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * GET /api/projets/{projetId}/taux : taux de conformité RGAA (global + par thématique).
 */
final readonly class TauxController
{
    public function __construct(private ObtenirTauxHandler $obtenirTaux)
    {
    }

    #[Route('/api/projets/{projetId}/taux', name: 'api_projets_taux', methods: ['GET'])]
    public function __invoke(string $projetId): JsonResponse
    {
        $taux = ($this->obtenirTaux)(new ObtenirTaux($projetId));

        return new JsonResponse([
            'global' => $taux->global,
            'parThematique' => $taux->parThematique,
            'conformes' => $taux->conformes,
            'nonConformes' => $taux->nonConformes,
            'nonApplicables' => $taux->nonApplicables,
            'nonTestes' => $taux->nonTestes,
        ]);
    }
}
