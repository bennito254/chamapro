<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #1e40af; }
        .meta { color: #64748b; margin-bottom: 16px; font-size: 10px; }
        .summary { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .summary td { padding: 6px 8px; border: 1px solid #e2e8f0; }
        .summary td:first-child { font-weight: bold; background: #f8fafc; width: 40%; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #e2e8f0; padding: 5px 6px; text-align: left; }
        table.data th { background: #eff6ff; color: #1e40af; font-size: 10px; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">Generated {{ $generatedAt }}</div>

    @if (! empty($summary))
        <table class="summary">
            @foreach ($summary as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td>
                        @if (($item['format'] ?? '') === 'currency')
                            KES {{ number_format((float) $item['value'], 2) }}
                        @else
                            {{ $item['value'] }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @if (! empty($headers) && ! empty($rows))
        <table class="data">
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $value)
                            <td>{{ $value }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
