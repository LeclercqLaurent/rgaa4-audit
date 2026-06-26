<?php

declare(strict_types=1);

namespace App\Application\Scan\Command;

use App\Application\Audit\Command\GenererConstats;
use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Domain\Scan\Port\PageScanner;
use App\Domain\Scan\Port\ScanRepository;
use App\Domain\Scan\ValueObject\ResultatPage;
use App\Domain\Scan\ValueObject\StatutScan;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

/**
 * Exécute un scan : récupère l'échantillon du projet, analyse chaque page via le
 * PageScanner, agrège les résultats et clôt le scan (terminé ou échoué).
 */
#[AsMessageHandler]
final readonly class LancerScanHandler
{
    public function __construct(
        private ScanRepository $scans,
        private ObtenirProjetHandler $obtenirProjet,
        private PageScanner $scanner,
        private MessageBusInterface $bus,
    ) {
    }

    public function __invoke(LancerScan $message): void
    {
        $scan = $this->scans->get($message->scanId);

        if (null === $scan) {
            return;
        }

        $maintenant = new DateTimeImmutable();
        $projet = ($this->obtenirProjet)(new ObtenirProjet($scan->projetId()));
        $scan->demarrer();

        try {
            if (null === $projet) {
                throw new RuntimeException('Projet introuvable au lancement du scan.');
            }

            foreach ($projet->pages() as $page) {
                $url = (string) $page->url;
                $scan->ajouterResultat(new ResultatPage($url, $this->scanner->scan($url)));
            }

            $scan->terminer($maintenant);
        } catch (Throwable $erreur) {
            $scan->echouer($erreur->getMessage(), $maintenant);
        }

        $this->scans->save($scan);

        if (StatutScan::Termine === $scan->statut()) {
            $this->bus->dispatch(new GenererConstats($scan->projetId()));
        }
    }
}
