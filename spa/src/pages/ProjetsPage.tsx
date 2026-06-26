import { ArrowRight, Check, Download, Pencil, Plus, Trash2, X } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { Link } from 'react-router-dom';
import { DernierScan, LIBELLE_TYPE, Projet, TypeAudit, ouvrirRapport, useCreerProjet, useModifierProjet, useProjets, useSupprimerProjet } from '../api';
import { SkeletonTable } from '../components/Skeleton';

const LIBELLE_STATUT: Record<DernierScan['statut'], string> = {
  pending: 'En attente',
  running: 'En cours',
  done: 'Terminé',
  failed: 'Échoué',
};

function ResumeScan({ scan }: { scan: DernierScan | null }) {
  if (!scan) {
    return <span className="text-gray-400">—</span>;
  }

  return (
    <span>
      {LIBELLE_STATUT[scan.statut]} <span className="text-xs text-gray-500">({new Date(scan.date).toLocaleDateString('fr-FR')})</span>
    </span>
  );
}

export default function ProjetsPage() {
  const { data: projets, isLoading, isError } = useProjets();
  const [creation, setCreation] = useState(false);

  return (
    <>
      <div className="flex flex-wrap items-center justify-between gap-4">
        <h1>Projets d'audit RGAA</h1>
        <button type="button" className="btn" onClick={() => setCreation((v) => !v)} aria-expanded={creation}>
          {creation ? <X size={16} aria-hidden="true" /> : <Plus size={16} aria-hidden="true" />}
          {creation ? 'Fermer' : 'Nouveau projet'}
        </button>
      </div>

      {creation && <FormulaireCreation onCree={() => setCreation(false)} />}

      {isLoading && <SkeletonTable lignes={5} colonnes={6} />}
      {isError && <p role="alert">Impossible de charger les projets.</p>}
      {projets && projets.length === 0 && <p className="text-gray-500">Aucun projet. Créez-en un avec « Nouveau projet ».</p>}

      {projets && projets.length > 0 && (
        <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
          <table className="table">
            <caption className="sr-only">Liste des projets d'audit</caption>
            <thead>
              <tr>
                <th scope="col">Nom</th>
                <th scope="col">Client</th>
                <th scope="col">Cible</th>
                <th scope="col">Pages</th>
                <th scope="col">Dernier scan</th>
                <th scope="col" colSpan={4}>
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              {projets.map((projet) => (
                <LigneProjet key={projet.id} projet={projet} />
              ))}
            </tbody>
          </table>
        </div>
      )}
    </>
  );
}

function FormulaireCreation({ onCree }: { onCree: () => void }) {
  const creer = useCreerProjet();
  const [nom, setNom] = useState('');
  const [client, setClient] = useState('');
  const [type, setType] = useState<TypeAudit>('rgaa');
  const [cible, setCible] = useState('');

  const estRgaa = 'rgaa' === type;

  const soumettre = (event: FormEvent) => {
    event.preventDefault();
    creer.mutate({ nom, client, type, cible }, { onSuccess: onCree });
  };

  return (
    <section className="card space-y-4" aria-labelledby="creer-titre">
      <h2 id="creer-titre">Nouveau projet</h2>
      <form onSubmit={soumettre} className="grid grid-cols-1 items-end gap-4 lg:grid-cols-2">
        <div className="field">
          <label htmlFor="nom">Nom du projet</label>
          <input id="nom" required value={nom} onChange={(e) => setNom(e.target.value)} />
        </div>
        <div className="field">
          <label htmlFor="client">Client</label>
          <input id="client" required value={client} onChange={(e) => setClient(e.target.value)} />
        </div>
        <div className="field">
          <label htmlFor="type">Type d'audit</label>
          <select id="type" value={type} onChange={(e) => setType(e.target.value as TypeAudit)}>
            <option value="rgaa">{LIBELLE_TYPE.rgaa} — accessibilité d'un site</option>
            <option value="complexite_php">{LIBELLE_TYPE.complexite_php} — complexité d'un code</option>
          </select>
        </div>
        <div className="field">
          <label htmlFor="cible">{estRgaa ? 'URL du site à auditer' : 'Chemin du code à analyser'}</label>
          <input
            id="cible"
            type={estRgaa ? 'url' : 'text'}
            required
            placeholder={estRgaa ? 'https://exemple.fr' : '/var/www/app/src'}
            value={cible}
            onChange={(e) => setCible(e.target.value)}
          />
        </div>
        <button className="btn" type="submit" disabled={creer.isPending}>
          <Plus size={16} aria-hidden="true" /> {creer.isPending ? 'Création…' : 'Créer le projet'}
        </button>
        {creer.isError && (
          <p role="alert" className="text-sm font-medium text-danger">
            La création a échoué.
          </p>
        )}
      </form>
    </section>
  );
}

function LigneProjet({ projet }: { projet: Projet }) {
  const modifier = useModifierProjet(projet.id);
  const supprimer = useSupprimerProjet();
  const [edition, setEdition] = useState(false);
  const [nom, setNom] = useState(projet.nom);
  const [client, setClient] = useState(projet.client);
  const [cible, setCible] = useState(projet.cible);
  const estRgaa = 'rgaa' === projet.type;

  const ouvrirEdition = () => {
    setNom(projet.nom);
    setClient(projet.client);
    setCible(projet.cible);
    setEdition(true);
  };

  const enregistrer = () => {
    modifier.mutate({ nom, client, cible }, { onSuccess: () => setEdition(false) });
  };

  const supprimerProjet = () => {
    if (window.confirm(`Supprimer le projet « ${projet.nom} » et toutes ses données ?`)) {
      supprimer.mutate(projet.id);
    }
  };

  if (edition) {
    return (
      <tr>
        <td>
          <input className="input" aria-label="Nom" value={nom} onChange={(e) => setNom(e.target.value)} />
        </td>
        <td>
          <input className="input" aria-label="Client" value={client} onChange={(e) => setClient(e.target.value)} />
        </td>
        <td>
          <input className="input" aria-label="Cible" type={estRgaa ? 'url' : 'text'} value={cible} onChange={(e) => setCible(e.target.value)} />
        </td>
        <td>{projet.pages.length}</td>
        <td>
          <ResumeScan scan={projet.dernierScan} />
        </td>
        <td colSpan={4}>
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
      <td className="font-medium">
        <Link to={`/projets/${projet.id}`}>{projet.nom}</Link>
        <span className="ml-2 inline-block rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{LIBELLE_TYPE[projet.type]}</span>
      </td>
      <td>{projet.client}</td>
      <td className="max-w-xs truncate text-gray-600">{projet.cible}</td>
      <td>{projet.pages.length}</td>
      <td>
        <ResumeScan scan={projet.dernierScan} />
      </td>
      <td className="pr-1">
        <Link className="btn btn-sm btn-secondary w-full" to={`/projets/${projet.id}`}>
          Ouvrir <ArrowRight size={14} aria-hidden="true" />
        </Link>
      </td>
      <td className="px-1">
        {projet.dernierScan?.statut === 'done' && (
          <button type="button" className="btn btn-sm btn-secondary w-full" onClick={() => void ouvrirRapport(projet.id, true)} aria-label={`Rapport PDF de ${projet.nom}`}>
            <Download size={14} aria-hidden="true" /> PDF
          </button>
        )}
      </td>
      <td className="px-1">
        <button type="button" className="btn btn-sm btn-secondary w-full" onClick={ouvrirEdition} aria-label={`Modifier ${projet.nom}`}>
          <Pencil size={14} aria-hidden="true" /> Modifier
        </button>
      </td>
      <td className="pl-1">
        <button
          type="button"
          className="btn btn-sm btn-danger w-full"
          onClick={supprimerProjet}
          disabled={supprimer.isPending}
          aria-label={`Supprimer ${projet.nom}`}
        >
          <Trash2 size={14} aria-hidden="true" /> Supprimer
        </button>
      </td>
    </tr>
  );
}
