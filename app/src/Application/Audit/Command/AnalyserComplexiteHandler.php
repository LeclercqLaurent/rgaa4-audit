<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Application\Audit\Service\GenerateurConstatsComplexite;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Exception\TypeProjetIncompatible;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Scan\Port\AnalyseurCode;

/**
 * Analyse la complexité du code d'un projet (cible = chemin) et (re)génère ses
 * constats « Complexité PHP ». Utilisé par l'API et la commande console.
 */
final readonly class AnalyserComplexiteHandler
{
    public function __construct(
        private ObtenirProjetHandler $obtenirProjet,
        private AnalyseurCode $analyseur,
        private GenerateurConstatsComplexite $generateur,
        private ConstatRepository $constats,
    ) {
    }

    /**
     * @return int Nombre de constats générés
     */
    public function __invoke(AnalyserComplexite $command): int
    {
        $projet = ($this->obtenirProjet)(new ObtenirProjet($command->projetId));

        if (null === $projet) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        if (Referentiel::ComplexitePhp !== $projet->type()) {
            throw TypeProjetIncompatible::pour(Referentiel::ComplexitePhp->libelle());
        }

        $resultat = $this->analyseur->analyser($projet->cible());
        $constats = $this->generateur->pour($projet->id(), $resultat);
        $this->constats->remplacerAuto($projet->id(), Referentiel::ComplexitePhp, $constats);

        return count($constats);
    }
}
