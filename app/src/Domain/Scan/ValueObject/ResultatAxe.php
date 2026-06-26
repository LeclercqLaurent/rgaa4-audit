<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Un résultat axe-core (une règle évaluée sur une page) avec ses occurrences.
 *
 * Les `tags` portent notamment les marqueurs `wcagXYZ` qui servent de pivot au
 * mapping axe → WCAG → RGAA (lot suivant).
 */
final readonly class ResultatAxe
{
    /**
     * @param list<string>    $tags
     * @param list<NoeudAxe>  $noeuds
     */
    public function __construct(
        public string $id,
        public ?string $impact,
        public string $description,
        public string $help,
        public string $helpUrl,
        public array $tags,
        public array $noeuds,
    ) {
    }
}
