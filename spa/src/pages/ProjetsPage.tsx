import { FormEvent, useState } from 'react';
import { Link } from 'react-router-dom';
import { useCreerProjet, useProjets } from '../api';

export default function ProjetsPage() {
  const { data: projets, isLoading, isError } = useProjets();
  const creer = useCreerProjet();
  const [nom, setNom] = useState('');
  const [client, setClient] = useState('');
  const [urlReference, setUrlReference] = useState('');

  const soumettre = (event: FormEvent) => {
    event.preventDefault();
    creer.mutate(
      { nom, client, urlReference },
      {
        onSuccess: () => {
          setNom('');
          setClient('');
          setUrlReference('');
        },
      },
    );
  };

  return (
    <>
      <h1>Projets d'audit RGAA</h1>
      <div className="grille grille--2">
        <section className="carte" aria-labelledby="liste-titre">
          <h2 id="liste-titre">Projets</h2>
          {isLoading && <p>Chargement…</p>}
          {isError && <p role="alert">Impossible de charger les projets.</p>}
          {projets && projets.length === 0 && <p>Aucun projet pour le moment.</p>}
          <ul>
            {projets?.map((projet) => (
              <li key={projet.id}>
                <Link to={`/projets/${projet.id}`}>{projet.nom}</Link> — {projet.client}
              </li>
            ))}
          </ul>
        </section>

        <section className="carte" aria-labelledby="creer-titre">
          <h2 id="creer-titre">Nouveau projet</h2>
          <form onSubmit={soumettre}>
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
              <input
                id="url"
                type="url"
                required
                placeholder="https://exemple.fr"
                value={urlReference}
                onChange={(e) => setUrlReference(e.target.value)}
              />
            </div>
            <button className="bouton" type="submit" disabled={creer.isPending}>
              {creer.isPending ? 'Création…' : 'Créer le projet'}
            </button>
            {creer.isError && <p role="alert">La création a échoué.</p>}
          </form>
        </section>
      </div>
    </>
  );
}
