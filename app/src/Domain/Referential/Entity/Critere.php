<?php

declare(strict_types=1);

namespace App\Domain\Referential\Entity;

use App\Domain\Referential\ValueObject\ReferenceWcag;

/**
 * Critère RGAA (ex. 1.1), rattaché à une thématique.
 */
final readonly class Critere
{
    /**
     * @param list<ReferenceWcag> $referencesWcag
     * @param list<string>        $techniques
     * @param list<Test>          $tests
     */
    public function __construct(
        public string $numero,
        public string $intitule,
        public array $referencesWcag,
        public array $techniques,
        public array $tests,
    ) {
    }
}
