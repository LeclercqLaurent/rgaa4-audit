<?php

declare(strict_types=1);

namespace App\Application\Referential\Query;

use App\Domain\Referential\Entity\Thematique;
use App\Domain\Referential\Port\ThematiqueRepository;

final readonly class ListerThematiquesHandler
{
    public function __construct(private ThematiqueRepository $thematiques)
    {
    }

    /**
     * @return list<Thematique>
     */
    public function __invoke(ListerThematiques $query): array
    {
        return $this->thematiques->findAll();
    }
}
