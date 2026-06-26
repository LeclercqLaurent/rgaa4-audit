<?php

declare(strict_types=1);

namespace App\Domain\Audit\Entity;

use App\Domain\Audit\ValueObject\Url;

/**
 * Page de l'échantillon d'audit (entité enfant de l'agrégat Projet).
 */
final readonly class Page
{
    public function __construct(
        public string $id,
        public Url $url,
        public string $titre,
    ) {
    }
}
