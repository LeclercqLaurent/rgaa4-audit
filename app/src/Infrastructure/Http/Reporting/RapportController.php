<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Reporting;

use App\Application\Reporting\Query\ObtenirRapport;
use App\Application\Reporting\Query\ObtenirRapportHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Reporting\Port\RapportRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Rapport d'audit RGAA d'un projet, en HTML ou en PDF.
 */
final readonly class RapportController
{
    public function __construct(
        private ObtenirRapportHandler $obtenirRapport,
        private RapportRenderer $renderer,
    ) {
    }

    #[Route('/api/projets/{projetId}/rapport', name: 'api_projets_rapport_html', methods: ['GET'])]
    public function html(string $projetId): Response
    {
        try {
            $html = $this->renderer->html(($this->obtenirRapport)(new ObtenirRapport($projetId)));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    #[Route('/api/projets/{projetId}/rapport.pdf', name: 'api_projets_rapport_pdf', methods: ['GET'])]
    public function pdf(string $projetId): Response
    {
        try {
            $pdf = $this->renderer->pdf(($this->obtenirRapport)(new ObtenirRapport($projetId)));
        } catch (ProjetIntrouvable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport-rgaa.pdf"',
        ]);
    }
}
