#!/usr/bin/env python3
"""Construit la table inverse : critère RGAA -> SC WCAG -> axe_tag.

Entrée : app/fixtures/rgaa/criteres.yaml
Sortie : app/fixtures/rgaa/mapping-rgaa-wcag.yaml

Pour chaque critère RGAA, liste ses critères de succès WCAG (numéro, intitulé,
niveau, `axe_tag` axe-core) et l'ensemble agrégé `axe_tags`. Destiné à la fiche
critère côté UI (« ce critère se rattache à WCAG X.Y.Z, tags axe-core … ») et au
pré-remplissage automatique des résultats de scan par critère.

À relancer après régénération du référentiel :
    python3 scripts/build-rgaa-wcag-mapping.py
Dépendance : PyYAML.
"""
import re

import yaml

INPUT = "app/fixtures/rgaa/criteres.yaml"
OUTPUT = "app/fixtures/rgaa/mapping-rgaa-wcag.yaml"

WCAG_RE = re.compile(r"^([\d.]+)\s+(.*?)\s*\(([A]{1,3})\)\s*$")


def parse_wcag(label):
    m = WCAG_RE.match(label)
    if not m:
        raise ValueError(f"Référence WCAG non reconnue : {label!r}")
    return m.group(1), m.group(2).strip(), m.group(3)


def rgaa_key(numero):
    return tuple(int(p) for p in numero.split("."))


def main():
    ref = yaml.safe_load(open(INPUT, encoding="utf-8"))
    criteres = []
    nb_auto = 0

    for topic in ref["thematiques"]:
        for crit in topic["criteres"]:
            seen, wcag = set(), []
            for label in crit["wcag"]:
                sc, intitule, niveau = parse_wcag(label)
                if sc in seen:
                    continue
                seen.add(sc)
                wcag.append({
                    "sc": sc,
                    "intitule": intitule,
                    "niveau": niveau,
                    "axe_tag": "wcag" + sc.replace(".", ""),
                })
            wcag.sort(key=lambda w: tuple(int(p) for p in w["sc"].split(".")))
            axe_tags = [w["axe_tag"] for w in wcag]
            if axe_tags:
                nb_auto += 1
            criteres.append({
                "rgaa": crit["numero"],
                "thematique": topic["numero"],
                "wcag": wcag,
                "axe_tags": axe_tags,
            })

    criteres.sort(key=lambda c: rgaa_key(c["rgaa"]))
    out = {
        "referentiel": ref.get("referentiel", "RGAA"),
        "version": ref.get("version"),
        "wcag": ref.get("wcag"),
        "source": INPUT,
        "criteres": criteres,
    }

    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write("# Correspondance critère RGAA -> SC WCAG -> axe_tag (dérivée du référentiel).\n")
        f.write("# axe_tags : tags axe-core rattachés au critère (pré-remplissage du scan).\n")
        f.write("# Données dérivées : ne pas éditer à la main. Régénérer via : python3 scripts/build-rgaa-wcag-mapping.py\n")
        yaml.safe_dump(out, f, allow_unicode=True, sort_keys=False, width=10000)

    print(f"OK : {len(criteres)} critères RGAA, dont {nb_auto} avec ≥1 SC WCAG "
          f"-> écrit {OUTPUT}")


if __name__ == "__main__":
    main()
