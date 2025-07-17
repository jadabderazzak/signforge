<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Service for generating PDF documents using Dompdf.
 *
 * Configures Dompdf with default options and renders provided HTML into a PDF string.
 */
class PdfService
{
/**
 * Generates a PDF file from given HTML content.
 *
 * @param string $html The HTML content to convert into a PDF.
 *
 * @return string The raw PDF binary output.
 */

  public function generatePdf(string $html): string
{
    $options = new Options();
    $options->set([
        'defaultFont' => 'DejaVu Sans',
        'isRemoteEnabled' => true,
        'isPhpEnabled' => true,
        'defaultPaperSize' => 'A4',
        'defaultPaperOrientation' => 'portrait',
        'margin_top' => '10mm',
        'margin_bottom' => '15mm', // Réduit pour éviter les blancs
        'margin_left' => '10mm',
        'margin_right' => '10mm'
    ]);

    $dompdf = new Dompdf($options);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
}
}
