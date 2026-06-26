<?php

declare(strict_types=1);

namespace App\State\Audit;

use App\ApiResource\Audit\PageResource;
use App\ApiResource\Audit\ProjetResource;
use App\Domain\Audit\Entity\Page;
use App\Domain\Audit\Entity\Projet;
use DateTimeInterface;

/**
 * Mappe l'agrégat Domain Projet vers son DTO d'API (partagé provider/processors).
 */
final readonly class ProjetResourceMapper
{
    public function toResource(Projet $projet): ProjetResource
    {
        return new ProjetResource(
            $projet->id(),
            $projet->nom(),
            $projet->client(),
            (string) $projet->urlReference(),
            $projet->dateCreation()->format(DateTimeInterface::ATOM),
            array_map($this->pageToResource(...), $projet->pages()),
        );
    }

    public function pageToResource(Page $page): PageResource
    {
        return new PageResource($page->id, (string) $page->url, $page->titre);
    }
}
