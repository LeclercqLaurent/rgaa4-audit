<?php

declare(strict_types=1);

namespace App\Application\Scan\Command;

use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\Port\ScanRepository;
use App\Domain\Shared\Port\IdGenerator;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class PlanifierScanHandler
{
    public function __construct(
        private ScanRepository $scans,
        private ObtenirProjetHandler $obtenirProjet,
        private IdGenerator $ids,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @return string Identifiant du scan planifié
     */
    public function __invoke(PlanifierScan $command): string
    {
        if (null === ($this->obtenirProjet)(new ObtenirProjet($command->projetId))) {
            throw ProjetIntrouvable::pour($command->projetId);
        }

        $scan = new Scan($this->ids->generate(), $command->projetId, new DateTimeImmutable());
        $this->scans->save($scan);
        $this->bus->dispatch(new LancerScan($scan->id()));

        return $scan->id();
    }
}
