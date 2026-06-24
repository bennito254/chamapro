type Props = {
    onExportCsv?: () => void;
    onExportExcel?: () => void;
    onExportPdf?: () => void;
    csvHref?: string;
    excelHref?: string;
    pdfHref?: string;
};

export default function ExportButtons({
    onExportCsv,
    onExportExcel,
    onExportPdf,
    csvHref,
    excelHref,
    pdfHref,
}: Props) {
    return (
        <div className="btn-group btn-group-sm">
            {(onExportCsv || csvHref) && (
                <a
                    href={csvHref ?? '#'}
                    className="btn btn-outline-secondary"
                    onClick={onExportCsv ? (e) => { e.preventDefault(); onExportCsv(); } : undefined}
                >
                    <i className="bi bi-filetype-csv me-1" />
                    CSV
                </a>
            )}
            {(onExportExcel || excelHref) && (
                <a
                    href={excelHref ?? '#'}
                    className="btn btn-outline-secondary"
                    onClick={onExportExcel ? (e) => { e.preventDefault(); onExportExcel(); } : undefined}
                >
                    <i className="bi bi-file-earmark-excel me-1" />
                    Excel
                </a>
            )}
            {(onExportPdf || pdfHref) && (
                <a
                    href={pdfHref ?? '#'}
                    className="btn btn-outline-secondary"
                    onClick={onExportPdf ? (e) => { e.preventDefault(); onExportPdf(); } : undefined}
                >
                    <i className="bi bi-file-earmark-pdf me-1" />
                    PDF
                </a>
            )}
        </div>
    );
}
