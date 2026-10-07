<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InputPembimbingWorkbookExport implements WithMultipleSheets
{
    public function __construct(
        private readonly Collection $pembimbings,
        private readonly string $tahunPkl,
        private readonly string $tahunPelajaran
    ) {}

    public function sheets(): array
    {
        if ($this->pembimbings->isEmpty()) {
            return [
                new class implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle {
                    public function title(): string
                    {
                        return 'Data Kosong';
                    }

                    public function array(): array
                    {
                        return [
                            ['Tidak ada data pembimbing yang cocok dengan filter.'],
                        ];
                    }
                },
            ];
        }

        return $this->pembimbings
            ->map(fn ($pembimbing) => new InputPembimbingSheetExport($pembimbing, $this->tahunPkl, $this->tahunPelajaran))
            ->all();
    }
}
