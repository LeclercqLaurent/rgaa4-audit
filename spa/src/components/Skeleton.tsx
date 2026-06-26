export function Skeleton({ className = '' }: { className?: string }) {
  return <span className={`block animate-pulse rounded bg-gray-200 ${className}`} aria-hidden="true" />;
}

/**
 * Squelette de tableau pendant le chargement.
 */
export function SkeletonTable({ lignes = 5, colonnes = 4 }: { lignes?: number; colonnes?: number }) {
  return (
    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm" role="status" aria-label="Chargement…">
      <Skeleton className="mb-4 h-5 w-1/3" />
      <div className="space-y-3">
        {Array.from({ length: lignes }).map((_, i) => (
          <div key={i} className="grid gap-3" style={{ gridTemplateColumns: `repeat(${colonnes}, minmax(0, 1fr))` }}>
            {Array.from({ length: colonnes }).map((__, j) => (
              <Skeleton key={j} className="h-4" />
            ))}
          </div>
        ))}
      </div>
    </div>
  );
}
