<?php

declare(strict_types=1);

namespace App\Domain\Reporting\Port;

use App\Domain\Reporting\ValueObject\Rapport;

interface RapportRenderer
{
    public function html(Rapport $rapport): string;

    /**
     * @return string Contenu binaire du PDF
     */
    public function pdf(Rapport $rapport): string;
}
