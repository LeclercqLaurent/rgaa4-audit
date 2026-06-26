<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity\Command;

use App\Domain\Identity\Entity\Utilisateur;
use App\Domain\Identity\Port\UtilisateurRepository;
use App\Domain\Identity\ValueObject\Email;
use App\Domain\Identity\ValueObject\RoleUtilisateur;
use App\Domain\Shared\Port\IdGenerator;
use App\Infrastructure\Identity\Security\SecurityUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:utilisateur:creer', description: 'Crée un utilisateur (mot de passe haché en Argon2id).')]
final class CreerUtilisateurCommand extends Command
{
    public function __construct(
        private readonly UtilisateurRepository $utilisateurs,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly IdGenerator $ids,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail')
            ->addArgument('motDePasse', InputArgument::REQUIRED, 'Mot de passe (≥ 12 caractères)')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Rôle : auditeur ou client', 'auditeur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailBrut = $input->getArgument('email');
        $motDePasse = $input->getArgument('motDePasse');
        $emailBrut = is_string($emailBrut) ? $emailBrut : '';
        $motDePasse = is_string($motDePasse) ? $motDePasse : '';

        if (mb_strlen($motDePasse) < 12) {
            $io->error('Le mot de passe doit faire au moins 12 caractères (recommandation ANSSI).');

            return Command::FAILURE;
        }

        $email = new Email($emailBrut);
        $role = 'client' === $input->getOption('role') ? RoleUtilisateur::Client : RoleUtilisateur::Auditeur;
        $hache = $this->hasher->hashPassword(new SecurityUser($email->valeur, '', []), $motDePasse);

        $this->utilisateurs->save(new Utilisateur($this->ids->generate(), $email, $hache, [$role]));

        $io->success(sprintf('Utilisateur %s créé (%s).', $email->valeur, $role->value));

        return Command::SUCCESS;
    }
}
