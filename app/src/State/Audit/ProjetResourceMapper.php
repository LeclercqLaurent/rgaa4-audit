<?php

declare(strict_types=1);

namespace App\State\Audit;

use App\ApiResource\Audit\DernierScanResource;
use App\ApiResource\Audit\PageResource;
use App\ApiResource\Audit\ProjetResource;
use App\Domain\Audit\Entity\Page;
use App\Domain\Audit\Entity\Projet;
use App\Domain\Scan\ValueObject\ResumeScan;
use DateTimeInterface;

/**
 * Mappe l'agrégat Domain Projet vers son DTO d'API (partagé provider/processors).
 */
final readonly class ProjetResourceMapper
{
    public function toResource(Projet $projet, ?ResumeScan $dernierScan = null): ProjetResource
    {
        return new ProjetResource(
            $projet->id(),
            $projet->nom(),
            $projet->client(),
            (string) $projet->urlReference(),
            $projet->dateCreation()->format(DateTimeInterface::ATOM),
            array_map($this->pageToResource(...), $projet->pages()),
            null === $dernierScan ? null : new DernierScanResource(
                $dernierScan->statut->value,
                $dernierScan->dateCreation->format(DateTimeInterface::ATOM),
            ),
        );
    }

    public function pageToResource(Page $page): PageResource
    {
        return new PageResource($page->id, (string) $page->url, $page->titre);
    }
}
