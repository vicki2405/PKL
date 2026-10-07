<!DOCTYPE html>
<html>
<head>
    <title>Form P-1 Biodata PKL</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.3;
        }
        .header-image {
            width: 100%;
            display: block;
            margin-bottom: 10px;
        }
        .form-label {
            display: inline-block;
            border: 1px solid #000;
            padding: 5px 15px;
            font-weight: bold;
            font-size: 12pt;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            line-height: 1.2;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 12pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table td {
            vertical-align: top;
            padding: 4px 0;
        }
        .label-col {
            width: 220px;
        }
        .separator-col {
            width: 20px;
        }
        .solid-line {
            border-bottom: 1px solid #000;
            display: block;
            width: 100%;
            margin-bottom: 8px;
            height: 25px;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            margin-top: 80px;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <img src="{{ public_path('kopsurat.jpg') }}" class="header-image" alt="Kop Surat">

    <!-- Title & Form Label -->
    <table style="width: 100%; margin-top: 5px; margin-bottom: 25px;">
        <tr>
            <td style="width: 25%;"></td>
            <td class="title">
                BIODATA PRAKTIK KERJA LAPANGAN<br>
                SMKS MUHAMMADIYAH 2 GENTENG<br>
                TAHUN {{ date('Y') }}/{{ date('Y') + 1 }}
            </td>
            <td style="width: 25%; vertical-align: top; text-align: right;">
                <span class="form-label" style="margin-right: 30px;">Form : P-1</span>
            </td>
        </tr>
    </table>

    <!-- Content -->
    <div style="margin-left: 50px;">
        <div class="section-title">BIODATA PESERTA :</div>
        <table class="data-table">
            <tr>
                <td class="label-col">Nama Peserta</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->siswa->nama }}</td>
            </tr>
            <tr>
                <td class="label-col">Kelas</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->siswa->kelas->nama_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">No. Telp Peserta</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->siswa->no_hp ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">No. Telp Wali</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->siswa->kelas->no_wa_wali ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">DATA BENGKEL TEMPAT PRAKTIK KERJA LAPANGAN :</div>
        <table class="data-table">
            <tr>
                <td class="label-col">Nama Bengkel</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->bengkelAsal->nama_bengkel ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Nama Pemilik Bengkel</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->bengkelAsal->nama_pemilik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">No. Telp / HP</td>
                <td class="separator-col">:</td>
                <td>{{ $mutasi->bengkelAsal->no_hp ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Alamat Bengkel</td>
                <td class="separator-col">:</td>
                <td>
                    @if($mutasi->bengkelAsal)
                        {{ $mutasi->bengkelAsal->dusun ? $mutasi->bengkelAsal->dusun . ', ' : '' }}
                        {{ $mutasi->bengkelAsal->desa ? $mutasi->bengkelAsal->desa . ', ' : '' }}
                        {{ $mutasi->bengkelAsal->kecamatan ? 'Kec. ' . $mutasi->bengkelAsal->kecamatan : '' }}
                        {{ $mutasi->bengkelAsal->deskripsi ? '(' . $mutasi->bengkelAsal->deskripsi . ')' : '' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-title">ALASAN PINDAH BENGKEL</div>
        <div style="margin-bottom: 20px; margin-right: 50px;">
            <div class="solid-line">{{ $mutasi->alasan }}</div>
            <div class="solid-line"></div>
            <div class="solid-line"></div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    <br>
                    Pimpinan Bengkel
                    <div class="signature-name">(...........................................)</div>
                </td>
                <td style="width: 50%;">
                    Banyuwangi, {{ $tanggalSurat ?? now()->translatedFormat('d F Y') }}<br>
                    Peserta PKL
                    <div class="signature-name">( {{ $mutasi->siswa->nama ?? '...........................................' }} )</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 30px;">
                    Mengetahui<br>
                    Pembimbing PKL
                    <div class="signature-name">( {{ $mutasi->pembimbing->nama_guru ?? '...........................................' }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
