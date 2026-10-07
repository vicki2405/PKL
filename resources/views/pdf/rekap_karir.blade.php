<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Aspirasi Karier Siswa</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header img {
            width: 100%;
            max-height: 100px;
            object-fit: contain;
        }
        .title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-bottom: 15px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 11px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            color: #334155;
        }
        .text-center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
        }
        .badge-kuliah { background-color: #f97316; }
        .badge-kerja { background-color: #4f46e5; }
        .badge-wirausaha { background-color: #059669; }
        .badge-belum { background-color: #64748b; }
        
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .footer td {
            border: none;
            font-size: 11px;
        }
    </style>
</head>
<body>
    @if(file_exists(public_path('kopsurat.jpg')))
        <div class="header">
            <img src="{{ public_path('kopsurat.jpg') }}" alt="Kop Surat">
        </div>
    @endif

    <div class="title">REKAPITULASI ASPIRASI KARIER SISWA</div>
    <div class="subtitle">Dicetak Pada: {{ $date }} | Filter: {{ $filterLabel }}</div>

    <!-- Summary Box -->
    <table class="summary-box">
        <tr>
            <td>Total Siswa: {{ $stats['total_siswa'] ?? 0 }}</td>
            <td style="color: #ea580c;">🎓 Kuliah: {{ $stats['total_kuliah'] ?? 0 }}</td>
            <td style="color: #4338ca;">💼 Kerja: {{ $stats['total_kerja'] ?? 0 }}</td>
            <td style="color: #047857;">🏪 Wirausaha: {{ $stats['total_wirausaha'] ?? 0 }}</td>
            <td style="color: #475569;">⏳ Belum Mengisi: {{ $stats['total_belum'] ?? 0 }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            @if(isset($tipeKarir) && $tipeKarir === 'kuliah')
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 22%;">Identitas Siswa & Kontak</th>
                    <th style="width: 23%;">Jurusan, Kelas & Penempatan PKL</th>
                    <th style="width: 20%;">Perguruan Tinggi & Status</th>
                    <th style="width: 17%;">Jenjang & Program Studi</th>
                    <th style="width: 15%;">Jalur & Tahun</th>
                </tr>
            @elseif(isset($tipeKarir) && $tipeKarir === 'kerja')
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 22%;">Identitas Siswa & Kontak</th>
                    <th style="width: 23%;">Jurusan, Kelas & Penempatan PKL</th>
                    <th style="width: 20%;">Target Perusahaan & Posisi</th>
                    <th style="width: 16%;">Jenis & Kesesuaian</th>
                    <th style="width: 16%;">Target, Gaji & Lokasi</th>
                </tr>
            @elseif(isset($tipeKarir) && $tipeKarir === 'wirausaha')
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 22%;">Identitas Siswa & Kontak</th>
                    <th style="width: 23%;">Jurusan, Kelas & Penempatan PKL</th>
                    <th style="width: 20%;">Nama & Sektor Usaha</th>
                    <th style="width: 16%;">Status & Modal Awal</th>
                    <th style="width: 16%;">Lokasi Usaha</th>
                </tr>
            @elseif(isset($tipeKarir) && $tipeKarir === 'belum')
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 28%;">Identitas Siswa & Kontak</th>
                    <th style="width: 20%;">Jurusan & Kelas</th>
                    <th style="width: 33%;">Penempatan PKL (Bengkel & Pembimbing)</th>
                    <th style="width: 15%;">Status</th>
                </tr>
            @else
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 22%;">Identitas Siswa & Kontak</th>
                    <th style="width: 23%;">Jurusan, Kelas & Penempatan PKL</th>
                    <th style="width: 14%;">Pilihan & Waktu</th>
                    <th style="width: 38%;">Rincian Aspirasi & Target</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse($data as $index => $siswa)
                @php
                    $karir = $siswa->siswaKarir;
                    $tipe = $karir ? $karir->tipe_karir : 'belum';
                    $email = $siswa->user?->email ?: ($siswa->account?->email ?: null);
                    $penempatan = $siswa->penempatan;
                    $bengkel = $penempatan?->bengkel;
                    $pembimbing = $penempatan?->pembimbing;
                @endphp

                @if(isset($tipeKarir) && $tipeKarir === 'kuliah')
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $siswa->nama }}</strong><br>
                            <span style="color: #64748b; font-size: 10px;">NISN: {{ $siswa->nisn ?? '-' }} | NIPD: {{ $siswa->nipd ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 10px;">JK: {{ $siswa->jenis_kelamin ?? '-' }} | WA: {{ $siswa->no_hp ?? '-' }}</span>
                            @if($email)
                                <br><span style="color: #2563eb; font-size: 9px;">{{ $email }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</strong><br>
                            <span style="color: #475569; font-size: 10px;">Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}</span><br>
                            <span style="color: #0f172a; font-size: 9px;"><strong>Bengkel:</strong> {{ $bengkel?->nama_bengkel ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 9px;">Pembimbing: {{ $pembimbing?->nama_guru ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $karir?->nama_universitas ?? '-' }}</strong><br>
                            <span style="color: #ea580c; font-size: 10px; font-weight: bold;">Status: {{ $karir?->status_universitas ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $karir?->jenjang_studi ?? '-' }}</strong> - {{ $karir?->program_studi ?? '-' }}
                        </td>
                        <td>
                            <strong>Jalur:</strong> {{ $karir?->jalur_masuk ?? '-' }}<br>
                            <span style="color: #64748b; font-size: 10px;">Tahun Mulai: {{ $karir?->tahun_mulai ?? '-' }}</span>
                        </td>
                    </tr>
                @elseif(isset($tipeKarir) && $tipeKarir === 'kerja')
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $siswa->nama }}</strong><br>
                            <span style="color: #64748b; font-size: 10px;">NISN: {{ $siswa->nisn ?? '-' }} | NIPD: {{ $siswa->nipd ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 10px;">JK: {{ $siswa->jenis_kelamin ?? '-' }} | WA: {{ $siswa->no_hp ?? '-' }}</span>
                            @if($email)
                                <br><span style="color: #2563eb; font-size: 9px;">{{ $email }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</strong><br>
                            <span style="color: #475569; font-size: 10px;">Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}</span><br>
                            <span style="color: #0f172a; font-size: 9px;"><strong>Bengkel:</strong> {{ $bengkel?->nama_bengkel ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 9px;">Pembimbing: {{ $pembimbing?->nama_guru ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $karir?->nama_perusahaan ?? '-' }}</strong><br>
                            <span style="color: #4338ca; font-size: 10px;">Posisi: {{ $karir?->posisi_kerja ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>Jenis:</strong> {{ $karir?->jenis_pekerjaan ?? '-' }}<br>
                            <span style="color: #64748b; font-size: 10px;">Kesesuaian: {{ $karir?->kesesuaian_jurusan ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>Target:</strong> {{ $karir?->target_mulai_kerja ?? '-' }}<br>
                            <strong>Gaji:</strong> {{ $karir?->ekspektasi_gaji ?? '-' }}<br>
                            <span style="color: #64748b; font-size: 9px;">Lokasi: {{ $karir?->lokasi_kerja ?? '-' }}</span>
                        </td>
                    </tr>
                @elseif(isset($tipeKarir) && $tipeKarir === 'wirausaha')
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $siswa->nama }}</strong><br>
                            <span style="color: #64748b; font-size: 10px;">NISN: {{ $siswa->nisn ?? '-' }} | NIPD: {{ $siswa->nipd ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 10px;">JK: {{ $siswa->jenis_kelamin ?? '-' }} | WA: {{ $siswa->no_hp ?? '-' }}</span>
                            @if($email)
                                <br><span style="color: #2563eb; font-size: 9px;">{{ $email }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</strong><br>
                            <span style="color: #475569; font-size: 10px;">Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}</span><br>
                            <span style="color: #0f172a; font-size: 9px;"><strong>Bengkel:</strong> {{ $bengkel?->nama_bengkel ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 9px;">Pembimbing: {{ $pembimbing?->nama_guru ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $karir?->nama_usaha ?? '-' }}</strong><br>
                            <span style="color: #047857; font-size: 10px;">Sektor: {{ $karir?->sektor_usaha ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>Kepemilikan:</strong> {{ $karir?->status_kepemilikan ?? '-' }}<br>
                            <span style="color: #64748b; font-size: 10px;">Modal: {{ $karir?->modal_awal ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>Lokasi:</strong> {{ $karir?->lokasi_usaha ?? '-' }}
                        </td>
                    </tr>
                @elseif(isset($tipeKarir) && $tipeKarir === 'belum')
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $siswa->nama }}</strong><br>
                            <span style="color: #64748b; font-size: 10px;">NISN: {{ $siswa->nisn ?? '-' }} | NIPD: {{ $siswa->nipd ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 10px;">JK: {{ $siswa->jenis_kelamin ?? '-' }} | WA: {{ $siswa->no_hp ?? '-' }}</span>
                            @if($email)
                                <br><span style="color: #2563eb; font-size: 9px;">{{ $email }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</strong><br>
                            <span style="color: #475569; font-size: 10px;">Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>Bengkel:</strong> {{ $bengkel?->nama_bengkel ?? '-' }}<br>
                            <span style="color: #64748b; font-size: 9px;">Pemilik: {{ $bengkel?->nama_pemilik ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 9px;">Pembimbing: {{ $pembimbing?->nama_guru ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-belum">Belum Mengisi</span>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $siswa->nama }}</strong><br>
                            <span style="color: #64748b; font-size: 10px;">NISN: {{ $siswa->nisn ?? '-' }} | NIPD: {{ $siswa->nipd ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 10px;">JK: {{ $siswa->jenis_kelamin ?? '-' }} | WA: {{ $siswa->no_hp ?? '-' }}</span>
                            @if($email)
                                <br><span style="color: #2563eb; font-size: 9px;">{{ $email }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $siswa->kelas?->jurusan?->nama_jurusan ?? '-' }}</strong><br>
                            <span style="color: #475569; font-size: 10px;">Kelas: {{ $siswa->kelas?->nama_kelas ?? '-' }}</span><br>
                            <span style="color: #0f172a; font-size: 9px;"><strong>Bengkel:</strong> {{ $bengkel?->nama_bengkel ?? '-' }}</span><br>
                            <span style="color: #64748b; font-size: 9px;">Pembimbing: {{ $pembimbing?->nama_guru ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($tipe === 'kuliah')
                                <span class="badge badge-kuliah">Kuliah</span>
                            @elseif($tipe === 'kerja')
                                <span class="badge badge-kerja">Kerja</span>
                            @elseif($tipe === 'wirausaha')
                                <span class="badge badge-wirausaha">Wirausaha</span>
                            @else
                                <span class="badge badge-belum">Belum</span>
                            @endif
                            @if($karir && $karir->updated_at)
                                <br><span style="color: #64748b; font-size: 9px; font-weight: normal; margin-top: 3px; display: inline-block;">{{ $karir->updated_at->translatedFormat('d/m/Y H:i') }} WIB</span>
                            @endif
                        </td>
                        <td>
                            @if($tipe === 'kuliah')
                                <strong>Target PT:</strong> {{ $karir->nama_universitas ?? '-' }} ({{ $karir->status_universitas ?? '-' }})<br>
                                <strong>Jenjang & Prodi:</strong> {{ $karir->jenjang_studi ?? '-' }} - {{ $karir->program_studi ?? '-' }}<br>
                                <strong>Jalur & Tahun:</strong> {{ $karir->jalur_masuk ?? '-' }} (Tahun {{ $karir->tahun_mulai ?? '-' }})
                            @elseif($tipe === 'kerja')
                                <strong>Target Perusahaan:</strong> {{ $karir->nama_perusahaan ?? '-' }}<br>
                                <strong>Posisi & Jenis:</strong> {{ $karir->posisi_kerja ?? '-' }} ({{ $karir->jenis_pekerjaan ?? '-' }})<br>
                                <strong>Lokasi Target:</strong> {{ $karir->lokasi_kerja ?? '-' }} | <strong>Gaji:</strong> {{ $karir->ekspektasi_gaji ?? '-' }}
                            @elseif($tipe === 'wirausaha')
                                <strong>Nama Usaha:</strong> {{ $karir->nama_usaha ?? '-' }} ({{ $karir->sektor_usaha ?? '-' }})<br>
                                <strong>Kepemilikan & Modal:</strong> {{ $karir->status_kepemilikan ?? '-' }} ({{ $karir->modal_awal ?? '-' }})<br>
                                <strong>Lokasi Usaha:</strong> {{ $karir->lokasi_usaha ?? '-' }}
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Belum memilih aspirasi karier</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada data siswa ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="footer">
        <tr>
            <td style="width: 60%;"></td>
            <td class="text-center">
                <p>Mengetahui,<br>Koordinator BKK / BK</p>
                <br><br><br>
                <p><strong>________________________</strong></p>
            </td>
        </tr>
    </table>
</body>
</html>
