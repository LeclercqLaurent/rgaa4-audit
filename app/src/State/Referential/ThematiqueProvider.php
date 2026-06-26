<?php

declare(strict_types=1);

namespace App\State\Referential;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Referential\CritereResource;
use App\ApiResource\Referential\ReferenceWcagResource;
use App\ApiResource\Referential\TestResource;
use App\ApiResource\Referential\ThematiqueResource;
use App\Application\Referential\Query\ListerThematiques;
use App\Application\Referential\Query\ListerThematiquesHandler;
use App\Domain\Referential\Entity\Critere;
use App\Domain\Referential\Entity\Test;
use App\Domain\Referential\Entity\Thematique;
use App\Domain\Referential\ValueObject\ReferenceWcag;

/**
 * @implements ProviderInterface<ThematiqueResource>
 */
final readonly class ThematiqueProvider implements ProviderInterface
{
    public function __construct(private ListerThematiquesHandler $lister)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $thematiques = ($this->lister)(new ListerThematiques());

        if ($operation instanceof CollectionOperationInterface) {
            return array_map($this->toResource(...), $thematiques);
        }

        $rawNumero = $uriVariables['numero'] ?? 0;
        $numero = is_numeric($rawNumero) ? (int) $rawNumero : 0;
        foreach ($thematiques as $thematique) {
            if ($thematique->numero === $numero) {
                return $this->toResource($thematique);
            }
        }

        return null;
    }

    private function toResource(Thematique $thematique): ThematiqueResource
    {
        return new ThematiqueResource(
            $thematique->numero,
            $thematique->nom,
            array_map($this->critereToResource(...), $thematique->criteres),
        );
    }

    private function critereToResource(Critere $critere): CritereResource
    {
        return new CritereResource(
            $critere->numero,
            $critere->intitule,
            array_map(
                static fn (ReferenceWcag $r): ReferenceWcagResource => new ReferenceWcagResource($r->successCriterion, $r->intitule, $r->niveau->value, $r->axeTag),
                $critere->referencesWcag,
            ),
            $critere->techniques,
            array_map(
                static fn (Test $t): TestResource => new TestResource($t->numero, $t->enonces),
                $critere->tests,
            ),
        );
    }
}
