<?php

declare(strict_types=1);

namespace App\ApiResource\Referential;

final class CritereResource
{
    /**
     * @param list<ReferenceWcagResource> $wcag
     * @param list<string>                $techniques
     * @param list<TestResource>          $tests
     */
    public function __construct(
        public string $numero,
        public string $intitule,
        public array $wcag,
        public array $techniques,
        public array $tests,
    ) {
    }
}
