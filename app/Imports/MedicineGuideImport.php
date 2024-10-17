<?php
namespace App\Imports;

use App\Models\MedicineGuide;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMappedCells;

class MedicineGuideImport implements ToModel
{
    public function model(array $row)
    {
        return new MedicineGuide([
            'common_name' => $row[1], // Значение из 2-го столбца
            'company_name' => $row[3], // Значение из 4-го столбца
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function startRow(): int
    {
        return 2; // Начало чтения со второй строки, чтобы пропустить шапку
    }

    public function onlySheets(): array
    {
        return ['UniN']; // Чтение только из листа с названием UniN
    }
}

