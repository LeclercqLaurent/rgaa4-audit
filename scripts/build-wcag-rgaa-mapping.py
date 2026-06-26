#!/usr/bin/env python3
"""Construit la table de correspondance WCAG SC -> critères RGAA.

Entrée : app/fixtures/rgaa/criteres.yaml (références WCAG par critère)
Sortie : app/fixtures/rgaa/mapping-wcag-rgaa.yaml

Pour chaque critère de succès (Success Criterion) WCAG, liste les critères RGAA
qui le référencent. Ajoute `axe_tag` = tag axe-core correspondant (`wcag` + numéro
sans points, ex. 1.1.1 -> `wcag111`), pour joindre directement un résultat
axe-core au(x) critère(s) RGAA : c'est le maillon `axe-core → WCAG → RGAA`.

À relancer après régénération du référentiel :
    python3 scripts/build-wcag-rgaa-mapping.py
Dépendance : PyYAML.
"""
import re

import yaml

INPUT = "app/fixtures/rgaa/criteres.yaml"
OUTPUT = "app/fixtures/rgaa/mapping-wcag-rgaa.yaml"

# "1.1.1 Non-text Content (A)" -> (numéro, intitulé, niveau)
WCAG_RE = re.compile(r"^([\d.]+)\s+(.*?)\s*\(([A]{1,3})\)\s*$")


def parse_wcag(label):
    m = WCAG_RE.match(label)
    if not m:
        raise ValueError(f"Référence WCAG non reconnue : {label!r}")
    return m.group(1), m.group(2).strip(), m.group(3)


def rgaa_key(numero):
    return tuple(int(p) for p in numero.split("."))


def wcag_key(numero):
    return tuple(int(p) for p in numero.split("."))


def main():
    ref = yaml.safe_load(open(INPUT, encoding="utf-8"))
    table = {}  # sc -> {intitule, niveau, criteres_rgaa:set}

    for topic in ref["thematiques"]:
        for crit in topic["criteres"]:
            for label in crit["wcag"]:
                sc, intitule, niveau = parse_wcag(label)
                entry = table.setdefault(
                    sc, {"intitule": intitule, "niveau": niveau, "criteres": set()}
                )
                entry["criteres"].add(crit["numero"])

    criteres_succes = []
    for sc in sorted(table, key=wcag_key):
        e = table[sc]
        criteres_succes.append({
            "wcag": sc,
            "intitule": e["intitule"],
            "niveau": e["niveau"],
            "axe_tag": "wcag" + sc.replace(".", ""),
            "criteres_rgaa": sorted(e["criteres"], key=rgaa_key),
        })

    out = {
        "referentiel": ref.get("referentiel", "RGAA"),
        "version": ref.get("version"),
        "wcag": ref.get("wcag"),
        "source": INPUT,
        "criteres_succes": criteres_succes,
    }

    with open(OUTPUT, "w", encoding="utf-8") as f:
        f.write("# Correspondance WCAG SC -> critères RGAA (dérivée du référentiel).\n")
        f.write("# axe_tag : tag axe-core du critère de succès (jointure axe-core → WCAG → RGAA).\n")
        f.write(f"# Données dérivées : ne pas éditer à la main. Régénérer via : python3 scripts/build-wcag-rgaa-mapping.py\n")
        yaml.safe_dump(out, f, allow_unicode=True, sort_keys=False, width=10000)

    nb_liens = sum(len(c["criteres_rgaa"]) for c in criteres_succes)
    niveaux = {}
    for c in criteres_succes:
        niveaux[c["niveau"]] = niveaux.get(c["niveau"], 0) + 1
    print(f"OK : {len(criteres_succes)} critères de succès WCAG "
          f"({', '.join(f'{k}:{v}' for k, v in sorted(niveaux.items()))}) "
          f"-> {nb_liens} liens vers critères RGAA")
    print(f"     écrit -> {OUTPUT}")


if __name__ == "__main__":
    main()
