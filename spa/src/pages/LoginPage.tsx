import { LogIn } from 'lucide-react';
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
    <section className="card mx-auto mt-12 max-w-md space-y-4" aria-labelledby="login-titre">
      <h1 id="login-titre">Connexion</h1>
      <form onSubmit={soumettre} className="space-y-4">
        <div className="field">
          <label htmlFor="email">Adresse e-mail</label>
          <input id="email" type="email" autoComplete="username" required value={email} onChange={(e) => setEmail(e.target.value)} />
        </div>
        <div className="field">
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
          <p role="alert" className="text-sm font-medium text-danger">
            {erreur}
          </p>
        )}
        <button className="btn w-full" type="submit" disabled={enCours}>
          <LogIn size={16} aria-hidden="true" /> {enCours ? 'Connexion…' : 'Se connecter'}
        </button>
      </form>
    </section>
  );
}
