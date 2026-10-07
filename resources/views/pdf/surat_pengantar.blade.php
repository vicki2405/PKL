<!DOCTYPE html>
<html>
<head>
    <title>Surat Pengantar PKL</title>
    <style>
        @page {
            size: 21.5cm 33cm;
            margin: 0.45cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5pt;
            line-height: 1.35;
            margin: 0;
        }
        .header-image {
            width: 100%;
            display: block;
            margin-bottom: 6px;
            height: auto;
            max-height: 125px;
        }
        .content-wrapper {
            padding-left: 0.8cm;
            padding-right: 0.8cm;
        }
        p {
            margin-top: 3px;
            margin-bottom: 3px;
        }
        .content-table {
            width: 100%;
            margin-bottom: 4px;
        }
        .content-table td {
            vertical-align: top;
        }
        .label-col {
            width: 100px;
        }
        .separator-col {
            width: 10px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .student-table th, .student-table td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 0px;
        }
        .signature-name-up {
            margin-top: -16px;
            margin-bottom: 6px;
        }
        .signature-nbm-up {
            margin-top: 0px;
        }
        .signature-text {
            width: 260px;
            margin-left: 0;
            margin-right: auto;
            text-align: center;
        }
        .signature-text-right {
            width: 260px;
            margin-right: 0;
            margin-left: auto;
            text-align: center;
        }
        .signature-container {
            position: relative;
            display: block;
            width: 260px;
            height: 115px;
            margin-left: auto;
            margin-right: auto;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        .stempel-image {
            position: absolute;
            width: 105px;
            height: 105px;
            opacity: 0.8;
            top: -36px;
            left: 16px;
            z-index: 3;
            transform: scale(1.5);
            transform-origin: top left;
        }
        .ttd-image {
            position: absolute;
            height: 80px;
            width: 135px;
            top: -6px;
            left: 102px;
            z-index: 2;
            transform: scale(1.5);
            transform-origin: top left;
        }
        .stempel-image-left {
            position: absolute;
            width: 105px;
            height: 105px;
            opacity: 0.8;
            top: -30px;
            left: -48px;
            z-index: 3;
            transform: scale(1.5);
            transform-origin: top left;
        }
        .ttd-image-left {
            position: absolute;
            height: 80px;
            width: 135px;
            top: -22px;
            left: -20px;
            z-index: 2;
            transform: scale(1.8);
            transform-origin: top left;
        }
    </style>
</head>
<body>
    @php
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
    <!-- Header -->
    <img src="{{ $toDataUri(file_exists($kopSuratFile) ? $kopSuratFile : public_path('images/kopsurat.jpg')) }}" class="header-image" alt="Kop Surat">

    <div class="content-wrapper">
        <!-- Surat Details -->
        <table class="content-table">
            <tr>
                <td class="label-col">Nomor</td>
                <td class="separator-col">:</td>
                <td>{{ $suratPengantar->nomor_surat }}</td>
            </tr>
            <tr>
                <td class="label-col">Lampiran</td>
                <td class="separator-col">:</td>
                <td colspan="2">{{ $suratPengantar->lampiran ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Perihal</td>
                <td class="separator-col">:</td>
                <td colspan="2" style="font-weight: bold; text-decoration: underline;">{{ $defaultPerihal }}</td>
            </tr>
        </table>

        <div style="margin-top: 10px; margin-bottom: 10px;">
            Kepada Yth.<br>
            <strong>{{ $suratPengantar->tujuan_surat }}</strong><br>
            {{ $suratPengantar->alamat_tujuan }}
        </div>

        <div style="margin-top: 10px;">
            <p style="margin-bottom: 10px;"><strong><em>Assalamualaikum Warahmatullahi Wabarakatuh.</em></strong></p>
            <p style="margin-bottom: 10px;"><strong><em>Dengan Hormat,</em></strong></p>

            <p style="text-align: justify; text: 60px; margin-bottom: 10px;">
                Dalam rangka persiapan pelaksanaan Program PKL Industri bagi siswa SMKS Muhammadiyah 2 Genteng Banyuwangi, yang akan dilaksanakan pada tanggal <strong>{{ $tanggalMulai }}</strong> s/d <strong>{{ $tanggalSelesai }}</strong>.
            </p>
            <p style="text-align: justify; text: 50px;">
                Maka bersama ini kami sampaikan surat permohonan Kepada <strong>{{ $suratPengantar->tujuan_surat }}</strong> untuk menjadi tempat Praktik Kerja Lapangan (PKL) bagi siswa kami dengan data sebagai berikut :
            </p>
            <!-- Student Table -->
            <table class="student-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Nama Siswa</th>
                        <th style="width: 15%;">Kelas</th>
                        <th style="width: 25%;">Kompetensi Keahlian</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 1; $i <= 10; $i++)
                    @php $siswa = $suratPengantar->siswas[$i-1] ?? null; @endphp
                    <tr>
                        <td>{{ $i }}</td>
                        <td style="text-align: left; padding-left: 10px;">{{ $siswa ? $siswa->nama : '-' }}</td>
                        <td>{{ $siswa ? ($siswa->kelas->nama_kelas ?? '-') : '-' }}</td>
                        <td>{{ $siswa ? ($siswa->kelas->jurusan->nama_jurusan ?? '-') : '-' }}</td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <p style="text-align: justify; text: 50px;">
                Demikian surat permohonan ini kami ajukan, semoga berkenan dan kami sampaikan banyak terima kasih.
            </p>

            <p><strong><em>Wassalamualaikum Warahmatullahi Wabarakatuh.</em></strong></p>
        </div>
        <br>
        <table class="signature-table" style="margin-top: 0px;">
            <tr>
                <td style="width: 50%; vertical-align: top; text-align: left; padding-left: 20px;">
                    <div class="signature-text">
                        Mengetahui<br>
                        Kepala Sekolah ,
                    </div>
                    <div class="signature-container">
                        <img src="{{ $toDataUri($kepalaSekolahTtdFile) }}" class="ttd-image-left" alt="TTD Kepala Sekolah">
                        <img src="{{ $toDataUri($kepalaSekolahStempelFile) }}" class="stempel-image-left" alt="Stempel Kepala Sekolah">
                    </div>
                    <div class="signature-name signature-text signature-name-up">{{ $suratPengantar->kepala_sekolah_nama }}</div>
                    <div class="signature-text signature-nbm-up">NBM. {{ $suratPengantar->kepala_sekolah_nbm }}</div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: center; padding-right: 20px;">
                    <div class="signature-text-right">
                        Banyuwangi, {{ $tanggalSurat }}<br>
                        Ketua Praktik Kerja Lapangan
                    </div>
                    <div class="signature-container">
                        <img src="{{ $toDataUri($ketuaPklTtdFile) }}" class="ttd-image" alt="TTD Ketua PKL">
                        <img src="{{ $toDataUri($ketuaPklStempelFile) }}" class="stempel-image" alt="Stempel Ketua PKL">
                    </div>
                    <div class="signature-name signature-text-right signature-name-up">{{ $suratPengantar->ketua_pkl_nama }}</div>
                    <div class="signature-text-right signature-nbm-up">NBM. {{ $suratPengantar->ketua_pkl_nbm ?? $defaultKetuaPklNbm ?? '-' }}</div>
                </td>
            </tr>
        </table>
        <br>
        <div style="margin-top: -8px; font-style: italic;">
            NB : Mohon maaf bila terjadi kekeliruan atau kurang lengkap dalam penulisan Nama dan Alamat Bengkel
        </div>
    </div>
</body>
</html>
