<?php

declare(strict_types=1);

namespace App\Features\Reports\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports presented report data to CSV, PDF, or JSON.
 */
class ReportExportService
{
    /**
     * @param  array<string, mixed>  $presented
     */
    public function export(string $type, string $title, array $presented, string $format): Response|StreamedResponse
    {
        $filename = 'report-'.$type.'-'.now()->format('Y-m-d');

        return match ($format) {
            'pdf' => $this->exportPdf($title, $presented, $filename),
            'csv' => $this->exportCsv($type, $presented, $filename),
            default => $this->exportJson($presented, $filename),
        };
    }

    /**
     * @param  array<string, mixed>  $presented
     */
    private function exportPdf(string $title, array $presented, string $filename): Response
    {
        $headers = [];
        $rows = [];

        if (isset($presented['rows'])) {
            $rows = $presented['rows'];
            if ($rows !== []) {
                $headers = array_map(
                    fn (string $key): string => ucwords(str_replace('_', ' ', $key)),
                    array_keys($rows[0]),
                );
            }
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'title' => $title,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'summary' => $presented['summary'] ?? [],
            'headers' => $headers,
            'rows' => $rows,
        ]);

        return $pdf->download($filename.'.pdf');
    }

    /**
     * @param  array<string, mixed>  $presented
     */
    private function exportCsv(string $type, array $presented, string $filename): StreamedResponse
    {
        $presenter = app(ReportPresenter::class);
        $headers = $presenter->exportHeaders($type);
        $rows = $presenter->exportRows($type, $presented);

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $presented
     */
    private function exportJson(array $presented, string $filename): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print json_encode($presented, JSON_PRETTY_PRINT),
            $filename.'.json',
            ['Content-Type' => 'application/json'],
        );
    }
}
