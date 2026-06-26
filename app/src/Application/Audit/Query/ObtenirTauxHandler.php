<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\Service\ResolveurConstatsEffectifs;
use App\Domain\Audit\ValueObject\TauxConformite;

final readonly class ObtenirTauxHandler
{
    public function __construct(
        private ConstatRepository $constats,
        private ResolveurConstatsEffectifs $resolveur,
        private CalculateurTaux $calculateur,
    ) {
    }

    public function __invoke(ObtenirTaux $query): TauxConformite
    {
        $effectifs = $this->resolveur->resoudre($this->constats->findByProjet($query->projetId));

        return $this->calculateur->calculer($effectifs);
    }
}
