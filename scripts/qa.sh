#!/usr/bin/env bash
#
# Garde-fou QA local (socle) — à lancer avant chaque commit.
# Exécute PHP-CS-Fixer (vérification) + PHPStan level 9 dans le conteneur www.
# Code de sortie non nul = à corriger avant de committer.
#
set -euo pipefail

cd "$(dirname "$0")/.."
EXEC=(docker compose exec -T www)

echo "▶ PHP-CS-Fixer (vérification, sans modification)…"
"${EXEC[@]}" vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php

echo "▶ Réchauffement du cache (pour l'analyse Symfony)…"
"${EXEC[@]}" php bin/console cache:warmup --env=dev >/dev/null

echo "▶ PHPStan (level 9)…"
"${EXEC[@]}" vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --no-progress

echo "✅ QA OK"
