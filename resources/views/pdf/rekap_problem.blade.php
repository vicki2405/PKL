<!DOCTYPE html>
<html>
<head>
    <title>Rekap Problem Siswa</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
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
            text-align: left;
            padding-left: 72%;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('kopsurat.jpg') }}" alt="Kop Surat">
    </div>

    <div class="title">REKAP DATA PERMASALAHAN SISWA PKL</div>
    <div class="subtitle">
        @if(!empty($filters['tanggal']))
            Tanggal: {{ \Carbon\Carbon::parse($filters['tanggal'])->translatedFormat('d F Y') }}
        @endif
        @if(isset($filters['kelas_nama']))
            <br>Kelas: {{ $filters['kelas_nama'] }}
        @endif
        @if(isset($filters['pembimbing_nama']))
            <br>Pembimbing: {{ $filters['pembimbing_nama'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 7%;">Kelas</th>
                <th style="width: 13%;">Nama Siswa</th>
                <th style="width: 13%;">Nama Bengkel</th>
                <th style="width: 13%;">Pembimbing</th>
                <th style="width: 14%;">Masalah</th>
                <th style="width: 14%;">Tindakan</th>
                <th style="width: 4%;">Sanksi</th>
                <th style="width: 10%;">Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>
                <td>{{ $item->siswa && $item->siswa->kelas ? $item->siswa->kelas->nama_kelas : '-' }}</td>
                <td>{{ $item->siswa ? $item->siswa->nama : '-' }}</td>
                <td>{{ $item->bengkel ? $item->bengkel->nama_bengkel : '-' }}</td>
                <td>{{ $item->pembimbing ? $item->pembimbing->nama_guru : '-' }}</td>
                <td>{{ $item->deskripsi_masalah }}</td>
                <td>{{ $item->deskripsi_tindakan }}</td>
                <td>{{ $item->saksi }}</td>
                <td>{{ $item->admin ? $item->admin->name : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data untuk filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Banyuwangi, {{ date('d F Y') }}</p>
        <p>BK SMKS Muhammadiyah 2 Genteng</p>
        <br><br><br>
        <p>--------------------------------</p>
    </div>
</body>
</html>
