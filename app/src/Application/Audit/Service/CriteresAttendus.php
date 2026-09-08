<?php

declare(strict_types=1);

namespace App\Application\Audit\Service;

use App\Application\Referential\Query\ListerThematiques;
use App\Application\Referential\Query\ListerThematiquesHandler;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Referential\Entity\Critere;
use App\Domain\Referential\Entity\Thematique;

/**
 * Liste les critères qu'un référentiel impose d'évaluer.
 *
 * Le calcul du taux a besoin de savoir ce qui aurait dû être regardé, et pas
 * seulement ce qui l'a été. Cette information vit dans le contexte Referential ;
 * c'est ici, dans l'Application, que les deux contextes se rencontrent, sans que
 * le domaine Audit ait à connaître le référentiel.
 *
 * Le RGAA a une liste fermée de 106 critères. L'audit de complexité PHP n'en a
 * pas : ses lentilles ne s'appliquent qu'au code réellement analysé, et rien n'y
 * manque par construction.
 */
final readonly class CriteresAttendus
{
    public function __construct(
        private ListerThematiquesHandler $listerThematiques,
    ) {
    }

    /**
     * @return list<string>
     */
    public function pour(Referentiel $referentiel): array
    {
        if (Referentiel::Rgaa !== $referentiel) {
            return [];
        }

        return $this->numeros(($this->listerThematiques)(new ListerThematiques()));
    }

    /**
     * @param list<Thematique> $thematiques
     *
     * @return list<string>
     */
    private function numeros(array $thematiques): array
    {
        $numeros = [];
        foreach ($thematiques as $thematique) {
            $numeros = [...$numeros, ...array_map(
                static fn (Critere $critere): string => $critere->numero,
                $thematique->criteres,
            )];
        }

        return $numeros;
    }
}
