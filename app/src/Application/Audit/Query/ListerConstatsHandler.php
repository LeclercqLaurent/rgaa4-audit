<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Port\ConstatRepository;

final readonly class ListerConstatsHandler
{
    public function __construct(private ConstatRepository $constats)
    {
    }

    /**
     * @return list<Constat>
     */
    public function __invoke(ListerConstats $query): array
    {
        return $this->constats->findByProjet($query->projetId);
    }
}
