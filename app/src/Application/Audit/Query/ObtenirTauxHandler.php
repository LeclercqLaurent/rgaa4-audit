<?php

declare(strict_types=1);

namespace App\Application\Audit\Query;

use App\Application\Audit\Service\CriteresAttendus;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\Service\CalculateurTaux;
use App\Domain\Audit\Service\ResolveurConstatsEffectifs;
use App\Domain\Audit\ValueObject\TauxConformite;

final readonly class ObtenirTauxHandler
{
    public function __construct(
        private ObtenirProjetHandler $obtenirProjet,
        private ConstatRepository $constats,
        private ResolveurConstatsEffectifs $resolveur,
        private CalculateurTaux $calculateur,
        private CriteresAttendus $criteresAttendus,
    ) {
    }

    /**
     * Le projet est chargé pour son référentiel : le taux ne peut pas dire ce
     * qui n'a pas été évalué sans savoir ce qui devait l'être.
     */
    public function __invoke(ObtenirTaux $query): TauxConformite
    {
        $projet = ($this->obtenirProjet)(new ObtenirProjet($query->projetId));

        if (null === $projet) {
            throw ProjetIntrouvable::pour($query->projetId);
        }

        $effectifs = $this->resolveur->resoudre($this->constats->findByProjet($query->projetId));

        return $this->calculateur->calculer($effectifs, $this->criteresAttendus->pour($projet->type()));
    }
}
