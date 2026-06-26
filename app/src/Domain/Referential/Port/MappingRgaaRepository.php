<?php

declare(strict_types=1);

namespace App\Domain\Referential\Port;

interface MappingRgaaRepository
{
    /**
     * Associations axe-core → RGAA : tag axe (`wcagXYZ`) → liste de numéros de
     * critères RGAA concernés.
     *
     * @return array<string, list<string>>
     */
    public function associations(): array;
}
