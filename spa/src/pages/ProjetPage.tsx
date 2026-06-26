import { useQueryClient } from '@tanstack/react-query';
import { ArrowLeft, Check, Download, ExternalLink, FileText, Pencil, Play, Plus, RefreshCw, Trash2, X } from 'lucide-react';
import { FormEvent, useEffect, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  LIBELLE_TYPE,
  Page,
  Projet,
  Statut,
  ouvrirRapport,
  useAjouterPage,
  useAnalyserComplexite,
  useConsommerScans,
  useConstats,
  useDefinirStatut,
  useLancerScan,
  useModifierPage,
  useProjet,
  useScans,
  useSupprimerPage,
  useTaux,
} from '../api';
import { Donut } from '../components/Donut';
import { Skeleton } from '../components/Skeleton';

const LIBELLES: Record<Statut, string> = {
  conforme: 'Conforme',
  non_conforme: 'Non conforme',
  non_applicable: 'Non applicable',
  non_teste: 'Non testé',
};

const SCAN_BADGE: Record<string, string> = {
  pending: 'bg-amber-100 text-amber-800',
  running: 'bg-amber-100 text-amber-800',
  done: 'bg-green-100 text-green-800',
  failed: 'bg-red-100 text-red-800',
};

const LIBELLE_SCAN: Record<string, string> = {
  pending: 'En attente',
  running: 'En cours',
  done: 'Terminé',
  failed: 'Échoué',
};

export default function ProjetPage() {
  const { id } = useParams();
  const projetId = id ?? '';
  const { data: projet, isLoading } = useProjet(projetId);
  const { data: scans } = useScans(projetId);
  const queryClient = useQueryClient();

  // Rafraîchit taux et constats dès que le dernier scan passe à « done ».
  const dernierStatut = scans?.[0]?.statut;
  const precedent = useRef<string | undefined>(undefined);
  useEffect(() => {
    if ('done' === dernierStatut && 'done' !== precedent.current) {
      queryClient.invalidateQueries({ queryKey: ['taux', projetId] });
      queryClient.invalidateQueries({ queryKey: ['constats', projetId] });
    }
    precedent.current = dernierStatut;
  }, [dernierStatut, projetId, queryClient]);

  if (isLoading) {
    return (
      <div className="space-y-6" role="status" aria-label="Chargement du projet…">
        <Skeleton className="h-4 w-32" />
        <div className="card space-y-3">
          <Skeleton className="h-7 w-1/2" />
          <Skeleton className="h-4 w-1/3" />
          <Skeleton className="h-4 w-2/3" />
        </div>
        <div className="card">
          <Skeleton className="h-28 w-full" />
        </div>
      </div>
    );
  }

  if (!projet) {
    return <p role="alert">Projet introuvable.</p>;
  }

  return (
    <div className="space-y-6">
      <Link to="/" className="inline-flex items-center gap-1 text-sm">
        <ArrowLeft size={16} aria-hidden="true" /> Tous les projets
      </Link>
      <Entete projet={projet} />
      <TauxSection projetId={projetId} />
      {'rgaa' === projet.type && <PagesSection projetId={projetId} pages={projet.pages} />}
      <ConstatsSection projetId={projetId} />
    </div>
  );
}

function Entete({ projet }: { projet: Projet }) {
  const { data: scans } = useScans(projet.id);
  const lancer = useLancerScan(projet.id);
  const consommer = useConsommerScans(projet.id);
  const analyser = useAnalyserComplexite(projet.id);
  const estRgaa = 'rgaa' === projet.type;
  const dernier = scans?.[0];

  return (
    <section className="card border-l-4 border-l-brand" aria-labelledby="projet-titre">
      <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div className="space-y-2">
          <h1 id="projet-titre">{projet.nom}</h1>
          <p className="text-gray-600">{projet.client}</p>
          {estRgaa ? (
            <a href={projet.cible} className="inline-block break-all text-sm" target="_blank" rel="noopener noreferrer">
              {projet.cible} ↗
            </a>
          ) : (
            <code className="inline-block break-all rounded bg-gray-100 px-2 py-0.5 text-sm text-gray-700">{projet.cible}</code>
          )}
          <div className="flex flex-wrap gap-2 pt-1">
            <span className="inline-flex items-center rounded-full bg-brand/10 px-3 py-1 text-xs font-medium text-brand">{LIBELLE_TYPE[projet.type]}</span>
            {estRgaa && (
              <span className="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                {projet.pages.length} page{projet.pages.length > 1 ? 's' : ''}
              </span>
            )}
            {estRgaa && dernier && (
              <span className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ${SCAN_BADGE[dernier.statut] ?? 'bg-gray-100 text-gray-700'}`}>
                Scan : {LIBELLE_SCAN[dernier.statut] ?? dernier.statut}
              </span>
            )}
          </div>
        </div>

        <div className="flex shrink-0 flex-col gap-2">
          <div className="actions">
            {estRgaa ? (
              <>
                <button className="btn" type="button" onClick={() => lancer.mutate()} disabled={lancer.isPending}>
                  <Play size={16} aria-hidden="true" /> Lancer un scan
                </button>
                <button className="btn btn-secondary" type="button" onClick={() => consommer.mutate()} disabled={consommer.isPending}>
                  <RefreshCw size={16} aria-hidden="true" className={consommer.isPending ? 'animate-spin' : ''} />
                  {consommer.isPending ? 'Traitement…' : 'Traiter la file'}
                </button>
              </>
            ) : (
              <button className="btn" type="button" onClick={() => analyser.mutate()} disabled={analyser.isPending}>
                <RefreshCw size={16} aria-hidden="true" className={analyser.isPending ? 'animate-spin' : ''} />
                {analyser.isPending ? 'Analyse…' : 'Analyser le code'}
              </button>
            )}
          </div>
          <div className="actions">
            <button type="button" className="btn btn-sm btn-secondary" onClick={() => void ouvrirRapport(projet.id, false)}>
              <FileText size={14} aria-hidden="true" /> Rapport HTML
            </button>
            <button type="button" className="btn btn-sm btn-secondary" onClick={() => void ouvrirRapport(projet.id, true)}>
              <Download size={14} aria-hidden="true" /> Rapport PDF
            </button>
          </div>
        </div>
      </div>

      {estRgaa && consommer.isSuccess && (
        <p className="mt-3 text-sm text-gray-600" aria-live="polite">
          {consommer.data.traites} message(s) traité(s).
        </p>
      )}
      {!estRgaa && analyser.isSuccess && (
        <p className="mt-3 text-sm text-gray-600" aria-live="polite">
          {analyser.data.traites} constat(s) de complexité générés.
        </p>
      )}
      {estRgaa && dernier?.erreur && (
        <p className="mt-3 text-sm text-danger" aria-live="polite">
          Dernier scan en échec : {dernier.erreur}
        </p>
      )}
    </section>
  );
}

function TauxSection({ projetId }: { projetId: string }) {
  const { data: taux } = useTaux(projetId);

  if (!taux) {
    return null;
  }

  const pourcentage = null === taux.global ? null : Math.round(taux.global * 1000) / 10;
  const repartition = [
    { valeur: taux.conformes, label: 'Conformes', couleur: 'text-success', point: 'bg-success' },
    { valeur: taux.nonConformes, label: 'Non conformes', couleur: 'text-danger', point: 'bg-danger' },
    { valeur: taux.nonApplicables, label: 'Non applicables', couleur: 'text-gray-500', point: 'bg-gray-400' },
    { valeur: taux.nonTestes, label: 'Non testés', couleur: 'text-gray-500', point: 'bg-gray-300' },
  ];

  return (
    <section className="space-y-3" aria-labelledby="taux-titre">
      <h2 id="taux-titre">Taux de conformité</h2>
      <div className="card">
        <div className="flex flex-col items-center gap-8 md:flex-row md:gap-12">
          <div className="shrink-0 text-center">
            <Donut value={pourcentage} />
            <div className="mt-2 max-w-[150px] text-xs text-gray-500">Conformité des critères évaluables automatiquement</div>
          </div>
          <ul className="grid w-full flex-1 grid-cols-2 gap-3 sm:grid-cols-4">
              {repartition.map((r) => (
                <li key={r.label} className="rounded-lg border border-gray-100 bg-gray-50 p-3 text-center">
                  <div className={`text-2xl font-bold ${r.couleur}`}>{r.valeur}</div>
                  <div className="mt-0.5 inline-flex items-center gap-1.5 text-xs text-gray-600">
                    <span className={`inline-block h-2 w-2 rounded-full ${r.point}`} aria-hidden="true" />
                    {r.label}
                  </div>
                </li>
              ))}
            </ul>
        </div>
      </div>
    </section>
  );
}

function PagesSection({ projetId, pages }: { projetId: string; pages: Page[] }) {
  return (
    <section className="space-y-3" aria-labelledby="pages-titre">
      <h2 id="pages-titre">Pages à auditer (échantillon)</h2>
      <p className="text-sm text-gray-500">
        Les pages représentatives du site qui seront analysées au lancement du scan. En RGAA, l'audit porte sur un
        échantillon de pages, pas sur le site entier.
      </p>
      <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table className="table">
          <caption className="sr-only">Pages de l'échantillon</caption>
          <thead>
            <tr>
              <th scope="col">Titre</th>
              <th scope="col">URL</th>
              <th scope="col" colSpan={3}>
                Actions
              </th>
            </tr>
          </thead>
          <tbody>
            {pages.length === 0 ? (
              <tr>
                <td colSpan={5} className="text-gray-500">
                  Aucune page. Ajoutez-en une ci-dessous.
                </td>
              </tr>
            ) : (
              pages.map((page) => <LignePage key={page.id} projetId={projetId} page={page} />)
            )}
          </tbody>
        </table>
      </div>
      <FormulaireAjoutPage projetId={projetId} />
    </section>
  );
}

function LignePage({ projetId, page }: { projetId: string; page: Page }) {
  const modifier = useModifierPage(projetId);
  const supprimer = useSupprimerPage(projetId);
  const [edition, setEdition] = useState(false);
  const [titre, setTitre] = useState(page.titre);
  const [url, setUrl] = useState(page.url);

  const ouvrirEdition = () => {
    setTitre(page.titre);
    setUrl(page.url);
    setEdition(true);
  };

  const enregistrer = () => {
    modifier.mutate({ pageId: page.id, url, titre }, { onSuccess: () => setEdition(false) });
  };

  const supprimerPage = () => {
    if (window.confirm(`Retirer la page « ${page.titre} » de l'échantillon ?`)) {
      supprimer.mutate(page.id);
    }
  };

  if (edition) {
    return (
      <tr>
        <td>
          <input className="input" aria-label="Titre" value={titre} onChange={(e) => setTitre(e.target.value)} />
        </td>
        <td>
          <input className="input" aria-label="URL" type="url" value={url} onChange={(e) => setUrl(e.target.value)} />
        </td>
        <td colSpan={3}>
          <div className="actions">
            <button type="button" className="btn btn-sm" onClick={enregistrer} disabled={modifier.isPending}>
              <Check size={14} aria-hidden="true" /> Enregistrer
            </button>
            <button type="button" className="btn btn-sm btn-secondary" onClick={() => setEdition(false)}>
              <X size={14} aria-hidden="true" /> Annuler
            </button>
          </div>
        </td>
      </tr>
    );
  }

  return (
    <tr>
      <td className="font-medium">{page.titre}</td>
      <td className="max-w-sm truncate text-gray-600">{page.url}</td>
      <td className="pr-1">
        <a className="btn btn-sm btn-secondary w-full" href={page.url} target="_blank" rel="noopener noreferrer">
          <ExternalLink size={14} aria-hidden="true" /> Ouvrir
        </a>
      </td>
      <td className="px-1">
        <button type="button" className="btn btn-sm btn-secondary w-full" onClick={ouvrirEdition} aria-label={`Modifier ${page.titre}`}>
          <Pencil size={14} aria-hidden="true" /> Modifier
        </button>
      </td>
      <td className="pl-1">
        <button
          type="button"
          className="btn btn-sm btn-danger w-full"
          onClick={supprimerPage}
          disabled={supprimer.isPending}
          aria-label={`Supprimer ${page.titre}`}
        >
          <Trash2 size={14} aria-hidden="true" /> Supprimer
        </button>
      </td>
    </tr>
  );
}

function FormulaireAjoutPage({ projetId }: { projetId: string }) {
  const ajouter = useAjouterPage(projetId);
  const [url, setUrl] = useState('');
  const [titre, setTitre] = useState('');

  const soumettre = (event: FormEvent) => {
    event.preventDefault();
    ajouter.mutate(
      { url, titre },
      {
        onSuccess: () => {
          setUrl('');
          setTitre('');
        },
      },
    );
  };

  return (
    <form onSubmit={soumettre} className="grid grid-cols-1 items-end gap-4 lg:grid-cols-3">
      <div className="field">
        <label htmlFor="page-titre">Titre de la page</label>
        <input id="page-titre" required value={titre} onChange={(e) => setTitre(e.target.value)} />
      </div>
      <div className="field">
        <label htmlFor="page-url">URL de la page</label>
        <input id="page-url" type="url" required placeholder="https://exemple.fr/contact" value={url} onChange={(e) => setUrl(e.target.value)} />
      </div>
      <button className="btn" type="submit" disabled={ajouter.isPending}>
        <Plus size={16} aria-hidden="true" /> Ajouter la page
      </button>
    </form>
  );
}

function ConstatsSection({ projetId }: { projetId: string }) {
  const { data: constats } = useConstats(projetId);
  const definir = useDefinirStatut(projetId);
  const [filtre, setFiltre] = useState<Statut | 'tous'>('tous');

  if (!constats || constats.length === 0) {
    return (
      <section className="space-y-3" aria-labelledby="constats-titre">
        <h2 id="constats-titre">Constats</h2>
        <p className="text-gray-500">Aucun constat. Lancez un scan pour les générer.</p>
      </section>
    );
  }

  const comptes = constats.reduce<Record<string, number>>((acc, c) => {
    acc[c.statut] = (acc[c.statut] ?? 0) + 1;

    return acc;
  }, {});
  const affiches = constats.filter((c) => 'tous' === filtre || c.statut === filtre);

  return (
    <section className="space-y-3" aria-labelledby="constats-titre">
      <h2 id="constats-titre">Constats ({constats.length})</h2>
      <div className="field max-w-xs">
        <label htmlFor="filtre-statut">Filtrer par statut</label>
        <select id="filtre-statut" value={filtre} onChange={(e) => setFiltre(e.target.value as Statut | 'tous')}>
          <option value="tous">Tous ({constats.length})</option>
          <option value="non_conforme">Non conformes ({comptes['non_conforme'] ?? 0})</option>
          <option value="conforme">Conformes ({comptes['conforme'] ?? 0})</option>
          <option value="non_applicable">Non applicables ({comptes['non_applicable'] ?? 0})</option>
          <option value="non_teste">Non testés ({comptes['non_teste'] ?? 0})</option>
        </select>
      </div>
      <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <table className="table">
          <caption className="sr-only">Constats de conformité par critère et page</caption>
          <thead>
            <tr>
              <th scope="col">Critère</th>
              <th scope="col">Page</th>
              <th scope="col">Statut</th>
              <th scope="col">Source</th>
              <th scope="col">Statuer</th>
            </tr>
          </thead>
          <tbody>
            {affiches.map((constat) => (
              <tr key={constat.id}>
                <td className="font-medium">{constat.critereNumero}</td>
                <td className="max-w-xs truncate text-gray-600">{constat.pageUrl}</td>
                <td>
                  <span className={`badge badge-${constat.statut}`}>{LIBELLES[constat.statut]}</span>
                </td>
                <td className="text-gray-600">{constat.source}</td>
                <td>
                  <label className="sr-only" htmlFor={`statut-${constat.id}`}>
                    Statut du critère {constat.critereNumero} pour {constat.pageUrl}
                  </label>
                  <select
                    id={`statut-${constat.id}`}
                    className="input"
                    value={constat.statut}
                    onChange={(e) =>
                      definir.mutate({
                        pageUrl: constat.pageUrl,
                        critereNumero: constat.critereNumero,
                        statut: e.target.value as Statut,
                      })
                    }
                  >
                    {(Object.keys(LIBELLES) as Statut[]).map((statut) => (
                      <option key={statut} value={statut}>
                        {LIBELLES[statut]}
                      </option>
                    ))}
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  );
}
