<!DOCTYPE html>
<html>
<head>
    <title>Cetak Absen Pembekalan</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
            font-size: 13pt;
        }
        .header h3 {
            margin: 2px 0;
            padding: 0;
            font-size: 10pt;
        }
        .pembimbing-section {
            margin-bottom: 10px;
        }
        .pembimbing-title {
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 4px;
            padding: 4px 6px;
            background-color: #e5e7eb;
            border: 1px solid black;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        .table th, .table td {
            border: 1px solid black;
            padding: 6px 4px;
        }
        .table tbody td {
            height: 28px;
        }
        .table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .text-left {
            text-align: left !important;
        }
        .text-center {
            text-align: center !important;
        }
        .ttd-cell {
            width: 18%;
        }
        .no-cell {
            width: 5%;
        }
        .nama-siswa-cell {
            width: 27%;
        }
        .kelas-cell {
            width: 10%;
        }
        .nama-bengkel-cell {
            width: 20%;
        }
        .nama-pembina-cell {
            width: 18%;
        }
        .page-break {
            page-break-before: always;
        }
        .signature-section {
            margin-top: 20px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            margin-top: 10px;
        }
        .signature-line {
            margin-top: 50px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    @forelse ($grouped as $pembimbingNama => $items)
        <div class="pembimbing-section @if(!$loop->first) page-break @endif">
            <div class="header">
                <h2>DAFTAR HADIR PEMBEKALAN PRAKTIK KERJA LAPANGAN (PKL)</h2>
                <h3>SMKS MUHAMMADIYAH 2 GENTENG</h3>
                <h3>TAHUN PELAJARAN 2026/2027</h3>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 8.5pt; font-weight: bold; background-color: #e5e7eb; border: 1px solid black;">
                <tr>
                    <td style="border: none; padding: 4px 8px; text-align: left;">
                        Pembimbing: {{ $pembimbingNama }}
                    </td>
                    <td style="border: none; padding: 4px 8px; text-align: right;">
                        Banyuwangi, {{ $tanggal }}
                    </td>
                </tr>
            </table>

            <table class="table">
                <thead>
                    <tr>
                        <th class="no-cell">No</th>
                        <th class="nama-siswa-cell">Nama Siswa</th>
                        <th class="kelas-cell">Kelas</th>
                        <th class="nama-bengkel-cell">Nama Bengkel</th>
                        <th class="nama-pembina-cell">Nama Pembimbing</th>
                        <th class="ttd-cell">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-left">{{ $item->siswa?->nama ?? '-' }}</td>
                        <td class="text-center">{{ $item->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                        <td class="text-left">{{ $item->bengkel?->nama_bengkel ?? '-' }}</td>
                        <td class="text-left">{{ $item->pembimbing?->nama_guru ?? '-' }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <p style="text-align:center;">Tidak ada data penempatan PKL</p>
    @endforelse

    <div class="signature-section" style="margin-top:30px;">
        <div>
            Banyuwangi, {{ $tanggalSurat }}<br>
            Ketua Praktik Kerja Lapangan
        </div>
        <div class="signature-box">
            <div class="signature-line">&nbsp;</div>
            <div style="font-weight: bold;">(.........................)</div>
        </div>
    </div>
</body>
</html>
