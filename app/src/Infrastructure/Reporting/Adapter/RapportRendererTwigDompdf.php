<?php

declare(strict_types=1);

namespace App\Infrastructure\Reporting\Adapter;

use App\Domain\Reporting\Port\RapportRenderer;
use App\Domain\Reporting\ValueObject\Rapport;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * Rendu du rapport en HTML (Twig) et en PDF (dompdf, 100 % PHP, hors-ligne).
 */
final readonly class RapportRendererTwigDompdf implements RapportRenderer
{
    public function __construct(private Environment $twig)
    {
    }

    public function html(Rapport $rapport): string
    {
        return $this->twig->render('reporting/rapport.html.twig', ['rapport' => $rapport]);
    }

    public function pdf(Rapport $rapport): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($rapport), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
