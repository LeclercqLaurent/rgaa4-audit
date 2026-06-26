<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Command;

use App\Application\Audit\Query\ObtenirProjet;
use App\Application\Audit\Query\ObtenirProjetHandler;
use App\Application\Audit\Service\GenerateurConstatsComplexite;
use App\Domain\Audit\Port\ConstatRepository;
use App\Domain\Audit\ValueObject\Referentiel;
use App\Domain\Scan\Port\AnalyseurCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 2ᵉ moteur (Phase B) : audite la complexité d'un dossier de code via
 * phpx-complexity et alimente les constats d'un projet — qui sont ensuite lus
 * par le taux et le rapport, comme pour un audit RGAA.
 */
#[AsCommand(name: 'app:complexite:auditer', description: 'Audite la complexité PHP d\'un dossier et alimente les constats d\'un projet.')]
final class AuditerComplexiteCommand extends Command
{
    public function __construct(
        private readonly AnalyseurCode $analyseur,
        private readonly GenerateurConstatsComplexite $generateur,
        private readonly ConstatRepository $constats,
        private readonly ObtenirProjetHandler $obtenirProjet,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('projetId', InputArgument::REQUIRED, 'Identifiant du projet à alimenter')
            ->addArgument('chemin', InputArgument::REQUIRED, 'Dossier de code à analyser');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projetIdBrut = $input->getArgument('projetId');
        $cheminBrut = $input->getArgument('chemin');
        $projetId = is_string($projetIdBrut) ? $projetIdBrut : '';
        $chemin = is_string($cheminBrut) ? $cheminBrut : '';

        if (null === ($this->obtenirProjet)(new ObtenirProjet($projetId))) {
            $io->error(sprintf('Projet introuvable : %s', $projetId));

            return Command::FAILURE;
        }

        $resultat = $this->analyseur->analyser($chemin);
        $constats = $this->generateur->pour($projetId, $resultat);
        $this->constats->remplacerAuto($projetId, Referentiel::ComplexitePhp, $constats);

        $nonConformes = count(array_filter($constats, static fn ($c): bool => $c->statut()->value === 'non_conforme'));
        $io->success(sprintf('%d méthode(s) analysée(s), %d constat(s) (%d non conformes).', count($resultat->methodes), count($constats), $nonConformes));

        return Command::SUCCESS;
    }
}
