<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapKarirExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private array|Collection $data;
    private string $tipeKarir;

    public function __construct(array|Collection $data, string $tipeKarir = 'all')
    {
        $this->data = $data;
        $this->tipeKarir = strtolower($tipeKarir);
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $base = [
            'No',
            'Jurusan',
            'Kelas',
            'NIPD',
            'NISN',
            'Nama Siswa',
            'Jenis Kelamin',
            'No HP / WA',
            'Email Siswa',
            'Alamat Siswa (Dusun, Kelurahan, Kecamatan)',
            'Nama Bengkel PKL',
            'Nama Pemilik Bengkel',
            'Alamat Bengkel (Dusun, Desa, Kecamatan)',
            'Nama Guru Pembimbing PKL',
        ];

        if ($this->tipeKarir === 'kuliah') {
            return array_merge($base, [
                'Pilihan Karier',
                'Waktu Pengisian',
                'Target Perguruan Tinggi',
                'Status Kampus',
                'Jenjang Studi',
                'Program Studi',
                'Jalur Masuk',
                'Tahun Mulai',
            ]);
        }

        if ($this->tipeKarir === 'kerja') {
            return array_merge($base, [
                'Pilihan Karier',
                'Waktu Pengisian',
                'Target Perusahaan',
                'Posisi / Jabatan',
                'Jenis Pekerjaan',
                'Kesesuaian Jurusan',
                'Target Mulai Kerja',
                'Ekspektasi Gaji',
                'Lokasi Kerja',
            ]);
        }

        if ($this->tipeKarir === 'wirausaha') {
            return array_merge($base, [
                'Pilihan Karier',
                'Waktu Pengisian',
                'Nama Usaha',
                'Sektor Usaha',
                'Status Kepemilikan',
                'Modal Awal',
                'Lokasi Usaha',
            ]);
        }

        if ($this->tipeKarir === 'belum') {
            return array_merge($base, [
                'Pilihan Karier',
            ]);
        }

        // Default 'all'
        return array_merge($base, [
            'Pilihan Karier',
            'Waktu Pengisian',
            // Kuliah
            'Target Perguruan Tinggi',
            'Status Kampus',
            'Jenjang Studi',
            'Program Studi',
            'Jalur Masuk',
            'Tahun Mulai',
            // Kerja
            'Target Perusahaan',
            'Posisi / Jabatan',
            'Jenis Pekerjaan',
            'Kesesuaian Jurusan',
            'Target Mulai Kerja',
            'Ekspektasi Gaji',
            'Lokasi Kerja',
            // Wirausaha
            'Nama Usaha',
            'Sektor Usaha',
            'Status Kepemilikan',
            'Modal Awal',
            'Lokasi Usaha',
        ]);
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $karir = $row->siswaKarir;
        $tipe = $karir ? ucfirst($karir->tipe_karir ?? '-') : 'Belum Mengisi';
        $waktuPengisian = '-';
        if (!empty($karir?->updated_at)) {
            $waktuPengisian = Carbon::parse($karir->updated_at)->translatedFormat('d/m/Y H:i') . ' WIB';
        }

        $email = $row->user?->email ?: ($row->account?->email ?: '-');

        // Alamat Siswa
        $alamatSiswaParts = array_filter([$row->dusun, $row->kelurahan, $row->kecamatan]);
        $alamatSiswa = !empty($alamatSiswaParts) ? implode(', ', $alamatSiswaParts) : ($row->alamat ?: '-');

        // Data Penempatan PKL
        $penempatan = $row->penempatan;
        $bengkel = $penempatan?->bengkel;
        $pembimbing = $penempatan?->pembimbing;

        $namaBengkel = $bengkel?->nama_bengkel ?? '-';
        $pemilikBengkel = $bengkel?->nama_pemilik ?? '-';
        $alamatBengkelParts = array_filter([$bengkel?->dusun, $bengkel?->desa, $bengkel?->kecamatan]);
        $alamatBengkel = !empty($alamatBengkelParts) ? implode(', ', $alamatBengkelParts) : ($bengkel?->alamat ?? '-');
        $namaPembimbing = $pembimbing?->nama_guru ?? '-';

        $base = [
            $no,
            $row->kelas?->jurusan?->nama_jurusan ?? '-',
            $row->kelas?->nama_kelas ?? '-',
            $row->nipd ?? '-',
            $row->nisn ?? '-',
            $row->nama ?? '-',
            $row->jenis_kelamin ?? '-',
            $row->no_hp ?? '-',
            $email,
            $alamatSiswa,
            $namaBengkel,
            $pemilikBengkel,
            $alamatBengkel,
            $namaPembimbing,
        ];

        if ($this->tipeKarir === 'kuliah') {
            return array_merge($base, [
                $tipe,
                $waktuPengisian,
                $karir?->nama_universitas ?? '-',
                $karir?->status_universitas ?? '-',
                $karir?->jenjang_studi ?? '-',
                $karir?->program_studi ?? '-',
                $karir?->jalur_masuk ?? '-',
                $karir?->tahun_mulai ?? '-',
            ]);
        }

        if ($this->tipeKarir === 'kerja') {
            return array_merge($base, [
                $tipe,
                $waktuPengisian,
                $karir?->nama_perusahaan ?? '-',
                $karir?->posisi_kerja ?? '-',
                $karir?->jenis_pekerjaan ?? '-',
                $karir?->kesesuaian_jurusan ?? '-',
                $karir?->target_mulai_kerja ?? '-',
                $karir?->ekspektasi_gaji ?? '-',
                $karir?->lokasi_kerja ?? '-',
            ]);
        }

        if ($this->tipeKarir === 'wirausaha') {
            return array_merge($base, [
                $tipe,
                $waktuPengisian,
                $karir?->nama_usaha ?? '-',
                $karir?->sektor_usaha ?? '-',
                $karir?->status_kepemilikan ?? '-',
                $karir?->modal_awal ?? '-',
                $karir?->lokasi_usaha ?? '-',
            ]);
        }

        if ($this->tipeKarir === 'belum') {
            return array_merge($base, [
                'Belum Mengisi',
            ]);
        }

        // Default 'all'
        return array_merge($base, [
            $tipe,
            $waktuPengisian,
            // Kuliah
            $karir?->tipe_karir === 'kuliah' ? ($karir->nama_universitas ?? '-') : '-',
            $karir?->tipe_karir === 'kuliah' ? ($karir->status_universitas ?? '-') : '-',
            $karir?->tipe_karir === 'kuliah' ? ($karir->jenjang_studi ?? '-') : '-',
            $karir?->tipe_karir === 'kuliah' ? ($karir->program_studi ?? '-') : '-',
            $karir?->tipe_karir === 'kuliah' ? ($karir->jalur_masuk ?? '-') : '-',
            $karir?->tipe_karir === 'kuliah' ? ($karir->tahun_mulai ?? '-') : '-',
            // Kerja
            $karir?->tipe_karir === 'kerja' ? ($karir->nama_perusahaan ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->posisi_kerja ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->jenis_pekerjaan ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->kesesuaian_jurusan ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->target_mulai_kerja ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->ekspektasi_gaji ?? '-') : '-',
            $karir?->tipe_karir === 'kerja' ? ($karir->lokasi_kerja ?? '-') : '-',
            // Wirausaha
            $karir?->tipe_karir === 'wirausaha' ? ($karir->nama_usaha ?? '-') : '-',
            $karir?->tipe_karir === 'wirausaha' ? ($karir->sektor_usaha ?? '-') : '-',
            $karir?->tipe_karir === 'wirausaha' ? ($karir->status_kepemilikan ?? '-') : '-',
            $karir?->tipe_karir === 'wirausaha' ? ($karir->modal_awal ?? '-') : '-',
            $karir?->tipe_karir === 'wirausaha' ? ($karir->lokasi_usaha ?? '-') : '-',
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ]
            ],
        ];
    }
}
