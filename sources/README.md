# Sources — Kit d'audit RGAA 4

Documents officiels du **kit d'audit RGAA**, publiés par la DINUM.

- **Source** : https://accessibilite.numerique.gouv.fr/ressources/kit-audit/
- **Récupéré le** : 2026-06-23
- **Licence** : ressources publiques de l'État français (Licence Ouverte / Etalab).

Ces fichiers servent de **référence métier** pour l'outil : structure de la
grille d'audit (106 critères), format du rapport d'audit et de la déclaration
d'accessibilité à reproduire dans le module Reporting.

| Fichier | Rôle |
|---|---|
| `rgaa4.1.2.modele-de-grille-d-audit.ods` | Grille d'audit officielle (13 thématiques / 106 critères / tests) → modèle du référentiel |
| `rgaa4-2019-modele-rapport-audit.odt` / `.pdf` | Modèle de rapport d'audit → cible du module Reporting |
| `rgaa4-2019-exemple-declaration.odt` / `.pdf` | Exemple de déclaration d'accessibilité → cible du module Reporting |
| `rgaa-criteres-tests.json` | **Référentiel officiel complet** (critères + tests détaillés + références WCAG/techniques) → source du référentiel |

> `rgaa-criteres-tests.json` provient de `criteres.json` du dépôt officiel
> [DISIC/accessibilite.numerique.gouv.fr](https://github.com/DISIC/accessibilite.numerique.gouv.fr)
> (branche `main`). Le `.ods` ne contient que thématique/critère/intitulé ; le
> JSON ajoute les **tests détaillés** (258) et les **références WCAG**, d'où son
> rôle de source autoritative du référentiel. Régénération :
> `python3 scripts/build-rgaa-referentiel.py`.

> Ne pas éditer ces fichiers : ce sont des originaux de référence. Toute
> transformation (extraction des critères, gabarit de rapport) se fait dans le
> code, pas dans ces sources.
