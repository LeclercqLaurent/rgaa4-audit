<?php

declare(strict_types=1);

namespace App\Infrastructure\Scan\Command;

use App\Application\Audit\Command\LancerAudit;
use App\Application\Audit\Command\LancerAuditHandler;
use App\Domain\Audit\Exception\ProjetIntrouvable;
use App\Infrastructure\Scan\Messenger\ConsommateurAsync;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Audite un projet en ligne de commande, quel que soit son référentiel
 * (point d'entrée CLI équivalent de POST /api/projets/{id}/auditer).
 *
 * - Complexité : analyse synchrone, constats produits immédiatement.
 * - RGAA : scan asynchrone planifié, puis la file est drainée pour aller au bout
 *   (scan axe-core + génération des constats) — sauf --planifier-seulement.
 */
#[AsCommand(name: 'app:auditer', description: 'Audite un projet (RGAA ou Complexité) en ligne de commande.')]
final class AuditerCommand extends Command
{
    public function __construct(
        private readonly LancerAuditHandler $lancer,
        private readonly ConsommateurAsync $consommateur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('projetId', InputArgument::REQUIRED, 'Identifiant du projet à auditer')
            ->addOption('planifier-seulement', null, InputOption::VALUE_NONE, 'RGAA : planifier le scan sans drainer la file (laissé au worker)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projetIdBrut = $input->getArgument('projetId');
        $projetId = is_string($projetIdBrut) ? $projetIdBrut : '';

        try {
            $resultat = ($this->lancer)(new LancerAudit($projetId));
        } catch (ProjetIntrouvable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $planifierSeulement = true === $input->getOption('planifier-seulement');

        if ($resultat->synchrone) {
            $io->success(sprintf('Analyse terminée : %d constat(s) générés.', $resultat->constatsGeneres ?? 0));
        } elseif ($planifierSeulement) {
            $io->success('Scan RGAA planifié (asynchrone). Le worker traitera la file (ou POST /api/scans/consommer).');
        } else {
            $io->text('Scan RGAA planifié — traitement de la file…');
            $traites = $this->consommateur->consommer();
            $io->success(sprintf('Audit RGAA terminé : %d message(s) traité(s) (scan + génération des constats).', $traites));
        }

        return Command::SUCCESS;
    }
}
