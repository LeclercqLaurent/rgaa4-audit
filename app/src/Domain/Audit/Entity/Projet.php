<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entity;

use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\Url;
use DateTimeImmutable;

/**
 * Projet d'audit : racine d'agrégat. Son type (RGAA 4 ou Complexité PHP) est
 * choisi à la création et détermine le moteur ; la cible est l'URL du site
 * (RGAA, avec un échantillon de pages) ou le chemin du code (Complexité).
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
        private readonly Referentiel $type,
        private string $cible,
        private readonly DateTimeImmutable $dateCreation,
        private array $pages = [],
    ) {
    }

    public function ajouterPage(Page $page): void
    {
        $this->pages[] = $page;
    }

    public function modifier(string $nom, string $client, string $cible): void
    {
        $this->nom = $nom;
        $this->client = $client;
        $this->cible = $cible;
    }

    public function supprimerPage(string $pageId): void
    {
        $this->pages = array_values(array_filter($this->pages, static fn (Page $p): bool => $p->id !== $pageId));
    }

    public function modifierPage(string $pageId, Url $url, string $titre): void
    {
        $this->pages = array_map(
            static fn (Page $p): Page => $p->id === $pageId ? new Page($pageId, $url, $titre) : $p,
            $this->pages,
        );
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

    public function type(): Referentiel
    {
        return $this->type;
    }

    public function cible(): string
    {
        return $this->cible;
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
