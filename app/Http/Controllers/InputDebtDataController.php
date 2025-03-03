<?php

namespace App\Http\Controllers;

use App\Models\InputDebtDataAlseco;
use App\Models\InputDebtDataIvc;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InputDebtDataController extends Controller
{
    public function uploadAlseco(Request $request)
    {
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        foreach (array_slice($rows, 5) as $row) {

            InputDebtDataAlseco::create([
                'account_number' => $row['A'],
                'management_body_code' => $row['B'],
                'management_body_name' => $row['C'],
                'supplier_code' => $row['D'],
                'supplier_name' => $row['E'],
                'owner_full_name' => $row['F'],
                'region' => $row['G'],
                'locality' => $row['H'],
                'locality_part' => $row['I'],
                'house' => $row['J'],
                'apartment' => $row['K'],
                'service' => $row['L'],
                'debt_months_count' => $this->formatNumeric($row['M']),
                'last_payment_date' => $this->formatDate($row['N']),
                'debt_amount' => $this->formatNumeric($row['O']),
                'current_charges' => $this->formatNumeric($row['P']),
                'document_type' => $row['Q'],
                'document_date' => $this->formatDate($row['R']),
                'comment' => $row['S'],
            ]);
        }

        return response()->json(['message' => 'Файл успешно импортирован']);
    }

    private function formatNumeric($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[\xC2\xA0\s]/u', '', $value);
        $value = str_replace(',', '', $value);
        $value = preg_replace('/[^0-9.-]/', '', $value);

        if ($value === '') {
            return null;
        }

        return (float)$value;
    }

    private function formatDate($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uploadIvc(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();

            $rows = $sheet->toArray();

            foreach ($rows as $index => $row) {
                $excelRowIndex = $index + 1;

                if (!empty($row[1]) && $sheet->getStyle('B' . $excelRowIndex)->getFill()->getStartColor()->getRGB() === 'FFFFFF') {
                    $house = $this->getHouseValue($sheet, $excelRowIndex);

                    InputDebtDataIvc::create([
                        'account_number' => $row[0],
                        'apartment'     => $row[1],
                        'full_name'     => empty($row[2]) ? 'ФИО не указаны' : $row[2],
                        'phone'         => $row[3],
                        'service_name'  => $row[4],
                        'debt'          => $row[5],
                        'penalty'       => $row[6],
                        'house'         => $house,
                    ]);
                }
            }

            return response()->json(['message' => 'Файл успешно загружен и обработан']);
        }

        return response()->json(['message' => 'Файл не был загружен'], 400);
    }

    private function getHouseValue($sheet, $excelRowIndex)
    {
        $excelRowIndex = intval($excelRowIndex);

        for ($i = $excelRowIndex; $i >= 1; $i--) {
            $cell = 'A' . $i;
            if ($sheet->getStyle($cell)->getFill()->getStartColor()->getRGB() !== 'FFFFFF') {
                return $sheet->getCell('A' . ($i - 1))->getValue();
            }
        }
        return 'Неизвестный дом';
    }
}