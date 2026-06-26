<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Adapter;

use App\Domain\Audit\Entity\Constat;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\ValueObject\Preuve;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Audit\ValueObject\SourceConstat;
use App\Domain\Audit\ValueObject\StatutConformite;
use App\Domain\Shared\Port\IdGenerator;
use App\Infrastructure\Persistence\Entity\ConstatEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineConstatRepository implements ConstatRepository
{
    public function __construct(
        private EntityManagerInterface $em,
        private IdGenerator $ids,
    ) {
    }

    public function remplacerAuto(string $projetId, Referentiel $referentiel, array $constats): void
    {
        $this->em->createQuery(
            sprintf('DELETE FROM %s c WHERE c.projetId = :projet AND c.referentiel = :referentiel AND c.source = :source', ConstatEntity::class),
        )
            ->setParameter('projet', $projetId)
            ->setParameter('referentiel', $referentiel->value)
            ->setParameter('source', SourceConstat::Auto->value)
            ->execute();

        foreach ($constats as $constat) {
            $this->em->persist($this->toEntity($constat));
        }

        $this->em->flush();
    }

    public function enregistrerManuel(Constat $constat): void
    {
        $this->em->createQuery(
            sprintf('DELETE FROM %s c WHERE c.projetId = :projet AND c.referentiel = :referentiel AND c.uniteAuditee = :unite AND c.critereNumero = :critere AND c.source = :source', ConstatEntity::class),
        )
            ->setParameter('projet', $constat->projetId())
            ->setParameter('referentiel', $constat->referentiel()->value)
            ->setParameter('unite', $constat->uniteAuditee())
            ->setParameter('critere', $constat->critereNumero())
            ->setParameter('source', SourceConstat::Manuel->value)
            ->execute();

        $this->em->persist($this->toEntity($constat));
        $this->em->flush();
    }

    public function findByProjet(string $projetId): array
    {
        $entities = $this->em->getRepository(ConstatEntity::class)->findBy(['projetId' => $projetId]);

        return array_map($this->toDomain(...), $entities);
    }

    public function supprimerPourProjet(string $projetId): void
    {
        $this->em->createQuery(sprintf('DELETE FROM %s c WHERE c.projetId = :projet', ConstatEntity::class))
            ->setParameter('projet', $projetId)
            ->execute();
    }

    private function toEntity(Constat $constat): ConstatEntity
    {
        $entity = new ConstatEntity(
            $this->ids->generate(),
            $constat->projetId(),
            $constat->referentiel()->value,
            $constat->uniteAuditee(),
            $constat->critereNumero(),
            $constat->statut()->value,
            $constat->source()->value,
        );
        $entity->setPreuves(array_map($this->preuveToArray(...), $constat->preuves()));
        $entity->setCommentaire($constat->commentaire());

        return $entity;
    }

    private function toDomain(ConstatEntity $entity): Constat
    {
        return (new Constat(
            $entity->getProjetId(),
            Referentiel::from($entity->getReferentiel()),
            $entity->getUniteAuditee(),
            $entity->getCritereNumero(),
            StatutConformite::from($entity->getStatut()),
            SourceConstat::from($entity->getSource()),
            array_map($this->preuveFromArray(...), $entity->getPreuves()),
        ))->avecCommentaire($entity->getCommentaire());
    }

    /**
     * @return array<string, mixed>
     */
    private function preuveToArray(Preuve $preuve): array
    {
        return [
            'regle' => $preuve->regle,
            'impact' => $preuve->impact,
            'cible' => $preuve->cible,
            'extraitHtml' => $preuve->extraitHtml,
            'resume' => $preuve->resume,
            'aide' => $preuve->aide,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function preuveFromArray(array $data): Preuve
    {
        return new Preuve(
            $this->str($data['regle'] ?? null),
            $this->nullableStr($data['impact'] ?? null),
            $this->str($data['cible'] ?? null),
            $this->str($data['extraitHtml'] ?? null),
            $this->nullableStr($data['resume'] ?? null),
            $this->str($data['aide'] ?? null),
        );
    }

    private function str(mixed $v): string
    {
        return is_string($v) ? $v : '';
    }

    private function nullableStr(mixed $v): ?string
    {
        return is_string($v) ? $v : null;
    }
}
