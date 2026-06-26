<?php

declare(strict_types=1);

namespace App\ApiResource\Referential;

final class ReferenceWcagResource
{
    public function __construct(
        public string $sc,
        public string $intitule,
        public string $niveau,
        public string $axeTag,
    ) {
    }
}
