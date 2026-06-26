<?php

declare(strict_types=1);

namespace App\ApiResource\Audit;

/**
 * DTO d'une page de l'échantillon, embarqué dans ProjetResource (pas une ressource autonome).
 */
final class PageResource
{
    public function __construct(
        public string $id,
        public string $url,
        public string $titre,
    ) {
    }
}
