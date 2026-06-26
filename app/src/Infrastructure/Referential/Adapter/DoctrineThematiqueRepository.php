<?php

declare(strict_types=1);

namespace App\Infrastructure\Referential\Adapter;

use App\Domain\Referential\Entity\Critere;
use App\Domain\Referential\Entity\Test;
use App\Domain\Referential\Entity\Thematique;
use App\Domain\Referential\Port\ThematiqueRepository;
use App\Domain\Referential\ValueObject\NiveauWcag;
use App\Domain\Referential\ValueObject\ReferenceWcag;
use App\Infrastructure\Persistence\Entity\CritereEntity;
use App\Infrastructure\Persistence\Entity\TestEntity;
use App\Infrastructure\Persistence\Entity\ThematiqueEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineThematiqueRepository implements ThematiqueRepository
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findAll(): array
    {
        $entities = $this->em->getRepository(ThematiqueEntity::class)->findBy([], ['numero' => 'ASC']);

        return array_map($this->toDomain(...), $entities);
    }

    public function get(int $numero): ?Thematique
    {
        $entity = $this->em->getRepository(ThematiqueEntity::class)->find($numero);

        return $entity instanceof ThematiqueEntity ? $this->toDomain($entity) : null;
    }

    private function toDomain(ThematiqueEntity $entity): Thematique
    {
        $criteres = $entity->getCriteres()->toArray();
        usort($criteres, static fn (CritereEntity $a, CritereEntity $b): int => self::compareNumero($a->getNumero(), $b->getNumero()));

        return new Thematique(
            $entity->getNumero(),
            $entity->getNom(),
            array_map($this->critereToDomain(...), $criteres),
        );
    }

    private function critereToDomain(CritereEntity $entity): Critere
    {
        $tests = $entity->getTests()->toArray();
        usort($tests, static fn (TestEntity $a, TestEntity $b): int => self::compareNumero($a->getNumero(), $b->getNumero()));

        return new Critere(
            $entity->getNumero(),
            $entity->getIntitule(),
            array_map($this->referenceToDomain(...), $entity->getWcag()),
            $entity->getTechniques(),
            array_map(static fn (TestEntity $t): Test => new Test($t->getNumero(), $t->getEnonces()), $tests),
        );
    }

    /**
     * @param array{sc: string, intitule: string, niveau: string, axe_tag: string} $reference
     */
    private function referenceToDomain(array $reference): ReferenceWcag
    {
        return new ReferenceWcag(
            $reference['sc'],
            $reference['intitule'],
            NiveauWcag::from($reference['niveau']),
            $reference['axe_tag'],
        );
    }

    /**
     * Comparaison « naturelle » de numéros pointés (ex. 1.2 < 1.10).
     */
    private static function compareNumero(string $a, string $b): int
    {
        return array_map(intval(...), explode('.', $a)) <=> array_map(intval(...), explode('.', $b));
    }
}
