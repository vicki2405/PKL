<!DOCTYPE html>
<html>
<head>
    <title>Rekap Pembimbing</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        .stats-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 11px;
            border-collapse: collapse;
        }
        .stats-table td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: center;
            background-color: #fafafa;
        }
        .stats-table span {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
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
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('kopsurat.jpg') }}" alt="Kop Surat">
    </div>

    <div class="title">LAPORAN REKAP PEMBIMBING</div>
    <div class="subtitle">Data Pembimbing, Bengkel, dan Siswa PKL</div>

    <table class="stats-table">
        <tr>
            <td>Total Pembimbing: <span>{{ $stats['pembimbing_total'] }}</span></td>
            <td>Bengkel Terisi: <span>{{ $stats['bengkel_terisi_total'] }}</span></td>
            <td>Siswa Terdata: <span>{{ $stats['siswa_terisi_total'] }}</span></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 40px">No</th>
                <th>Nama Pembimbing</th>
                <th>Jabatan</th>
                <th style="width: 80px">Jumlah Bengkel</th>
                <th style="width: 80px">Jumlah Siswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    <td class="text-center">{{ $row['no'] }}</td>
                    <td>{{ $row['nama_guru'] }}</td>
                    <td>{{ $row['jabatan'] }}</td>
                    <td class="text-center">{{ $row['bengkel_count'] }}</td>
                    <td class="text-center">{{ $row['siswa_count'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data pembimbing.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
