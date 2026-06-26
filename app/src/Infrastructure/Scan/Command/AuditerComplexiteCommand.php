<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Command;

use App\Application\Audit\Command\AnalyserComplexite;
use App\Application\Audit\Command\AnalyserComplexiteHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Domain\Audit\Exception\TypeProjetIncompatible;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 2ᵉ moteur (complexité) : audite la complexité du code d'un projet de type
 * « Complexité PHP » (cible = chemin) et alimente ses constats.
 */
#[AsCommand(name: 'app:complexite:auditer', description: 'Analyse la complexité d\'un projet « Complexité PHP » et génère ses constats.')]
final class AuditerComplexiteCommand extends Command
{
    public function __construct(private readonly AnalyserComplexiteHandler $analyser)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('projetId', InputArgument::REQUIRED, 'Identifiant du projet « Complexité PHP »');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projetIdBrut = $input->getArgument('projetId');
        $projetId = is_string($projetIdBrut) ? $projetIdBrut : '';

        try {
            $nb = ($this->analyser)(new AnalyserComplexite($projetId));
        } catch (ProjetIntrouvable|TypeProjetIncompatible $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('%d constat(s) de complexité générés.', $nb));

        return Command::SUCCESS;
    }
}
