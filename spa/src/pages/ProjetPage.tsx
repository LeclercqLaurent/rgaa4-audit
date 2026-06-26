import { useQueryClient } from '@tanstack/react-query';
import { FormEvent, useEffect, useRef, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  Page,
  Statut,
  ouvrirRapport,
  useAjouterPage,
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

const LIBELLES: Record<Statut, string> = {
  conforme: 'Conforme',
  non_conforme: 'Non conforme',
  non_applicable: 'Non applicable',
  non_teste: 'Non testé',
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

      <PagesSection projetId={projetId} pages={projet.pages} />
      <ScanSection projetId={projetId} />
      <TauxSection projetId={projetId} />
      <ConstatsSection projetId={projetId} />

      <section aria-labelledby="rapport-titre">
        <h2 id="rapport-titre">Rapport</h2>
        <div className="actions">
          <button type="button" className="bouton" onClick={() => void ouvrirRapport(projetId, false)}>
            Voir le rapport (HTML)
          </button>
          <button type="button" className="bouton" onClick={() => void ouvrirRapport(projetId, true)}>
            Télécharger le PDF
          </button>
        </div>
      </section>
    </>
  );
}

function PagesSection({ projetId, pages }: { projetId: string; pages: Page[] }) {
  return (
    <section aria-labelledby="pages-titre">
      <h2 id="pages-titre">Pages à auditer (échantillon)</h2>
      <p className="aide">
        Les pages représentatives du site qui seront analysées au lancement du scan. En RGAA, l'audit porte sur un
        échantillon de pages, pas sur le site entier.
      </p>
      <table>
        <caption className="visuellement-cache">Pages de l'échantillon</caption>
        <thead>
          <tr>
            <th scope="col">Titre</th>
            <th scope="col">URL</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          {pages.length === 0 ? (
            <tr>
              <td colSpan={3}>Aucune page. Ajoutez-en une ci-dessous.</td>
            </tr>
          ) : (
            pages.map((page) => <LignePage key={page.id} projetId={projetId} page={page} />)
          )}
        </tbody>
      </table>
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
        <td className="cellule-edition">
          <input aria-label="Titre" value={titre} onChange={(e) => setTitre(e.target.value)} />
        </td>
        <td className="cellule-edition">
          <input aria-label="URL" type="url" value={url} onChange={(e) => setUrl(e.target.value)} />
        </td>
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
      <td>{page.titre}</td>
      <td>{page.url}</td>
      <td>
        <div className="actions">
          <a className="bouton bouton--petit" href={page.url} target="_blank" rel="noopener noreferrer">
            Ouvrir
          </a>
          <button type="button" className="bouton bouton--petit" onClick={ouvrirEdition} aria-label={`Modifier ${page.titre}`}>
            Modifier
          </button>
          <button
            type="button"
            className="bouton bouton--petit bouton--danger"
            onClick={supprimerPage}
            disabled={supprimer.isPending}
            aria-label={`Supprimer ${page.titre}`}
          >
            Supprimer
          </button>
        </div>
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
    <form onSubmit={soumettre} className="grille grille--2">
      <div className="champ">
        <label htmlFor="page-titre">Titre de la page</label>
        <input id="page-titre" required value={titre} onChange={(e) => setTitre(e.target.value)} />
      </div>
      <div className="champ">
        <label htmlFor="page-url">URL de la page</label>
        <input id="page-url" type="url" required placeholder="https://exemple.fr/contact" value={url} onChange={(e) => setUrl(e.target.value)} />
      </div>
      <div className="champ" style={{ alignSelf: 'end' }}>
        <button className="bouton" type="submit" disabled={ajouter.isPending}>
          Ajouter la page
        </button>
      </div>
    </form>
  );
}

function ScanSection({ projetId }: { projetId: string }) {
  const { data: scans } = useScans(projetId);
  const lancer = useLancerScan(projetId);
  const consommer = useConsommerScans(projetId);
  const dernier = scans?.[0];

  return (
    <section className="carte" aria-labelledby="scan-titre">
      <h2 id="scan-titre">Scan automatique</h2>
      <div className="actions">
        <button className="bouton" type="button" onClick={() => lancer.mutate()} disabled={lancer.isPending}>
          Lancer un scan
        </button>
        <button className="bouton" type="button" onClick={() => consommer.mutate()} disabled={consommer.isPending}>
          {consommer.isPending ? 'Traitement…' : 'Traiter les scans en attente'}
        </button>
      </div>
      {consommer.isSuccess && <p aria-live="polite">{consommer.data.traites} message(s) traité(s).</p>}
      {dernier && (
        <p aria-live="polite">
          Dernier scan : <strong>{LIBELLE_SCAN[dernier.statut] ?? dernier.statut}</strong> (
          {new Date(dernier.dateCreation).toLocaleString('fr-FR')}){dernier.erreur && <> — {dernier.erreur}</>}
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
      <div className="cards">
        <div className="card card--principal">
          <span className="card__valeur">{null === taux.global ? 'Non évalué' : `${Math.round(taux.global * 1000) / 10} %`}</span>
          <span className="card__label">Conformité globale</span>
        </div>
        <div className="card">
          <span className="card__valeur statut--conforme">{taux.conformes}</span>
          <span className="card__label">Conformes</span>
        </div>
        <div className="card">
          <span className="card__valeur statut--non_conforme">{taux.nonConformes}</span>
          <span className="card__label">Non conformes</span>
        </div>
        <div className="card">
          <span className="card__valeur">{taux.nonApplicables}</span>
          <span className="card__label">Non applicables</span>
        </div>
        <div className="card">
          <span className="card__valeur">{taux.nonTestes}</span>
          <span className="card__label">Non testés</span>
        </div>
      </div>
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
        <caption className="visuellement-cache">Constats de conformité par critère et page</caption>
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
