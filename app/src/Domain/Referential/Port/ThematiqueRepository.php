<?php

declare(strict_types=1);

namespace App\Domain\Referential\Port;

use App\Domain\Referential\Entity\Thematique;

interface ThematiqueRepository
{
    /**
     * @return list<Thematique> Toutes les thématiques, triées par numéro, avec leurs critères et tests.
     */
    public function findAll(): array;

    public function get(int $numero): ?Thematique;
}
