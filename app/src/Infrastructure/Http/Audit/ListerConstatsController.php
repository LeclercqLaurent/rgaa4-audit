<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Audit;

use App\Application\Audit\Query\ListerConstats;
use App\Application\Audit\Query\ListerConstatsHandler;
use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\Preuve;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * GET /api/projets/{projetId}/constats : constats de conformité d'un projet.
 *
 * Exposé via un contrôleur (JSON simple) plutôt qu'une ressource API Platform :
 * les constats sont une projection de lecture sans route item individuelle.
 */
final readonly class ListerConstatsController
{
    public function __construct(private ListerConstatsHandler $lister)
    {
    }

    #[Route('/api/projets/{projetId}/constats', name: 'api_projets_constats', methods: ['GET'])]
    public function __invoke(string $projetId): JsonResponse
    {
        $constats = array_map($this->toArray(...), ($this->lister)(new ListerConstats($projetId)));

        return new JsonResponse($constats);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(Constat $constat): array
    {
        return [
            'id' => sha1($constat->pageUrl()).'-'.$constat->critereNumero(),
            'projetId' => $constat->projetId(),
            'pageUrl' => $constat->pageUrl(),
            'critereNumero' => $constat->critereNumero(),
            'statut' => $constat->statut()->value,
            'source' => $constat->source()->value,
            'commentaire' => $constat->commentaire(),
            'preuves' => array_map($this->preuveToArray(...), $constat->preuves()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function preuveToArray(Preuve $preuve): array
    {
        return [
            'regle' => $preuve->regle,
            'impact' => $preuve->impact,
            'cible' => $preuve->cible,
            'extraitHtml' => $preuve->extraitHtml,
            'resume' => $preuve->resume,
            'aide' => $preuve->aide,
        ];
    }
}
