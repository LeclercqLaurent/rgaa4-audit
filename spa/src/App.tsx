import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { LogOut } from 'lucide-react';
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
          <div className="flex items-center justify-between px-6 py-3">
            <Link to="/" className="text-lg font-bold text-white no-underline">
              Auditor
            </Link>
            {authentifie && (
              <button type="button" className="btn btn-sm btn-secondary" onClick={deconnexion}>
                <LogOut size={16} aria-hidden="true" /> Se déconnecter
              </button>
            )}
          </div>
        </header>
        <main id="contenu" className="space-y-6 px-6 py-8">
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
