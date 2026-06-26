import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import { BrowserRouter, Link, Route, Routes } from 'react-router-dom';
import { getJeton, setJeton } from './api';
import LoginPage from './pages/LoginPage';
import ProjetsPage from './pages/ProjetsPage';
import ProjetPage from './pages/ProjetPage';

const queryClient = new QueryClient({
  defaultOptions: { queries: { refetchOnWindowFocus: false } },
});

export default function App() {
  const [authentifie, setAuthentifie] = useState<boolean>(() => null !== getJeton());

  const deconnexion = () => {
    setJeton(null);
    queryClient.clear();
    setAuthentifie(false);
  };

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <a className="skip-link" href="#contenu">
          Aller au contenu
        </a>
        <header className="entete">
          <Link to="/" className="entete__logo">
            Audit RGAA 4
          </Link>
          {authentifie && (
            <button type="button" className="entete__deconnexion" onClick={deconnexion}>
              Se déconnecter
            </button>
          )}
        </header>
        <main id="contenu" className="contenu">
          {authentifie ? (
            <Routes>
              <Route path="/" element={<ProjetsPage />} />
              <Route path="/projets/:id" element={<ProjetPage />} />
            </Routes>
          ) : (
            <LoginPage onConnecte={() => setAuthentifie(true)} />
          )}
        </main>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
