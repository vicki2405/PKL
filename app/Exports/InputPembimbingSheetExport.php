<?php

namespace App\Exports;

use App\Models\Bengkel;
use App\Models\Pembimbing;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InputPembimbingSheetExport extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithEvents, WithTitle
{
    private const MIN_STUDENT_ROWS = 20;

    public function __construct(
        private readonly Pembimbing $pembimbing,
        private readonly string $tahunPkl,
        private readonly string $tahunPelajaran
    ) {}

    public function title(): string
    {
        $title = $this->pembimbing->id.' '.$this->pembimbing->nama_guru;
        $title = preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', ' ', $title);
        $title = trim((string) preg_replace('/\\s+/', ' ', $title));

        return mb_substr($title !== '' ? $title : 'Pembimbing', 0, 31);
    }

    public function array(): array
    {
        $placements = $this->placements();
        $studentRows = max(self::MIN_STUDENT_ROWS, $placements->count());

        $rows = [
            ['DAFTAR PESERTA PKL dan PEMBIMBING PKL '.$this->tahunPkl, '', '', '', '', '', 'NAMA PEMBIMBING', ''],
            ['SMKS MUHAMMADIYAH 2 GENTENG', '', '', '', '', '', $this->pembimbing->nama_guru, ''],
            ['TAHUN PELAJARAN '.$this->tahunPelajaran, '', '', '', '', '', 'Area Monitoring', ''],
            ['', '', '', '', '', '', $this->formatArea(), ''],
            ['', '', '', '', '', '', 'No Kontak', $this->pembimbing->no_hp ?: '-'],
            ['', '', '', '', '', '', 'Update', now()->locale('id')->translatedFormat('d F Y')],
            ['No', 'Nama Siswa', 'Kelas', 'No HP Siswa', 'Nama Bengkel', 'Nama Pemilik', 'No Kontak', 'Alamat'],
        ];

        for ($index = 0; $index < $studentRows; $index++) {
            $placement = $placements->get($index);
            $siswa = $placement?->siswa;
            $bengkel = $placement?->bengkel;

            $rows[] = [
                $index + 1,
                $siswa?->nama ?? '',
                $siswa?->kelas?->nama_kelas ?? '',
                $siswa?->no_hp ?? '',
                $bengkel?->nama_bengkel ?? '',
                $bengkel?->nama_pemilik ?? '',
                $bengkel?->no_hp ?? '',
                $this->formatBengkelAddress($bengkel),
            ];
        }

        $rows[] = ['', '', '', '', '', '', '', ''];
        $rows[] = ['Keterangan', '', '', '', '', '', '', ''];
        $rows[] = ['Jumlah Bengkel', '', '', $placements->pluck('bengkel_id')->filter()->unique()->count().' Bengkel', '', '', '', ''];
        $rows[] = ['Jumlah Siswa PKL', '', '', $placements->count().' Peserta', '', '', '', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $studentEndRow = 7 + max(self::MIN_STUDENT_ROWS, $this->placements()->count());

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');
                $sheet->mergeCells('A3:F3');
                $sheet->mergeCells('G1:H1');
                $sheet->mergeCells('G2:H2');
                $sheet->mergeCells('G3:H3');
                $sheet->mergeCells('G4:H4');

                $this->styleHeader($sheet);
                $this->styleTable($sheet, $studentEndRow);
                $this->styleNotes($sheet, $studentEndRow + 2, $highestRow);
                $this->setDimensions($sheet, $highestRow);
            },
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        $row = $cell->getRow();

        if (($column === 'D' || $column === 'G' || ($column === 'H' && $row === 5)) && $value !== null && $value !== '') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function placements(): Collection
    {
        return $this->pembimbing->penempatan_pkl
            ->sortBy(fn ($placement) => ($placement->bengkel?->nama_bengkel ?? '').'|'.($placement->siswa?->nama ?? ''))
            ->values();
    }

    private function formatArea(): string
    {
        $kecamatans = $this->placements()
            ->map(fn ($placement) => $placement->bengkel?->kecamatan)
            ->filter()
            ->unique()
            ->values();

        return $kecamatans->isNotEmpty() ? $kecamatans->join(' - ') : '-';
    }

    private function formatBengkelAddress(?Bengkel $bengkel): string
    {
        if (! $bengkel) {
            return '';
        }

        $alamat = $bengkel->getAttribute('alamat');
        if (is_string($alamat) && $alamat !== '') {
            return $alamat;
        }

        return collect([$bengkel->dusun ?? null, $bengkel->desa ?? null, $bengkel->kecamatan ?? null])
            ->filter()
            ->join(', ');
    }

    private function styleHeader(Worksheet $sheet): void
    {
        $blue = '16AEE2';

        $sheet->getStyle('A1:H7')->getFont()->setName('Times New Roman');
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setSize(11);
        $sheet->getStyle('A1:F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('G1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($blue);
        $sheet->getStyle('G3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($blue);
        $sheet->getStyle('G1:H6')->getFont()->setBold(true);
        $sheet->getStyle('G1:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G5:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G1:H6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function styleTable(Worksheet $sheet, int $studentEndRow): void
    {
        $sheet->getStyle('A7:H'.$studentEndRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A7:H7')->getFont()->setBold(true);
        $sheet->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:A'.$studentEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C8:C'.$studentEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D8:D'.$studentEndRow)->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('G8:G'.$studentEndRow)->getNumberFormat()->setFormatCode('@');
    }

    private function styleNotes(Worksheet $sheet, int $notesStartRow, int $highestRow): void
    {
        $sheet->getStyle('A'.$notesStartRow.':H'.$highestRow)->getFont()->setName('Times New Roman');
        $sheet->getStyle('A'.$notesStartRow)->getFont()->setBold(true);
    }

    private function setDimensions(Worksheet $sheet, int $highestRow): void
    {
        foreach (range(1, $highestRow) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(16);
        }

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(24);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(28);
    }
}
