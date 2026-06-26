<?php

declare(strict_types=1);

namespace App\State\Scan;

use App\ApiResource\Scan\ScanPageResumeResource;
use App\ApiResource\Scan\ScanResource;
use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\ValueObject\ResultatPage;
use DateTimeInterface;

/**
 * Mappe l'agrégat Scan vers son DTO d'API (statut + résumé chiffré par page).
 */
final readonly class ScanResourceMapper
{
    public function toResource(Scan $scan): ScanResource
    {
        return new ScanResource(
            $scan->id(),
            $scan->projetId(),
            $scan->statut()->value,
            $scan->dateCreation()->format(DateTimeInterface::ATOM),
            $scan->dateFin()?->format(DateTimeInterface::ATOM),
            $scan->erreur(),
            array_map($this->resumePage(...), $scan->resultats()),
        );
    }

    private function resumePage(ResultatPage $page): ScanPageResumeResource
    {
        $resultat = $page->resultat;

        return new ScanPageResumeResource(
            $page->url,
            count($resultat->violations),
            count($resultat->passes),
            count($resultat->incomplete),
            count($resultat->inapplicable),
        );
    }
}
