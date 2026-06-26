import { FormEvent, useState } from 'react';
import { connexion } from '../api';

export default function LoginPage({ onConnecte }: { onConnecte: () => void }) {
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [erreur, setErreur] = useState<string | null>(null);
  const [enCours, setEnCours] = useState(false);

  const soumettre = async (event: FormEvent) => {
    event.preventDefault();
    setErreur(null);
    setEnCours(true);
    try {
      await connexion(email, motDePasse);
      onConnecte();
    } catch {
      setErreur('Identifiants invalides.');
    } finally {
      setEnCours(false);
    }
  };

  return (
    <section className="carte" aria-labelledby="login-titre" style={{ maxWidth: 420, margin: '48px auto' }}>
      <h1 id="login-titre">Connexion</h1>
      <form onSubmit={soumettre}>
        <div className="champ">
          <label htmlFor="email">Adresse e-mail</label>
          <input id="email" type="email" autoComplete="username" required value={email} onChange={(e) => setEmail(e.target.value)} />
        </div>
        <div className="champ">
          <label htmlFor="mdp">Mot de passe</label>
          <input
            id="mdp"
            type="password"
            autoComplete="current-password"
            required
            value={motDePasse}
            onChange={(e) => setMotDePasse(e.target.value)}
          />
        </div>
        {erreur && (
          <p role="alert" className="statut--non_conforme">
            {erreur}
          </p>
        )}
        <button className="bouton" type="submit" disabled={enCours}>
          {enCours ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>
    </section>
  );
}
