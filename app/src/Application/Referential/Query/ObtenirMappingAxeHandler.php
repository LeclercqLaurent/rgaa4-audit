<?php

declare(strict_types=1);

namespace App\Application\Referential\Query;

use App\Domain\Referential\Port\MappingRgaaRepository;

final readonly class ObtenirMappingAxeHandler
{
    public function __construct(private MappingRgaaRepository $mapping)
    {
    }

    /**
     * @return array<string, list<string>>
     */
    public function __invoke(ObtenirMappingAxe $query): array
    {
        return $this->mapping->associations();
    }
}
