<?php

declare(strict_types=1);

namespace App\ApiResource\Referential;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Referential\ThematiqueProvider;

#[ApiResource(
    shortName: 'Thematique',
    operations: [
        new GetCollection(paginationEnabled: false),
        new Get(),
    ],
    provider: ThematiqueProvider::class,
)]
final class ThematiqueResource
{
    /**
     * @param list<CritereResource> $criteres
     */
    public function __construct(
        #[ApiProperty(identifier: true)]
        public int $numero,
        public string $nom,
        public array $criteres,
    ) {
    }
}
