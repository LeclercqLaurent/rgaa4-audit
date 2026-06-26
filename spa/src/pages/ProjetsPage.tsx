import { FormEvent, useState } from 'react';
import { Link } from 'react-router-dom';
import { Projet, useCreerProjet, useModifierProjet, useProjets, useSupprimerProjet } from '../api';

export default function ProjetsPage() {
  const { data: projets, isLoading, isError } = useProjets();
  const [creation, setCreation] = useState(false);

  return (
    <>
      <div className="barre-actions">
        <h1>Projets d'audit RGAA</h1>
        <button type="button" className="bouton" onClick={() => setCreation((v) => !v)} aria-expanded={creation}>
          {creation ? 'Fermer' : '+ Nouveau projet'}
        </button>
      </div>

      {creation && <FormulaireCreation onCree={() => setCreation(false)} />}

      {isLoading && <p>Chargement…</p>}
      {isError && <p role="alert">Impossible de charger les projets.</p>}
      {projets && projets.length === 0 && <p>Aucun projet. Créez-en un avec « Nouveau projet ».</p>}

      {projets && projets.length > 0 && (
        <table>
          <caption className="visuellement-cache">Liste des projets d'audit</caption>
          <thead>
            <tr>
              <th scope="col">Nom</th>
              <th scope="col">Client</th>
              <th scope="col">URL de référence</th>
              <th scope="col">Pages</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            {projets.map((projet) => (
              <LigneProjet key={projet.id} projet={projet} />
            ))}
          </tbody>
        </table>
      )}
    </>
  );
}

function FormulaireCreation({ onCree }: { onCree: () => void }) {
  const creer = useCreerProjet();
  const [nom, setNom] = useState('');
  const [client, setClient] = useState('');
  const [urlReference, setUrlReference] = useState('');

  const soumettre = (event: FormEvent) => {
    event.preventDefault();
    creer.mutate({ nom, client, urlReference }, { onSuccess: onCree });
  };

  return (
    <section className="carte" aria-labelledby="creer-titre">
      <h2 id="creer-titre">Nouveau projet</h2>
      <form onSubmit={soumettre} className="grille grille--2">
        <div className="champ">
          <label htmlFor="nom">Nom du projet</label>
          <input id="nom" required value={nom} onChange={(e) => setNom(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="client">Client</label>
          <input id="client" required value={client} onChange={(e) => setClient(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="url">URL de référence</label>
          <input id="url" type="url" required placeholder="https://exemple.fr" value={urlReference} onChange={(e) => setUrlReference(e.target.value)} />
        </div>
        <div className="champ" style={{ alignSelf: 'end' }}>
          <button className="bouton" type="submit" disabled={creer.isPending}>
            {creer.isPending ? 'Création…' : 'Créer le projet'}
          </button>
        </div>
        {creer.isError && (
          <p role="alert" className="statut--non_conforme">
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
  const [urlReference, setUrlReference] = useState(projet.urlReference);

  const ouvrirEdition = () => {
    setNom(projet.nom);
    setClient(projet.client);
    setUrlReference(projet.urlReference);
    setEdition(true);
  };

  const enregistrer = () => {
    modifier.mutate({ nom, client, urlReference }, { onSuccess: () => setEdition(false) });
  };

  const supprimerProjet = () => {
    if (window.confirm(`Supprimer le projet « ${projet.nom} » et toutes ses données ?`)) {
      supprimer.mutate(projet.id);
    }
  };

  if (edition) {
    return (
      <tr>
        <td className="cellule-edition">
          <input aria-label="Nom" value={nom} onChange={(e) => setNom(e.target.value)} />
        </td>
        <td className="cellule-edition">
          <input aria-label="Client" value={client} onChange={(e) => setClient(e.target.value)} />
        </td>
        <td className="cellule-edition">
          <input aria-label="URL de référence" type="url" value={urlReference} onChange={(e) => setUrlReference(e.target.value)} />
        </td>
        <td>{projet.pages.length}</td>
        <td>
          <div className="actions">
            <button type="button" className="bouton bouton--petit" onClick={enregistrer} disabled={modifier.isPending}>
              Enregistrer
            </button>
            <button type="button" className="bouton bouton--petit" onClick={() => setEdition(false)}>
              Annuler
            </button>
          </div>
        </td>
      </tr>
    );
  }

  return (
    <tr>
      <td>
        <Link to={`/projets/${projet.id}`}>{projet.nom}</Link>
      </td>
      <td>{projet.client}</td>
      <td>{projet.urlReference}</td>
      <td>{projet.pages.length}</td>
      <td>
        <div className="actions">
          <Link className="bouton bouton--petit" to={`/projets/${projet.id}`}>
            Ouvrir
          </Link>
          <button type="button" className="bouton bouton--petit" onClick={ouvrirEdition} aria-label={`Modifier ${projet.nom}`}>
            Modifier
          </button>
          <button
            type="button"
            className="bouton bouton--petit bouton--danger"
            onClick={supprimerProjet}
            disabled={supprimer.isPending}
            aria-label={`Supprimer ${projet.nom}`}
          >
            Supprimer
          </button>
        </div>
      </td>
    </tr>
  );
}
