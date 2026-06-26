#!/usr/bin/env python3
"""Construit le référentiel RGAA complet (critères + tests + références WCAG).

Source autoritative : sources/rgaa-criteres-tests.json
  (criteres.json officiel — dépôt DISIC/accessibilite.numerique.gouv.fr)
Sortie : app/fixtures/rgaa/criteres.yaml
  13 thématiques / 106 critères / ~257 tests, avec pour chaque critère ses
  références WCAG (utilisées par le mapping axe-core → WCAG → RGAA) et techniques.

À relancer si la source JSON est mise à jour :
    python3 scripts/build-rgaa-referentiel.py

Le texte (intitulés, énoncés de tests) est conservé tel quel, en markdown
officiel : les `[terme](#ancre)` pointent vers le glossaire RGAA et sont
volontairement préservés pour un rendu ultérieur.
Dépendance : PyYAML.
"""
import json

import yaml

SOURCE = "sources/rgaa-criteres-tests.json"
OUTPUT = "app/fixtures/rgaa/criteres.yaml"


def references(criterium):
    wcag, techniques = [], []
    for ref in criterium.get("references", []):
        wcag.extend(ref.get("wcag", []))
        techniques.extend(ref.get("techniques", []))
    return wcag, techniques


def main():
    data = json.load(open(SOURCE, encoding="utf-8"))
    thematiques = []
    nb_criteres = nb_tests = 0

    for topic in data["topics"]:
        tnum = topic["number"]
        criteres = []
        for entry in topic["criteria"]:
            crit = entry["criterium"]
            cnum = f"{tnum}.{crit['number']}"
            wcag, techniques = references(crit)
            tests = []
            for tkey in sorted(crit.get("tests", {}), key=int):
                enonce = crit["tests"][tkey]
                if isinstance(enonce, str):
                    enonce = [enonce]
                tests.append({"numero": f"{cnum}.{tkey}", "enonce": enonce})
                nb_tests += 1
            criteres.append({
                "numero": cnum,
                "intitule": crit["title"],
                "wcag": wcag,
                "techniques": techniques,
                "tests": tests,
            })
            nb_criteres += 1
        thematiques.append({"numero": tnum, "nom": topic["topic"], "criteres": criteres})

    out = {
        "referentiel": "RGAA",
        "version": "4.1.2",
        "wcag": str(data.get("wcag", {}).get("version", "")),
        "sources": {
            "grille": "sources/rgaa4.1.2.modele-de-grille-d-audit.ods",
            "referentiel": SOURCE,
        },
        "thematiques": thematiques,
    }

    assert len(thematiques) == 13, f"attendu 13 thématiques, obtenu {len(thematiques)}"
    assert nb_criteres == 106, f"attendu 106 critères, obtenu {nb_criteres}"

    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write("# RGAA 4.1.2 — référentiel complet (13 thématiques / 106 critères / tests)\n")
        f.write(f"# Source autoritative : {SOURCE} (criteres.json officiel DISIC)\n")
        f.write("# Données de référence : ne pas éditer à la main.\n")
        f.write("# Régénérer via : python3 scripts/build-rgaa-referentiel.py\n")
        yaml.safe_dump(out, f, allow_unicode=True, sort_keys=False, width=10000)

    print(f"OK : {len(thematiques)} thématiques / {nb_criteres} critères / {nb_tests} tests -> {OUTPUT}")


if __name__ == "__main__":
    main()
