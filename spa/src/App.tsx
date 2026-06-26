import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { useState } from 'react';
import { BrowserRouter, Link, Route, Routes } from 'react-router-dom';
import { getJeton, purgerSession } from './api';
import LoginPage from './pages/LoginPage';
import ProjetsPage from './pages/ProjetsPage';
import ProjetPage from './pages/ProjetPage';

const queryClient = new QueryClient({
  defaultOptions: { queries: { refetchOnWindowFocus: false } },
});

export default function App() {
  const [authentifie, setAuthentifie] = useState<boolean>(() => null !== getJeton());

  const deconnexion = () => {
    purgerSession();
    queryClient.clear();
    setAuthentifie(false);
  };

  return (
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <a className="skip-link" href="#contenu">
          Aller au contenu
        </a>
        <header className="bg-brand text-white shadow">
          <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <Link to="/" className="text-lg font-bold text-white no-underline">
              Audit RGAA 4
            </Link>
            {authentifie && (
              <button type="button" className="btn btn-sm btn-secondary" onClick={deconnexion}>
                Se déconnecter
              </button>
            )}
          </div>
        </header>
        <main id="contenu" className="mx-auto max-w-5xl space-y-6 px-4 py-8">
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
