<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entity;

use App\Domain\Audit\ValueObject\Url;
use DateTimeImmutable;

/**
 * Projet d'audit RGAA : racine d'agrégat regroupant l'échantillon de pages.
 */
final class Projet
{
    /**
     * @param list<Page> $pages
     */
    public function __construct(
        private readonly string $id,
        private string $nom,
        private string $client,
        private Url $urlReference,
        private readonly DateTimeImmutable $dateCreation,
        private array $pages = [],
    ) {
    }

    public function ajouterPage(Page $page): void
    {
        $this->pages[] = $page;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function nom(): string
    {
        return $this->nom;
    }

    public function client(): string
    {
        return $this->client;
    }

    public function urlReference(): Url
    {
        return $this->urlReference;
    }

    public function dateCreation(): DateTimeImmutable
    {
        return $this->dateCreation;
    }

    /**
     * @return list<Page>
     */
    public function pages(): array
    {
        return $this->pages;
    }
}
