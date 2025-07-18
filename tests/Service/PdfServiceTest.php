<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\PdfService;

class PdfServiceTest extends TestCase
{
    private PdfService $pdfService;

    protected function setUp(): void
    {
        $this->pdfService = new PdfService();
    }

    public function testGeneratePdfReturnsPdfBinary(): void
    {
        $html   = '<h1>Test PDF</h1><p>Hello World</p>';
        $output = $this->pdfService->generatePdf($html);

        // It should return a string
        $this->assertIsString($output);

        // PDF binaries start with "%PDF-"
        $this->assertStringStartsWith('%PDF-', $output);

        // And contain the PDF version header
        $this->assertStringContainsString('%PDF-', substr($output, 0, 10));
    }
}
