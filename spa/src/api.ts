import {
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';

const BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8082';

const CLE_JETON = 'rgaa_jwt';
let jeton: string | null = localStorage.getItem(CLE_JETON);

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

export async function connexion(email: string, motDePasse: string): Promise<void> {
  const reponse = await fetch(`${BASE}/api/login_check`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email, motDePasse }),
  });

  if (!reponse.ok) {
    throw new Error('Identifiants invalides.');
  }

  const data = (await reponse.json()) as { token: string };
  setJeton(data.token);
}

export type Page = { id: string; url: string; titre: string };

export type Projet = {
  id: string;
  nom: string;
  client: string;
  urlReference: string;
  dateCreation: string;
  pages: Page[];
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

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(`${BASE}${path}`, {
    ...init,
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(jeton ? { Authorization: `Bearer ${jeton}` } : {}),
      ...(init?.headers ?? {}),
    },
  });

  if (401 === response.status) {
    setJeton(null);
  }

  if (!response.ok) {
    throw new Error(`Requête ${path} : HTTP ${response.status}`);
  }

  return response.status === 204 ? (undefined as T) : ((await response.json()) as T);
}

export const rapportUrl = (projetId: string, pdf = false): string =>
  `${BASE}/api/projets/${projetId}/rapport${pdf ? '.pdf' : ''}`;

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

export function useLancerScan(projetId: string) {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => request<Scan>(`/api/projets/${projetId}/scans`, { method: 'POST', body: '{}' }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['scans', projetId] }),
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
