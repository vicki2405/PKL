<!DOCTYPE html>
<html>
<head>
    <title>Monitoring PKL</title>
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
            font-size: 13px;
            margin-bottom: 20px;
            color: #555;
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
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 50px;
            text-align: left;
            float: right;
            width: 30%;
            clear: both;
            margin-right: 0;
        }
        .pelanggaran-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 8px;
            border-bottom: 2px solid #333;
            padding-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('kopsurat.jpg') }}" alt="Kop Surat">
    </div>

    <div class="title">LAPORAN MONITORING PKL</div>
    <div class="subtitle">
        @if(isset($filters['pembimbing_nama']))
            Pembimbing: {{ $filters['pembimbing_nama'] }}<br>
        @endif
        @if(isset($filters['bengkel_nama']))
            Bengkel: {{ $filters['bengkel_nama'] }}<br>
        @endif
        @if(isset($filters['session_label']))
            Sesi: {{ $filters['session_label'] }}<br>
        @endif
        @if(isset($filters['bulan_label']))
            Bulan: {{ $filters['bulan_label'] }}<br>
        @endif
        @if(isset($filters['status_label']))
            Status: {{ $filters['status_label'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 12%;">Monitoring</th>
                <th style="width: 16%;">Pembimbing</th>
                <th style="width: 16%;">Bengkel</th>
                <th style="width: 21%;">Catatan</th>
                <th style="width: 8%;">Pelanggaran</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M Y') }}</td>
                <td class="text-center">
                    <strong>Monitoring {{ $item->session_display ?? $item->session ?? ($item->monitoring_ke ?? '-') }}</strong>
                    @if(!empty($item->bulan_name))
                        <br><span style="font-size: 9px; color: #555;">{{ $item->bulan_name }}</span>
                    @endif
                </td>
                <td>{{ $item->pembimbing?->nama_guru ?? $item->pembimbing?->user?->name ?? '-' }}</td>
                <td>{{ $item->bengkel?->nama_bengkel ?? '-' }}</td>
                <td>{{ $item->catatan_monitoring ?? '-' }}</td>
                <td class="text-center">{{ $item->pelanggaran_count }}</td>
                <td class="text-center">
                    @if($item->laporan_diterima)
                        <span class="badge badge-success">Diterima</span>
                    @else
                        <span class="badge badge-warning">Menunggu</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data untuk filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($totalMonitoring))
    <p style="text-align: left; font-size: 11px;">
        <strong>Total Monitoring:</strong> {{ $totalMonitoring }}
    </p>
    @endif

    @php
        $pelanggaranRows = [];
        foreach ($data as $item) {
            foreach ($item->details as $detail) {
                if ($detail->is_pelanggaran) {
                    $pelanggaranRows[] = [
                        'tanggal' => \Carbon\Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M Y'),
                        'session_display' => $item->session_display ?? $item->session ?? ($item->monitoring_ke ?? '-'),
                        'bulan_name' => $item->bulan_name ?? '',
                        'pembimbing' => $item->pembimbing?->nama_guru ?? $item->pembimbing?->user?->name ?? '-',
                        'bengkel' => $item->bengkel?->nama_bengkel ?? '-',
                        'nama_siswa' => $detail->siswa?->nama ?? '-',
                        'kelas' => $detail->siswa?->kelas?->nama_kelas ?? '-',
                        'catatan' => $detail->catatan ?? '-',
                        'status' => $item->laporan_diterima ? 'Diterima' : 'Menunggu',
                        'status_class' => $item->laporan_diterima ? 'badge-success' : 'badge-warning',
                    ];
                }
            }
        }
    @endphp

    @if(count($pelanggaranRows) > 0)
    <div class="pelanggaran-title">Detail Pelanggaran Siswa</div>
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 12%;">Monitoring</th>
                <th style="width: 14%;">Pembimbing</th>
                <th style="width: 14%;">Bengkel</th>
                <th style="width: 14%;">Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 14%;">Catatan Pelanggaran</th>
                <th style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pelanggaranRows as $no => $row)
            <tr>
                <td class="text-center">{{ $no + 1 }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td class="text-center">
                    <strong>Monitoring {{ $row['session_display'] }}</strong>
                    @if(!empty($row['bulan_name']))
                        <br><span style="font-size: 9px; color: #555;">{{ $row['bulan_name'] }}</span>
                    @endif
                </td>
                <td>{{ $row['pembimbing'] }}</td>
                <td>{{ $row['bengkel'] }}</td>
                <td>{{ $row['nama_siswa'] }}</td>
                <td>{{ $row['kelas'] }}</td>
                <td>{{ $row['catatan'] }}</td>
                <td class="text-center">
                    <span class="badge {{ $row['status_class'] }}">{{ $row['status'] }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <table style="width: 100%; border: none; margin-top: 50px;">
        <tr>
            <td style="width: 72%; border: none;"></td>
            <td style="width: 28%; border: none; text-align: left; vertical-align: top;">
                <p>Banyuwangi, {{ date('d F Y') }}</p>
                <p>BK SMKS Muhammadiyah 2 Genteng</p>
                <br><br><br><br>
                <p>-----------------------------------</p>
                <p style="font-size: 10px;">Penanggung Jawab BK</p>
            </td>
        </tr>
    </table>
</body>
</html>
