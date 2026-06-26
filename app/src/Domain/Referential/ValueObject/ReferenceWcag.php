<?php

declare(strict_types=1);

namespace App\Domain\Referential\ValueObject;

/**
 * Référence d'un critère RGAA vers un critère de succès (SC) WCAG, avec le tag
 * axe-core correspondant (pivot du mapping axe-core → WCAG → RGAA).
 */
final readonly class ReferenceWcag
{
    public function __construct(
        public string $successCriterion,
        public string $intitule,
        public NiveauWcag $niveau,
        public string $axeTag,
    ) {
    }
}
