<?php

declare(strict_types=1);

namespace App\Domain\Scan\ValueObject;

/**
 * Élément du DOM concerné par un résultat axe (sert de preuve dans les constats).
 */
final readonly class NoeudAxe
{
    /**
     * @param list<string> $cibles Sélecteurs CSS localisant l'élément
     */
    public function __construct(
        public array $cibles,
        public string $html,
        public ?string $resumeEchec,
    ) {
    }
}
