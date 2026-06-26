import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { axe } from 'jest-axe';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import LoginPage from './LoginPage';

describe('LoginPage', () => {
  beforeEach(() => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async () => ({ ok: true, status: 200, json: async () => ({ token: 'jeton-test' }) })),
    );
  });

  afterEach(() => {
    vi.unstubAllGlobals();
    localStorage.clear();
  });

  it('soumet les identifiants et signale la connexion', async () => {
    const onConnecte = vi.fn();
    render(<LoginPage onConnecte={onConnecte} />);

    await userEvent.type(screen.getByLabelText('Adresse e-mail'), 'auditeur@rgaa.test');
    await userEvent.type(screen.getByLabelText('Mot de passe'), 'motdepasse-tres-long-2026');
    await userEvent.click(screen.getByRole('button', { name: /Se connecter/ }));

    await waitFor(() => expect(onConnecte).toHaveBeenCalledOnce());
  });

  it("ne présente aucune violation d'accessibilité", async () => {
    const { container } = render(<LoginPage onConnecte={() => {}} />);

    expect(await axe(container)).toHaveNoViolations();
  });
});
