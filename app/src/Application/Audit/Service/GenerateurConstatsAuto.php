<?php

declare(strict_types=1);

namespace App\Application\Audit\Service;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Scan\ValueObject\NoeudAxe;
use App\Domain\Scan\ValueObject\ResultatAxe;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\ScanResult;

/**
 * Couche unique du mapping axe-core → WCAG → RGAA : transforme les résultats axe
 * d'un scan en constats automatiques par (page, critère).
 *
 * Règle : une violation mappée rend le critère « non conforme » (preuves à
 * l'appui) ; à défaut, un succès mappé le rend « conforme » ; les critères sans
 * signal automatique restent implicitement « non testés ».
 */
final readonly class GenerateurConstatsAuto
{
    /**
     * @param list<ResultatPage>          $pages
     * @param array<string, list<string>> $mapping tag axe → critères RGAA
     *
     * @return list<Constat>
     */
    public function pour(string $projetId, array $pages, array $mapping): array
    {
        $constats = [];
        foreach ($pages as $page) {
            $constats = [...$constats, ...$this->pourPage($projetId, $page, $mapping)];
        }

        return $constats;
    }

    /**
     * @param array<string, list<string>> $mapping
     *
     * @return list<Constat>
     */
    private function pourPage(string $projetId, ResultatPage $page, array $mapping): array
    {
        $preuvesParCritere = $this->preuvesParCritere($page->resultat, $mapping);
        $criteresConformes = $this->criteresPasses($page->resultat, $mapping);
        $criteres = array_values(array_unique([...array_keys($preuvesParCritere), ...$criteresConformes]));

        return array_map(
            fn (string $critere): Constat => new Constat(
                $projetId,
                $page->url,
                $critere,
                isset($preuvesParCritere[$critere]) ? StatutConformite::NonConforme : StatutConformite::Conforme,
                SourceConstat::Auto,
                $preuvesParCritere[$critere] ?? [],
            ),
            $criteres,
        );
    }

    /**
     * @param array<string, list<string>> $mapping
     *
     * @return array<string, list<Preuve>>
     */
    private function preuvesParCritere(ScanResult $resultat, array $mapping): array
    {
        $parCritere = [];
        foreach ($resultat->violations as $violation) {
            $preuves = $this->preuves($violation);
            foreach ($this->criteresPourTags($violation->tags, $mapping) as $critere) {
                $parCritere[$critere] = [...($parCritere[$critere] ?? []), ...$preuves];
            }
        }

        return $parCritere;
    }

    /**
     * @param array<string, list<string>> $mapping
     *
     * @return list<string>
     */
    private function criteresPasses(ScanResult $resultat, array $mapping): array
    {
        $criteres = [];
        foreach ($resultat->passes as $passe) {
            foreach ($this->criteresPourTags($passe->tags, $mapping) as $critere) {
                $criteres[$critere] = true;
            }
        }

        return array_keys($criteres);
    }

    /**
     * @param list<string>                $tags
     * @param array<string, list<string>> $mapping
     *
     * @return list<string>
     */
    private function criteresPourTags(array $tags, array $mapping): array
    {
        $criteres = [];
        foreach ($tags as $tag) {
            foreach ($mapping[$tag] ?? [] as $critere) {
                $criteres[$critere] = true;
            }
        }

        return array_keys($criteres);
    }

    /**
     * @return list<Preuve>
     */
    private function preuves(ResultatAxe $violation): array
    {
        if ([] === $violation->noeuds) {
            return [new Preuve($violation->id, $violation->impact, '', '', null, $violation->helpUrl)];
        }

        return array_map(
            fn (NoeudAxe $noeud): Preuve => new Preuve(
                $violation->id,
                $violation->impact,
                implode(' ', $noeud->cibles),
                $noeud->html,
                $noeud->resumeEchec,
                $violation->helpUrl,
            ),
            $violation->noeuds,
        );
    }
}
