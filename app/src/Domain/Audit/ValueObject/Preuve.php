<?php

declare(strict_types=1);

namespace App\Domain\Audit\ValueObject;

/**
 * Preuve d'un constat automatique : occurrence axe-core ayant motivé le statut
 * (règle, impact, élément ciblé). Sert de justification dans le rapport.
 */
final readonly class Preuve
{
    public function __construct(
        public string $regle,
        public ?string $impact,
        public string $cible,
        public string $extraitHtml,
        public ?string $resume,
        public string $aide,
    ) {
    }
}
