<?php

declare(strict_types=1);

namespace App\Domain\Reporting\ValueObject;

use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Audit\ValueObject\TauxConformite;

/**
 * Rapport d'audit RGAA assemblé : métadonnées du projet, taux de conformité,
 * sections par thématique et, dérivé, le plan d'action (non-conformités).
 */
final readonly class Rapport
{
    /**
     * @param list<SectionThematique> $sections
     */
    public function __construct(
        public string $projetNom,
        public string $client,
        public string $urlReference,
        public string $dateGeneration,
        public TauxConformite $taux,
        public array $sections,
    ) {
    }

    /**
     * Plan d'action : toutes les lignes non conformes, toutes thématiques confondues.
     *
     * @return list<LigneRapport>
     */
    public function planAction(): array
    {
        $lignes = [];
        foreach ($this->sections as $section) {
            foreach ($section->lignes as $ligne) {
                if (StatutConformite::NonConforme === $ligne->statut) {
                    $lignes[] = $ligne;
                }
            }
        }

        return $lignes;
    }
}
