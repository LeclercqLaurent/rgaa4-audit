import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { axe } from 'jest-axe';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ProjetsPage from './ProjetsPage';

function renderPage() {
  const client = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return render(
    <QueryClientProvider client={client}>
      <MemoryRouter>
        <ProjetsPage />
      </MemoryRouter>
    </QueryClientProvider>,
  );
}

const projets = [{ id: '1', nom: 'Mairie de Démo', client: 'Mairie', urlReference: 'https://exemple.fr', dateCreation: '', pages: [] }];

describe('ProjetsPage', () => {
  beforeEach(() => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => ({ ok: true, status: 200, json: async () => projets })),
    );
  });

  afterEach(() => {
    vi.unstubAllGlobals();
    vi.restoreAllMocks();
  });

  it('affiche le titre, le formulaire et la liste des projets', async () => {
    renderPage();

    expect(screen.getByRole('heading', { level: 1, name: /Projets d'audit RGAA/i })).toBeInTheDocument();
    expect(screen.getByLabelText('Nom du projet')).toBeInTheDocument();
    expect(screen.getByLabelText('Client')).toBeInTheDocument();
    expect(screen.getByLabelText('URL de référence')).toBeInTheDocument();
    expect(await screen.findByRole('link', { name: /Mairie de Démo/ })).toBeInTheDocument();
  });

  it('saisit le formulaire de création', async () => {
    renderPage();
    await userEvent.type(screen.getByLabelText('Nom du projet'), 'Nouveau');

    expect(screen.getByLabelText('Nom du projet')).toHaveValue('Nouveau');
  });

  it("ne présente aucune violation d'accessibilité", async () => {
    const { container } = renderPage();
    await screen.findByRole('link', { name: /Mairie de Démo/ });

    expect(await axe(container)).toHaveNoViolations();
  });
});
