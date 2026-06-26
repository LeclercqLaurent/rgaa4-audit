import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { BrowserRouter, Link, Route, Routes } from 'react-router-dom';
import ProjetsPage from './pages/ProjetsPage';
import ProjetPage from './pages/ProjetPage';

const queryClient = new QueryClient({
  defaultOptions: { queries: { refetchOnWindowFocus: false } },
});

export default function App() {
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
        </header>
        <main id="contenu" className="contenu">
          <Routes>
            <Route path="/" element={<ProjetsPage />} />
            <Route path="/projets/:id" element={<ProjetPage />} />
          </Routes>
        </main>
      </BrowserRouter>
    </QueryClientProvider>
  );
}
