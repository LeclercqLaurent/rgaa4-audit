import { FormEvent, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import {
  Projet,
  Statut,
  rapportUrl,
  useAjouterPage,
  useConstats,
  useDefinirStatut,
  useLancerScan,
  useModifierProjet,
  useProjet,
  useScans,
  useSupprimerProjet,
  useTaux,
} from '../api';

const LIBELLES: Record<Statut, string> = {
  conforme: 'Conforme',
  non_conforme: 'Non conforme',
  non_applicable: 'Non applicable',
  non_teste: 'Non testé',
};

export default function ProjetPage() {
  const { id } = useParams();
  const projetId = id ?? '';
  const { data: projet, isLoading } = useProjet(projetId);

  if (isLoading) {
    return <p>Chargement…</p>;
  }

  if (!projet) {
    return <p role="alert">Projet introuvable.</p>;
  }

  return (
    <>
      <p>
        <Link to="/">← Tous les projets</Link>
      </p>
      <h1>{projet.nom}</h1>
      <p>
        {projet.client} — <a href={projet.urlReference}>{projet.urlReference}</a>
      </p>

      <ProjetActions projet={projet} />

      <div className="grille grille--2">
        <PagesSection projetId={projetId} pages={projet.pages} />
        <ScanSection projetId={projetId} />
      </div>

      <TauxSection projetId={projetId} />
      <ConstatsSection projetId={projetId} />

      <h2>Rapport</h2>
      <p>
        <a href={rapportUrl(projetId)}>Voir le rapport (HTML)</a> ·{' '}
        <a href={rapportUrl(projetId, true)}>Télécharger le PDF</a>
      </p>
    </>
  );
}

function ProjetActions({ projet }: { projet: Projet }) {
  const navigate = useNavigate();
  const modifier = useModifierProjet(projet.id);
  const supprimer = useSupprimerProjet();
  const [edition, setEdition] = useState(false);
  const [nom, setNom] = useState(projet.nom);
  const [client, setClient] = useState(projet.client);
  const [urlReference, setUrlReference] = useState(projet.urlReference);

  const ouvrirEdition = () => {
    setNom(projet.nom);
    setClient(projet.client);
    setUrlReference(projet.urlReference);
    setEdition(true);
  };

  const enregistrer = (event: FormEvent) => {
    event.preventDefault();
    modifier.mutate({ nom, client, urlReference }, { onSuccess: () => setEdition(false) });
  };

  const supprimerProjet = () => {
    if (window.confirm('Supprimer ce projet et toutes ses données (pages, scans, constats) ?')) {
      supprimer.mutate(projet.id, { onSuccess: () => navigate('/') });
    }
  };

  if (!edition) {
    return (
      <p>
        <button type="button" className="bouton" onClick={ouvrirEdition}>
          Modifier
        </button>{' '}
        <button type="button" className="bouton bouton--danger" onClick={supprimerProjet} disabled={supprimer.isPending}>
          Supprimer le projet
        </button>
      </p>
    );
  }

  return (
    <section className="carte" aria-labelledby="edit-titre">
      <h2 id="edit-titre">Modifier le projet</h2>
      <form onSubmit={enregistrer}>
        <div className="champ">
          <label htmlFor="edit-nom">Nom du projet</label>
          <input id="edit-nom" required value={nom} onChange={(e) => setNom(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="edit-client">Client</label>
          <input id="edit-client" required value={client} onChange={(e) => setClient(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="edit-url">URL de référence</label>
          <input id="edit-url" type="url" required value={urlReference} onChange={(e) => setUrlReference(e.target.value)} />
        </div>
        <button className="bouton" type="submit" disabled={modifier.isPending}>
          Enregistrer
        </button>{' '}
        <button type="button" className="bouton" onClick={() => setEdition(false)}>
          Annuler
        </button>
      </form>
    </section>
  );
}

function PagesSection({ projetId, pages }: { projetId: string; pages: { id: string; url: string; titre: string }[] }) {
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
    <section className="carte" aria-labelledby="pages-titre">
      <h2 id="pages-titre">Échantillon de pages</h2>
      <ul>
        {pages.map((page) => (
          <li key={page.id}>
            {page.titre} — <a href={page.url}>{page.url}</a>
          </li>
        ))}
      </ul>
      <form onSubmit={soumettre}>
        <div className="champ">
          <label htmlFor="page-url">URL de la page</label>
          <input id="page-url" type="url" required value={url} onChange={(e) => setUrl(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="page-titre">Titre</label>
          <input id="page-titre" required value={titre} onChange={(e) => setTitre(e.target.value)} />
        </div>
        <button className="bouton" type="submit" disabled={ajouter.isPending}>
          Ajouter la page
        </button>
      </form>
    </section>
  );
}

function ScanSection({ projetId }: { projetId: string }) {
  const { data: scans } = useScans(projetId);
  const lancer = useLancerScan(projetId);
  const dernier = scans?.[0];

  return (
    <section className="carte" aria-labelledby="scan-titre">
      <h2 id="scan-titre">Scan automatique</h2>
      <button className="bouton" type="button" onClick={() => lancer.mutate()} disabled={lancer.isPending}>
        Lancer un scan
      </button>
      {dernier && (
        <p aria-live="polite">
          Dernier scan : <strong>{dernier.statut}</strong> (
          {new Date(dernier.dateCreation).toLocaleString('fr-FR')})
          {dernier.erreur && <> — {dernier.erreur}</>}
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

  return (
    <section aria-labelledby="taux-titre">
      <h2 id="taux-titre">Taux de conformité</h2>
      <p className="taux">{taux.global === null ? 'Non évalué' : `${Math.round(taux.global * 1000) / 10} %`}</p>
      <p>
        {taux.conformes} conforme(s), {taux.nonConformes} non conforme(s), {taux.nonApplicables} non applicable(s),{' '}
        {taux.nonTestes} non testé(s).
      </p>
    </section>
  );
}

function ConstatsSection({ projetId }: { projetId: string }) {
  const { data: constats } = useConstats(projetId);
  const definir = useDefinirStatut(projetId);
  const [filtre, setFiltre] = useState<Statut | 'tous'>('tous');

  if (!constats || constats.length === 0) {
    return (
      <section aria-labelledby="constats-titre">
        <h2 id="constats-titre">Constats</h2>
        <p>Aucun constat. Lancez un scan pour les générer.</p>
      </section>
    );
  }

  const comptes = constats.reduce<Record<string, number>>((acc, c) => {
    acc[c.statut] = (acc[c.statut] ?? 0) + 1;

    return acc;
  }, {});
  const affiches = constats.filter((c) => 'tous' === filtre || c.statut === filtre);

  return (
    <section aria-labelledby="constats-titre">
      <h2 id="constats-titre">Constats ({constats.length})</h2>
      <p>
        {comptes['conforme'] ?? 0} conforme(s), {comptes['non_conforme'] ?? 0} non conforme(s),{' '}
        {comptes['non_applicable'] ?? 0} non applicable(s), {comptes['non_teste'] ?? 0} non testé(s).
      </p>
      <div className="champ" style={{ maxWidth: 320 }}>
        <label htmlFor="filtre-statut">Filtrer par statut</label>
        <select id="filtre-statut" value={filtre} onChange={(e) => setFiltre(e.target.value as Statut | 'tous')}>
          <option value="tous">Tous ({constats.length})</option>
          <option value="non_conforme">Non conformes ({comptes['non_conforme'] ?? 0})</option>
          <option value="conforme">Conformes ({comptes['conforme'] ?? 0})</option>
          <option value="non_applicable">Non applicables ({comptes['non_applicable'] ?? 0})</option>
          <option value="non_teste">Non testés ({comptes['non_teste'] ?? 0})</option>
        </select>
      </div>
      <table>
        <caption>Constats de conformité par critère et page</caption>
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
              <td>{constat.critereNumero}</td>
              <td>{constat.pageUrl}</td>
              <td className={`statut--${constat.statut}`}>{LIBELLES[constat.statut]}</td>
              <td>{constat.source}</td>
              <td>
                <label className="visuellement-cache" htmlFor={`statut-${constat.id}`}>
                  Statut du critère {constat.critereNumero} pour {constat.pageUrl}
                </label>
                <select
                  id={`statut-${constat.id}`}
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
    </section>
  );
}
