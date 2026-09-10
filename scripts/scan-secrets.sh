#!/usr/bin/env bash
#
# Garde-fou « pas de secret dans le dépôt ».
#
# Un secret poussé sur un dépôt public reste lisible par SHA même après
# réécriture de l'historique : le seul remède fiable est de ne jamais l'y
# mettre. Ce script bloque en amont les valeurs qui en ont la forme.
#
# Usage :
#   scripts/scan-secrets.sh            # analyse l'index (contexte pre-commit)
#   scripts/scan-secrets.sh --tracked  # analyse tous les fichiers suivis
#
# Code de sortie non nul = secret probable, commit à revoir.
set -euo pipefail

cd "$(dirname "$0")/.."

MODE="${1:---staged}"
if [ "$MODE" = "--tracked" ]; then
    mapfile -t FICHIERS < <(git ls-files)
else
    mapfile -t FICHIERS < <(git diff --cached --name-only --diff-filter=ACM)
fi
[ ${#FICHIERS[@]} -eq 0 ] && { echo "▶ Aucun fichier à analyser."; exit 0; }

# Affectation d'une variable au nom sensible suivie d'une valeur à forte
# entropie (≥ 24 caractères hexadécimaux ou base64), guillemets optionnels.
MOTIF_AFFECTATION='(PASSPHRASE|SECRET|TOKEN|PASSWORD|API_?KEY|PRIVATE_?KEY)[A-Z0-9_]*[[:space:]]*[=:][[:space:]]*.?[A-Za-z0-9+/=_-]{24,}'
# Bloc de clé privée collé en clair.
MOTIF_PEM='-----BEGIN [A-Z ]*PRIVATE KEY-----'

TROUVAILLES=0
for f in "${FICHIERS[@]}"; do
    [ -f "$f" ] || continue
    case "$f" in
        composer.lock|*/composer.lock|package-lock.json|*/package-lock.json) continue ;;
        scripts/scan-secrets.sh) continue ;;
    esac

    # Les lignes commentées et les références %env(...)% ne sont pas des valeurs.
    if RESULTAT=$(grep -nEI "$MOTIF_AFFECTATION|$MOTIF_PEM" "$f" \
        | grep -vE '^[0-9]+:[[:space:]]*(#|//|\*)' \
        | grep -vE '%env\(' ); then
        echo "✖ $f"
        echo "$RESULTAT" | sed 's/^/    /'
        TROUVAILLES=$((TROUVAILLES + 1))
    fi
done

if [ "$TROUVAILLES" -gt 0 ]; then
    cat >&2 <<'MSG'

✖ Secret probable détecté.

  Une valeur sensible ne se versionne pas : la mettre dans le vault Symfony
  (bin/console secrets:set <NOM>) et ne laisser dans .env qu'un commentaire
  la référençant. En CI, la passer par une variable d'environnement.

  Faux positif ? Ajouter une exclusion ciblée dans scripts/scan-secrets.sh.
MSG
    exit 1
fi

echo "✅ Aucun secret détecté."
