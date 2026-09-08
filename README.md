# rgaa4-audit

Un outil d'audit **RGAA 4** complet : une API **Symfony 8.1** en architecture
hexagonale, un scanner **axe-core** piloté depuis l'outil, et la traduction
`axe-core → WCAG → RGAA` qui manque entre les deux.

[![CI](https://github.com/LeclercqLaurent/rgaa4-audit/actions/workflows/ci.yml/badge.svg)](https://github.com/LeclercqLaurent/rgaa4-audit/actions/workflows/ci.yml)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-8.1-000000?logo=symfony&logoColor=white)](https://symfony.com/)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-2A9D8F)](https://phpstan.org/)
[![Couverture métier](https://img.shields.io/badge/couverture%20m%C3%A9tier-94.2%25-brightgreen)](#ce-que-les-tests-prouvent)
[![Licence](https://img.shields.io/badge/code-MIT-blue)](LICENSE)
[![Données](https://img.shields.io/badge/donn%C3%A9es-Licence%20Ouverte%202.0-blue)](LICENCE-DONNEES.txt)

---

## Le problème

Un audit RGAA se conduit aujourd'hui dans un tableur. La DINUM publie une grille
`.ods` de **106 critères**, l'auditeur la remplit à la main, puis recopie le
résultat dans un rapport et dans la déclaration d'accessibilité.

À côté, les outils automatisés (axe-core, Lighthouse, Pa11y) savent détecter une
partie des problèmes, mais ils rapportent en **critères de succès WCAG**. La
grille, elle, est en **critères RGAA**. Entre les deux, rien : le résultat du
scan ne se déverse pas dans la grille, et chaque équipe refait la correspondance
à la main.

Ce dépôt relie les deux bouts.

```
   URL du site
        │
        ▼
   scan axe-core  ──▶  violations WCAG  ──▶  mapping WCAG → RGAA  ──▶  constats
   (Node headless)                            (49 critères de succès)   automatiques
                                                                             │
   saisie de l'auditeur ─────────────────────────────────────────────▶  constats
   (les critères qu'aucune machine ne tranche)                          manuels
                                                                             │
                                                                             ▼
                                                        taux de conformité + rapport
                                                              (HTML et PDF)
```

**Ce qu'aucun constat ne couvre est compté comme non évalué**, et non passé
sous silence. Le calcul reçoit la liste des critères que le référentiel impose
de regarder : tout critère sans verdict, sur chaque page de l'échantillon,
apparaît au décompte. Tant qu'il en reste, le rapport refuse de prononcer une
conformité, comme l'exige le RGAA. C'est le point le plus important à
comprendre avant de lire un chiffre produit ici.

---

## Ce que le dépôt démontre

C'est avant tout une **application Symfony écrite selon une discipline**, pas une
démonstration de fonctionnalités. Le RGAA est le domaine métier ; l'intérêt du
code est ailleurs.

| Ce qui est montré | Où le vérifier |
|---|---|
| Architecture hexagonale réelle | `app/src/Domain/*/Port/` déclare 11 ports, `app/src/Infrastructure/*/Adapter/` les implémente |
| DDD sur 5 bounded contexts | `Audit`, `Scan`, `Referential`, `Reporting`, `Identity`, chacun avec ses entités, ses Value Objects, ses événements et ses exceptions |
| CQRS | `app/src/Application/<Contexte>/{Command,Query}`, aucune logique métier dans les handlers |
| API Platform comme adaptateur | les ressources exposées (`app/src/ApiResource/`) sont des DTO, jamais les entités Doctrine |
| Schéma piloté par migrations | 9 migrations Doctrine, aucun `schema:update` |
| Sécurité ANSSI | JWT court (900 s), refresh token avec rotation, Argon2id, `login_throttling`, passphrase hors du dépôt |
| QA en barrière | PHPStan **level 9** avec les règles Sonar S107 et S1142 réécrites, PSR-12, plancher de couverture qui casse le build |
| Accessibilité de l'outil lui-même | la SPA est testée par rôle accessible et passée à axe en CI |

Le domaine est en **PHP pur** : `app/src/Domain/` n'importe ni Symfony, ni
Doctrine, ni API Platform.

---

## Architecture

```
app/src/
├── Domain/                       PHP pur, aucune dépendance framework
│   ├── Audit/                    projets, échantillon de pages, constats, taux
│   ├── Scan/                     scans a11y, résultats normalisés
│   ├── Referential/              RGAA 4.1.2 : thématiques, critères, tests
│   ├── Reporting/                rapport d'audit
│   ├── Identity/                 utilisateurs, authentification
│   └── Shared/                   Value Objects et ports transverses
│
├── Application/                  CQRS : Command, Query, handlers
│   └── Audit/Moteur/             MoteurRgaa et MoteurComplexite
│
├── Infrastructure/               les adaptateurs concrets
│   ├── */Adapter/                repositories Doctrine, scanner, renderer
│   ├── Persistence/Entity/       entités Doctrine, distinctes du Domain
│   ├── Identity/Security/        JWT, firewalls
│   └── Scan/Messenger/           traitement asynchrone des scans
│
├── ApiResource/ + State/         DTO exposés et providers/processors API Platform
└── Kernel.php

spa/                              SPA React 19 + TypeScript (Vite, Tailwind v4)
scanner/                          service Node : URL en entrée, JSON axe en sortie
sources/                          kit d'audit officiel DINUM, non modifié
```

Le flux de dépendance ne pointe que vers l'intérieur : Infrastructure → Application → Domain.

---

## Les trois points qui méritent une lecture

### 1. La frontière PHP ↔ Node est un contrat, pas un couplage

axe-core est du JavaScript et exige un navigateur : il n'a rien à faire dans du
PHP. Le Domain déclare donc un port `Scan/Port/PageScanner` dont le contrat tient
en une phrase : **une URL en entrée, un résultat de scan normalisé en sortie**.

L'adaptateur invoque un service Node (Puppeteer + axe-core) par la ligne de
commande, via une commande lue dans la configuration. Passer d'un appel CLI à un
microservice HTTP ne touche qu'une classe d'Infrastructure : ni le Domain, ni
l'Application ne savent que Node existe.

### 2. Le mapping ne se court-circuite pas

La tentation est d'écrire en dur « la règle axe `image-alt` correspond au critère
RGAA 1.1 ». C'est faux et ça ne tient pas dans le temps.

Le pivot est le **critère de succès WCAG** : axe rattache ses règles à des
critères WCAG, et le référentiel RGAA déclare pour chacun de ses critères les
critères WCAG dont il relève. La chaîne complète est donc
`règle axe → critère de succès WCAG → critères RGAA`, dérivée du référentiel
officiel, jamais saisie à la main. **49 critères de succès WCAG** sont référencés
par le RGAA 4.1.2, de niveaux A et AA uniquement.

Cette table est publiée séparément, en JSON et en YAML, dans
[`rgaa4-referentiel`](https://github.com/LeclercqLaurent/rgaa4-referentiel).

### 3. Deux moteurs d'audit derrière un seul port

Un audit RGAA et un audit de complexité de code n'ont rien en commun sur le fond,
mais tout en commun dans leur forme : un référentiel, des critères, des constats,
un taux. Le Domain déclare un port `Audit/Port/MoteurAudit` ; `MoteurRgaa` et
`MoteurComplexite` l'implémentent, et un projet porte son type.

Le second moteur s'appuie sur
[`phpx-complexity`](https://github.com/LeclercqLaurent/phpx-complexity). Ajouter
un troisième référentiel revient à écrire une classe, pas à toucher au reste.

---

## Ce que les tests prouvent

**63 tests PHP, 130 assertions**, plus 5 tests de la SPA. Ils tournent en CI sur
une base MariaDB construite **par les migrations**, jamais par un dump, et les
chiffres ci-dessous sont ceux que la CI publie à chaque exécution.

| Couche | Couverture de lignes | Sous plancher |
|---|---:|:---:|
| `Application` | 94,3 % | ✅ |
| `Domain` | 94,0 % | ✅ |
| `State` (providers API Platform) | 83,2 % | |
| `Infrastructure` | 65,9 % | |
| `ApiResource` (DTO) | 88,9 % | |
| **Total `src/`** | **77,9 %** | |

Le plancher qui fait échouer le build porte sur le **métier** : Domain et
Application réunis, à **94,2 %** pour un minimum de 90 %
(`scripts/couverture.php`). Le reste est mesuré et affiché, jamais opposé à un
seuil : un repository Doctrine se vérifie par un test d'API qui traverse la pile,
pas en visant un pourcentage sur une classe de mapping. Le chiffre global,
77,9 %, est donné tel quel plutôt que dissimulé derrière la seule ligne flatteuse.

Ce que les tests vérifient concrètement :

- les **invariants du Domain** : une URL invalide est refusée à la construction,
  un critère sans constat est compté non évalué page par page sans peser sur le
  taux, un scan ne change d'état que dans le sens permis ;
- l'**API de bout en bout**, par contexte : création de projet, échantillon de
  pages, planification de scan, constats, taux, rapport ;
- les **frontières de l'API** : 401 sans jeton et sur mauvais mot de passe, 404
  sur une ressource inconnue, 422 sur une entrée invalide, et le référentiel qui
  reste public là où le reste est fermé ;
- côté SPA, les écrans sont montés **par rôle accessible** (`getByRole`,
  `getByLabelText`) et passés à **axe** : une régression d'accessibilité fait
  échouer la CI.

La QA est vérifiée par `scripts/qa.sh` avant chaque commit et rejouée en CI :
PHP-CS-Fixer (PSR-12, `declare(strict_types=1)`), puis PHPStan level 9 augmenté
des règles Sonar **S107** (7 paramètres) et **S1142** (3 `return`) et d'un
plafond de complexité cognitive **S3776**. Le baseline PHPStan contient **une**
entrée, un faux positif du squelette Symfony.

---

## Sécurité

Les choix suivent les recommandations ANSSI, et se lisent dans la configuration :

- **Argon2id** pour les mots de passe, `login_throttling` contre le bourrinage ;
- **JWT** d'une durée de vie de 900 secondes, **refresh token avec rotation** à
  chaque renouvellement ;
- la **passphrase JWT n'est pas dans le dépôt** : vault Symfony sur le poste,
  variable d'environnement en CI. Ni la clé privée de déchiffrement, ni les
  clés JWT ne sont versionnées.

---

## Démarrer en local

Prérequis : Docker et Docker Compose. Ajouter `127.0.0.1 api.rgaa.local` à
`/etc/hosts`.

```bash
docker compose up -d
docker compose exec www composer install

# Le schéma vient des migrations, jamais d'un dump.
docker compose exec www php bin/console doctrine:migrations:migrate

# Le référentiel officiel et la table de correspondance axe-core.
docker compose exec www php bin/console app:referential:load
docker compose exec www php bin/console app:mapping:load

# Les secrets d'authentification : voir la section ci-dessous, l'ordre compte.

# Un compte pour se connecter.
docker compose exec www php bin/console app:utilisateur:creer <email> <motDePasse>
```

| Service | Accès |
|---|---|
| API (Symfony) | http://api.rgaa.local:8081/api |
| Documentation de l'API | http://api.rgaa.local:8081/api/docs |
| SPA (Vite) | http://localhost:5173 |
| Mailpit | http://localhost:8026 |

### Les secrets, à générer après le clone

Aucun secret n'est versionné : ni le vault Symfony (`app/config/secrets/`), ni la
paire de clés JWT (`app/config/jwt/*.pem`). Un clone frais n'en a donc aucun, et
c'est voulu. Il faut les produire une fois, dans cet ordre :

```bash
# 1. La paire de clés du vault de dev, propre à ce poste.
docker compose exec www php bin/console secrets:generate-keys

# 2. La passphrase qui protégera la clé privée JWT (saisie interactive).
docker compose exec www php bin/console secrets:set JWT_PASSPHRASE

# 3. La paire de clés JWT, chiffrée avec cette passphrase.
docker compose exec www php bin/console lexik:jwt:generate-keypair

# 4. Le vault de l'environnement de test, avec LA MÊME passphrase qu'en dev.
docker compose exec www php bin/console secrets:generate-keys --env=test
docker compose exec www php bin/console secrets:set JWT_PASSPHRASE --env=test

# Contrôle
docker compose exec www php bin/console lexik:jwt:check-config
```

**L'ordre n'est pas cosmétique** : `lexik:jwt:generate-keypair` lit
`JWT_PASSPHRASE` pour chiffrer la clé privée qu'il produit. Lancé en premier, il
échoue.

**Et la passphrase doit être identique en dev et en test.** Les deux
environnements ont chacun leur vault, mais pointent sur la **même** paire de
clés `config/jwt/*.pem` : deux valeurs différentes rendent la clé privée
illisible côté test, et toute la suite de tests d'API tombe. C'est aussi
pourquoi `secrets:set --random`, pratique ailleurs, ne convient pas ici : il
tirerait une valeur différente par environnement.

**Le symptôme, si l'étape a été oubliée**, est le même sur n'importe quelle
commande ou requête :

```
Environment variable not found: "JWT_PASSPHRASE".
```

**Sans vault, c'est possible aussi** : une vraie variable d'environnement prime
sur le vault côté Symfony. Définir `JWT_PASSPHRASE` dans l'environnement suffit,
et c'est ce que fait la CI, qui n'a pas de vault à déchiffrer. Le vault n'a
d'intérêt que pour garder la valeur d'un poste à l'autre sans la mettre en clair.

Ces fichiers ne doivent jamais revenir dans le dépôt : la clé
`*.decrypt.private.php` ouvre le vault, et `.gitignore` couvre déjà les deux
emplacements.

Le **second moteur d'audit** est un outil externe,
[`phpx-complexity`](https://github.com/LeclercqLaurent/phpx-complexity). Les
projets de type « Complexité PHP » en ont besoin : cloner l'outil, puis faire
pointer `COMPLEXITY_COMMAND` sur son exécutable (voir `app/.env`). Les projets
RGAA n'en dépendent pas.

Auditer un projet sans passer par l'interface :

```bash
docker compose exec www php bin/console app:auditer <projetId>
```

Contrôles qualité :

```bash
./scripts/qa.sh                                        # PSR-12 + PHPStan 9
docker compose exec www vendor/bin/phpunit             # tests PHP
docker compose exec spa npm run test                   # tests SPA + axe

# Couverture : pcov est dans l'image, désactivé par défaut.
docker compose exec www php -d pcov.enabled=1 vendor/bin/phpunit --coverage-clover var/clover.xml
php scripts/couverture.php app/var/clover.xml 90
```

---

## Ce que ce n'est pas

**Ce n'est pas un produit fini.** Pas de multi-tenant, pas de gestion fine des
rôles, pas d'internationalisation, pas d'export de la déclaration d'accessibilité.
Le périmètre s'arrête à ce qui rend l'architecture démontrable de bout en bout.

**Ce n'est pas un audit RGAA automatique.** Aucun outil ne l'est, et prétendre le
contraire est la faute la plus répandue du secteur. Une part importante des
critères demande un jugement humain : pertinence d'une alternative textuelle,
cohérence d'un titrage, utilisabilité réelle au clavier. Le scan ne fait que
pré-remplir ce qu'une machine peut trancher. Le rapport produit ici ne vaut pas
déclaration de conformité.

**Un scan seul ne suffit pas à prononcer une conformité, et l'outil le dit.**
Sur une exécution réelle contre un site statique, axe-core se prononce sur
**60 des 106 critères** ; les 46 autres restent à la charge de l'auditeur. Ils
sont comptés comme non évalués, et tant qu'il en reste le rapport affiche « la
conformité ne peut pas être déclarée » à la place de la déclaration.

Le taux, lui, garde la définition officielle du RGAA, conformes sur conformes
plus non conformes : les non évalués n'y entrent pas. Un projet peut donc
afficher 100 % **et** 92 critères non évalués, ce qui se lit exactement comme il
faut : parfait sur ce qui a été regardé, et loin d'être terminé.

---

## Licences

- **Code** : MIT (`LICENSE`).
- **Données RGAA** (`sources/`, `app/fixtures/rgaa/`) : Licence Ouverte 2.0
  (Etalab), publiées par la DINUM. Voir `LICENCE-DONNEES.txt`.

Le dépôt est rédigé en **français**, contrairement aux autres outils publiés ici.
Son domaine est un référentiel réglementaire français, ses identifiants métier
sont ceux de la grille officielle, et son lectorat est celui qui conduit des
audits RGAA. Traduire aurait ajouté une couche de correspondance à chaque
lecture.

## Dépôts liés

- [`rgaa4-referentiel`](https://github.com/LeclercqLaurent/rgaa4-referentiel) :
  le RGAA 4.1.2 en données, et la jointure axe-core / WCAG / RGAA dans les deux sens.
- [`phpx-complexity`](https://github.com/LeclercqLaurent/phpx-complexity) :
  l'auditeur de complexité qui sert de second moteur.
- [`phpstan-sonar-rules`](https://github.com/LeclercqLaurent/phpstan-sonar-rules) :
  les règles Sonar S107 et S1142 en extension PHPStan.
