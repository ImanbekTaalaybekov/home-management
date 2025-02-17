<?php

namespace App\Http\Controllers;

use App\Models\InputDebtData;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InputDebtDataController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx']);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);

        foreach ($rows as $rowIndex => $row) {
            $account = $row['A'] ?? null;
            $fio = $row['B'] ?? null;
            $apartment = $row['D'] ?? null;
            $paymentDate = $row['E'] ?? null;
            $debtMonth = $row['F'] ?? null;
            $housingCost = $row['G'] ?? null;
            $hotWaterSewageMeter = $row['I'] ?? null;
            $heating = $row['K'] ?? null;
            $garbageDisposal = $row['M'] ?? null;
            $coldWaterMeter = $row['O'] ?? null;
            $electricity = $row['S'] ?? null;
            $hotWaterMeter = $row['V'] ?? null;
            $coldWaterSewageMeter = $row['X'] ?? null;
            $previousDebts = $row['Z'] ?? null;
            $dutyLighting = $row['AB'] ?? null;
            $capitalRepair = $row['AD'] ?? null;
            $totalUtilities = $row['AH'] ?? null;

            $cellFill = $sheet->getStyle("A{$rowIndex}")->getFill()->getFillType();
            if (empty($account) || $cellFill !== Fill::FILL_NONE) {
                continue;
            }

            $address = null;
            for ($i = $rowIndex - 1; $i > 0; $i--) {
                $cellFill = $sheet->getStyle("A{$i}")->getFill()->getFillType();
                if ($cellFill !== Fill::FILL_NONE) {
                    $address = $sheet->getCell("A{$i}")->getValue();
                    break;
                }
            }

            InputDebtData::create([
                'account_number' => $account,
                'full_name' => $fio,
                'address' => $address,
                'apartment_number' => $apartment,
                'payment_date' => $paymentDate,
                'debt_month' => $debtMonth,
                'housing_maintenance' => $housingCost,
                'hot_water_sewage_meter' => $hotWaterSewageMeter,
                'heating' => $heating,
                'garbage_disposal' => $garbageDisposal,
                'cold_water_meter' => $coldWaterMeter,
                'electricity' => $electricity,
                'hot_water_meter' => $hotWaterMeter,
                'cold_water_sewage_meter' => $coldWaterSewageMeter,
                'previous_debts' => $previousDebts,
                'duty_lighting' => $dutyLighting,
                'capital_repair' => $capitalRepair,
                'total_utilities' => $totalUtilities,
            ]);
        }

        return response()->json(['message' => 'Файл успешно обработан и данные загружены'], 200);
    }
}
