<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi PKL</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.25;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h3 {
            margin: 0;
            padding: 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .table th, .table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .table th {
            background-color: #f0f0f0;
        }
        .text-left {
            text-align: left !important;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .header {
            page-break-inside: avoid;
        }
        .page {
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: auto;
        }
        .table {
            page-break-inside: auto;
        }
        .table thead {
            display: table-header-group;
        }
        .table tbody {
            display: table-row-group;
        }
        .footer {
            margin-top: 30px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
        .signature-cell {
            width: 40%;
            text-align: center;
        }
        .signature-meta {
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .signature-image {
            height: 85px;
            margin: 0 auto 6px;
        }
        .signature-ttd {
            display: block;
            height: 80px;
            margin: 0 auto;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 0;
        }
    </style>
</head>
<body>
    @php
        $siswas = $siswas ?? [];
        $monthName = $monthName ?? '';
        $year = $year ?? '';
        $kelasName = $kelasName ?? '';
        $tanggalSurat = $tanggalSurat ?? '';
        $suratPengantar = $suratPengantar ?? (object) ['ketua_pkl_nama' => ''];
        $ketuaPklTtdFile = $ketuaPklTtdFile ?? null;
        $blankPixel = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        $toDataUri = function ($path) use ($blankPixel) {
            if (!$path || !file_exists($path)) return $blankPixel;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => null,
            };
            if (!$mime) return $blankPixel;
            $contents = @file_get_contents($path);
            if ($contents === false) return $blankPixel;
            return 'data:'.$mime.';base64,'.base64_encode($contents);
        };
    @endphp
    @foreach(($siswasByKelas ?? collect()) as $kelasTitle => $siswasPerKelas)
        @php
            $rowNumber = 1;
        @endphp
        <div class="page">
            <div class="header">
                <h2>REKAPITULASI ABSENSI SISWA PRAKTIK KERJA LAPANGAN (PKL)</h2>
                <h2>SMKS MUHAMMADIYAH 2 GENTENG</h2>
                <h2>TAHUN PELAJARAN 2025/2026</h2>
            </div>

            <table class="info-table">
                <tr>
                    <td width="100">Bulan</td>
                    <td width="10">:</td>
                    <td>{{ $monthName }} {{ $year }}</td>
                </tr>
                <tr>
                    <td>Kelas</td>
                    <td>:</td>
                    <td>{{ $kelasTitle }}</td>
                </tr>
            </table>

            <table class="table">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="28%">Nama Siswa</th>
                        <th width="8%">Kelas</th>
                        <th width="18%">Tempat PKL</th>
                        <th width="14%">Pembimbing</th>
                        <th width="4%">H</th>
                        <th width="4%">I</th>
                        <th width="4%">S</th>
                        <th width="4%">A</th>
                        <th width="6%">Jml</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswasPerKelas as $siswa)
                        <tr>
                            <td>{{ $rowNumber++ }}</td>
                            <td class="text-left">{{ $siswa->nama }}</td>
                            <td>{{ $siswa->kelas ? $siswa->kelas->nama_kelas : '-' }}</td>
                            <td class="text-left">{{ $siswa->penempatan && $siswa->penempatan->bengkel ? $siswa->penempatan->bengkel->nama_bengkel : '-' }}</td>
                            <td class="text-left">{{ $siswa->penempatan?->pembimbing?->nama_guru ?: ($siswa->penempatan?->pembimbing?->user?->name ?? '-') }}</td>
                            <td>{{ $siswa->total_hadir }}</td>
                            <td>{{ $siswa->total_izin }}</td>
                            <td>{{ $siswa->total_sakit }}</td>
                            <td>{{ $siswa->total_alpha }}</td>
                            <td>{{ $siswa->total_hadir + $siswa->total_izin + $siswa->total_sakit + $siswa->total_alpha }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">Data tidak ditemukan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td style="width: 60%;"></td>
                <td class="signature-cell">
                    <div class="signature-meta">
                        Banyuwangi, {{ $tanggalSurat }}<br>
                        Ketua Praktik Kerja Lapangan
                    </div>
                    <div class="signature-image">
                        <img src="{{ $toDataUri($ketuaPklTtdFile) }}" class="signature-ttd" alt="">
                    </div>
                    <div class="signature-name">{{ $suratPengantar->ketua_pkl_nama }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
