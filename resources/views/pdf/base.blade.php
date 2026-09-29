{{--
    Layout dasar PDF.

    dompdf hanya mendukung CSS 2.1: tidak ada flexbox, tidak ada grid, tidak
    ada @vite/font web. Semua blok wajib table atau block. Font_family_default
    memakai DejaVu Sans bawaan dompdf yang cakupan Latin-1-nya lengkap,
    sehingga huruf Indonesia tetap tampil.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 25mm 15mm 20mm 15mm;
            footer: {
                content: "Halaman " counter(page) " dari " counter(pages);
                position: fixed;
                bottom: -12mm;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 8pt;
                color: #888;
            }
        }

        * { margin: 0; padding: 0; }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9pt;
            color: #1f2937;
            line-height: 1.4;
        }

        h1 { font-size: 14pt; margin-bottom: 2mm; }
        h2 { font-size: 11pt; margin-bottom: 1mm; }

        .muted { color: #6b7280; }
        .small { font-size: 8pt; }

        table { width: 100%; border-collapse: collapse; }
        .layout { width: 100%; }
        .layout td { vertical-align: top; }

        .doc-table th, .doc-table td {
            border: 1px solid #d1d5db;
            padding: 1.8mm 2mm;
        }
        .doc-table th {
            background: #f3f4f6;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #374151;
        }
        .doc-table td.num, .doc-table th.num { text-align: right; }
        .doc-table td.ctr, .doc-table th.ctr { text-align: center; }

        /* Judul tabel berulang di tiap halaman */
        .doc-table thead { display: table-header-group; }
        .doc-table tr { page-break-inside: avoid; }

        .empty { padding: 8mm 0; text-align: center; color: #9ca3af; }

        .total-row td { font-weight: bold; background: #f9fafb; }

        .signature td { padding-top: 18mm; text-align: center; vertical-align: top; }
        .signature .line {
            border-bottom: 1px solid #374151;
            margin: 0 8mm 1mm 8mm;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
