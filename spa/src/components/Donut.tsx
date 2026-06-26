export function Donut({ value, size = 132 }: { value: number | null; size?: number }) {
  const stroke = 14;
  const rayon = (size - stroke) / 2;
  const circonference = 2 * Math.PI * rayon;
  const pourcentage = value ?? 0;
  const offset = circonference - (pourcentage / 100) * circonference;
  const centre = size / 2;

  return (
    <svg
      width={size}
      height={size}
      viewBox={`0 0 ${size} ${size}`}
      role="img"
      aria-label={null === value ? 'Taux de conformité non évalué' : `Taux de conformité ${value} %`}
    >
      <circle cx={centre} cy={centre} r={rayon} fill="none" stroke="#e5e7eb" strokeWidth={stroke} />
      {null !== value && (
        <circle
          cx={centre}
          cy={centre}
          r={rayon}
          fill="none"
          stroke="#000091"
          strokeWidth={stroke}
          strokeDasharray={circonference}
          strokeDashoffset={offset}
          strokeLinecap="round"
          transform={`rotate(-90 ${centre} ${centre})`}
          className="transition-all duration-700"
        />
      )}
      <text x="50%" y="50%" textAnchor="middle" dominantBaseline="central" className="fill-gray-900 font-bold" fontSize={size * 0.2}>
        {null === value ? '—' : `${value}%`}
      </text>
    </svg>
  );
}
