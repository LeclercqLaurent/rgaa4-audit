<?php

declare(strict_types=1);

namespace App\ApiResource\Referential;

final class TestResource
{
    /**
     * @param list<string> $enonces
     */
    public function __construct(
        public string $numero,
        public array $enonces,
    ) {
    }
}
