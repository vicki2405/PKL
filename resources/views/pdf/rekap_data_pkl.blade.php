<!DOCTYPE html>
<html>
<head>
    <title>Rekap Data PKL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .footer .footer-content {
            display: inline-block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Assuming kopsurat.jpg exists in public path as seen in other views -->
        <img src="{{ public_path('kopsurat.jpg') }}" alt="Kop Surat">
    </div>

    <div class="title">REKAP DATA PRAKTIK KERJA LAPANGAN</div>
    <div class="subtitle">
        Per Tanggal: {{ $date }}
        @if(!empty($filters) && ($filters['mode'] ?? null) === 'bulanan')
            <br>
            Periode: {{ \Carbon\Carbon::create($filters['year'], $filters['month'], 1)->translatedFormat('F Y') }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 23%;">Nama Pembimbing</th>
                <th style="width: 18%;">Nama Bengkel</th>
                <th style="width: 12%;">Desa</th>
                <th style="width: 12%;">Kecamatan</th>
                <th style="width: 10%;">Tgl Ditambahkan</th>
                <th style="width: 10%;">Jumlah Siswa</th>
                <th style="width: 10%;">Monitoring</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                @php
                    $details = $item['monitoring_detail'] ?? [];
                @endphp
                @if (empty($details))
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_pembimbing'] }}</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">{{ $item['jumlah_siswa'] }}</td>
                        <td class="text-center">{{ ($item['monitoring'] ?? 0) }} / {{ ($item['target'] ?? 0) }}</td>
                    </tr>
                @else
                    @foreach ($details as $bIndex => $bengkel)
                        <tr>
                            <td class="text-center">{{ $bIndex === 0 ? $index + 1 : '' }}</td>
                            <td>{{ $bIndex === 0 ? $item['nama_pembimbing'] : '' }}</td>
                            <td>{{ $bengkel['bengkel_nama'] }}</td>
                            <td>{{ $bengkel['desa'] ?? '-' }}</td>
                            <td>{{ $bengkel['kecamatan'] ?? '-' }}</td>
                            <td class="text-center">{{ $bengkel['tanggal_ditambahkan'] ?? '-' }}</td>
                            <td class="text-center">{{ $bengkel['siswa_count'] }}</td>
                            <td class="text-center">{{ $bengkel['kunjungan_unik'] . '/' . $bengkel['target_kunjungan'] }}</td>
                        </tr>
                    @endforeach
                @endif
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="footer-content">
            <p>Banyuwangi, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</p>
            <p>Ketua PKL</p>
            <p>SMKS Muhammadiyah 2 Genteng</p>
            <br><br><br><br>
            <p>..................................</p>
        </div>
    </div>
</body>
</html>
