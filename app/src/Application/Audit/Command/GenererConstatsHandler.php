<?php

declare(strict_types=1);

namespace App\Application\Audit\Command;

use App\Application\Audit\Service\GenerateurConstatsAuto;
use App\Application\Referential\Query\ObtenirMappingAxe;
use App\Application\Referential\Query\ObtenirMappingAxeHandler;
use App\Application\Scan\Query\ListerScansProjet;
use App\Application\Scan\Query\ListerScansProjetHandler;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Scan\Entity\Scan;
use App\Domain\Scan\ValueObject\StatutScan;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Régénère les constats automatiques d'un projet depuis son dernier scan terminé.
 */
#[AsMessageHandler]
final readonly class GenererConstatsHandler
{
    public function __construct(
        private ListerScansProjetHandler $scans,
        private ObtenirMappingAxeHandler $mapping,
        private GenerateurConstatsAuto $generateur,
        private ConstatRepository $constats,
    ) {
    }

    public function __invoke(GenererConstats $message): void
    {
        $scan = $this->dernierScanTermine($message->projetId);

        if (null === $scan) {
            return;
        }

        $mapping = ($this->mapping)(new ObtenirMappingAxe());
        $constats = $this->generateur->pour($message->projetId, $scan->resultats(), $mapping);
        $this->constats->remplacerAuto($message->projetId, Referentiel::Rgaa, $constats);
    }

    private function dernierScanTermine(string $projetId): ?Scan
    {
        foreach (($this->scans)(new ListerScansProjet($projetId)) as $scan) {
            if (StatutScan::Termine === $scan->statut()) {
                return $scan;
            }
        }

        return null;
    }
}
