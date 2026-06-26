import {
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';

const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8082';

const CLE_JETON = 'rgaa_jwt';
const CLE_REFRESH = 'rgaa_refresh';
let jeton: string | null = localStorage.getItem(CLE_JETON);
let refresh: string | null = localStorage.getItem(CLE_REFRESH);

export function getJeton(): string | null {
  return jeton;
}

export function setJeton(valeur: string | null): void {
  jeton = valeur;
  if (valeur) {
    localStorage.setItem(CLE_JETON, valeur);
  } else {
    localStorage.removeItem(CLE_JETON);
  }
}

function setRefresh(valeur: string | null): void {
  refresh = valeur;
  if (valeur) {
    localStorage.setItem(CLE_REFRESH, valeur);
  } else {
    localStorage.removeItem(CLE_REFRESH);
  }
}

export function purgerSession(): void {
  setJeton(null);
  setRefresh(null);
}

type Jetons = { token: string; refresh_token?: string };

export async function connexion(email: string, motDePasse: string): Promise<void> {
  const reponse = await fetch(`${BASE}/api/login_check`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, motDePasse }),
  });

  if (!reponse.ok) {
    throw new Error('Identifiants invalides.');
  }

  const data = (await reponse.json()) as Jetons;
  setJeton(data.token);
  setRefresh(data.refresh_token ?? null);
}

async function rafraichir(): Promise<boolean> {
  if (!refresh) {
    return false;
  }

  const reponse = await fetch(`${BASE}/api/token/refresh`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ refresh_token: refresh }),
  });

  if (!reponse.ok) {
    purgerSession();

    return false;
  }

  const data = (await reponse.json()) as Jetons;
  setJeton(data.token);
  setRefresh(data.refresh_token ?? null);

  return true;
}

export type Page = { id: string; url: string; titre: string };

export type DernierScan = { statut: Scan['statut']; date: string };

export type Projet = {
  id: string;
  nom: string;
  client: string;
  urlReference: string;
  dateCreation: string;
  pages: Page[];
  dernierScan: DernierScan | null;
};

export type ScanPageResume = {
  url: string;
  violations: number;
  passes: number;
  incomplete: number;
  inapplicable: number;
};

export type Scan = {
  id: string;
  projetId: string;
  statut: 'pending' | 'running' | 'done' | 'failed';
  dateCreation: string;
  dateFin: string | null;
  erreur: string | null;
  pages: ScanPageResume[];
};

export type Preuve = {
  regle: string;
  impact: string | null;
  cible: string;
  extraitHtml: string;
  resume: string | null;
  aide: string;
};

export type Statut = 'conforme' | 'non_conforme' | 'non_applicable' | 'non_teste';

export type Constat = {
  id: string;
  projetId: string;
  pageUrl: string;
  critereNumero: string;
  statut: Statut;
  source: 'auto' | 'manuel';
  commentaire: string | null;
  preuves: Preuve[];
};

export type Taux = {
  global: number | null;
  parThematique: Record<string, number>;
  conformes: number;
  nonConformes: number;
  nonApplicables: number;
  nonTestes: number;
};

async function fetchAuthentifie(path: string, init?: RequestInit, reessai = false): Promise<Response> {
  const response = await fetch(`${BASE}${path}`, {
    ...init,
    headers: {
      Accept: 'application/json',
      ...(jeton ? { Authorization: `Bearer ${jeton}` } : {}),
      ...(init?.headers ?? {}),
    },
  });

  // Jeton expiré : on tente un renouvellement silencieux, puis on rejoue une fois.
  if (401 === response.status && !reessai && (await rafraichir())) {
    return fetchAuthentifie(path, init, true);
  }

  if (401 === response.status) {
    purgerSession();
  }

  return response;
}

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetchAuthentifie(path, {
    ...init,
    headers: { 'Content-Type': 'application/json', ...(init?.headers ?? {}) },
  });

  if (!response.ok) {
    throw new Error(`Requête ${path} : HTTP ${response.status}`);
  }

  return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
}

/**
 * Ouvre le rapport (HTML ou PDF) dans un nouvel onglet. L'endpoint étant
 * protégé, on télécharge avec le JWT puis on ouvre le blob obtenu.
 */
export async function ouvrirRapport(projetId: string, pdf: boolean): Promise<void> {
  const response = await fetchAuthentifie(`/api/projets/${projetId}/rapport${pdf ? '.pdf' : ''}`, {
    headers: { Accept: pdf ? 'application/pdf' : 'text/html' },
  });

  if (!response.ok) {
    throw new Error('Rapport indisponible.');
  }

  const url = URL.createObjectURL(await response.blob());
  window.open(url, '_blank', 'noopener');
  setTimeout(() => URL.revokeObjectURL(url), 60_000);
}

export function useProjets() {
  return useQuery({ queryKey: ['projets'], queryFn: () => request<Projet[]>('/api/projets') });
}

export function useProjet(id: string) {
  return useQuery({ queryKey: ['projet', id], queryFn: () => request<Projet>(`/api/projets/${id}`) });
}

export function useConstats(projetId: string) {
  return useQuery({ queryKey: ['constats', projetId], queryFn: () => request<Constat[]>(`/api/projets/${projetId}/constats`) });
}

export function useTaux(projetId: string) {
  return useQuery({ queryKey: ['taux', projetId], queryFn: () => request<Taux>(`/api/projets/${projetId}/taux`) });
}

export function useScans(projetId: string) {
  return useQuery({
    queryKey: ['scans', projetId],
    queryFn: () => request<Scan[]>(`/api/projets/${projetId}/scans`),
    refetchInterval: (query) =>
      (query.state.data ?? []).some((s) => s.statut === 'pending' || s.statut === 'running') ? 2000 : false,
  });
}

export function useCreerProjet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { nom: string; client: string; urlReference: string }) =>
      request<Projet>('/api/projets', { method: 'POST', body: JSON.stringify(body) }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projets'] }),
  });
}

export function useModifierProjet(id: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { nom: string; client: string; urlReference: string }) =>
      request<Projet>(`/api/projets/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/merge-patch+json' },
        body: JSON.stringify(body),
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['projet', id] });
      qc.invalidateQueries({ queryKey: ['projets'] });
    },
  });
}

export function useSupprimerProjet() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: string) => request<void>(`/api/projets/${id}`, { method: 'DELETE' }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['projets'] }),
  });
}

export function useAjouterPage(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { url: string; titre: string }) =>
      request<Projet>(`/api/projets/${projetId}/pages`, { method: 'POST', body: JSON.stringify(body) }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['projet', projetId] });
      qc.invalidateQueries({ queryKey: ['projets'] });
    },
  });
}

export function useModifierPage(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (p: { pageId: string; url: string; titre: string }) =>
      request<void>(`/api/projets/${projetId}/pages/${p.pageId}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/merge-patch+json' },
        body: JSON.stringify({ url: p.url, titre: p.titre }),
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['projet', projetId] });
      qc.invalidateQueries({ queryKey: ['projets'] });
    },
  });
}

export function useSupprimerPage(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (pageId: string) => request<void>(`/api/projets/${projetId}/pages/${pageId}`, { method: 'DELETE' }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['projet', projetId] });
      qc.invalidateQueries({ queryKey: ['projets'] });
    },
  });
}

export function useLancerScan(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => request<Scan>(`/api/projets/${projetId}/scans`, { method: 'POST', body: '{}' }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['scans', projetId] }),
  });
}

export function useConsommerScans(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => request<{ traites: number }>('/api/scans/consommer', { method: 'POST', body: '{}' }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['scans', projetId] });
      qc.invalidateQueries({ queryKey: ['constats', projetId] });
      qc.invalidateQueries({ queryKey: ['taux', projetId] });
    },
  });
}

export function useDefinirStatut(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (body: { pageUrl: string; critereNumero: string; statut: Statut; commentaire?: string }) =>
      request<void>(`/api/projets/${projetId}/constats`, { method: 'PUT', body: JSON.stringify(body) }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['constats', projetId] });
      qc.invalidateQueries({ queryKey: ['taux', projetId] });
    },
  });
}
