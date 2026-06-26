<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Scan;

use App\Infrastructure\Scan\Messenger\ConsommateurAsync;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Traite à la demande les messages en attente sur le transport « async »
 * (scans, génération de constats), puis s'arrête dès que la file est vide.
 *
 * Pratique en dev pour déclencher le traitement sans worker permanent.
 */
final readonly class ConsommerScansController
{
    public function __construct(private ConsommateurAsync $consommateur)
    {
    }

    #[Route('/api/scans/consommer', name: 'api_scans_consommer', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['traites' => $this->consommateur->consommer()]);
    }
}
