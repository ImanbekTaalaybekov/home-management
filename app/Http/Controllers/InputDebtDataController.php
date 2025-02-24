<?php

namespace App\Http\Controllers;

use App\Models\InputDebtData;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Settings;

class InputDebtDataController extends Controller
{
    public function upload(Request $request)
    {
        Settings::setLocale('ru_RU');

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        $rows = $sheet->toArray(null, true, true, true);

        $address = null;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex < 6) {
                continue;
            }

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

            if ($cellFill !== Fill::FILL_NONE) {
                $address = $account;
                continue;
            }

            if (empty($account)) {
                continue;
            }

            try {
                InputDebtData::create([
                    'account_number' => $account,
                    'full_name' => $fio,
                    'address' => $address,
                    'apartment_number' => $apartment,
                    'payment_date' => $this->parseDate($paymentDate),
                    'debt_month' => $debtMonth,
                    'housing_maintenance' => $this->parseNumber($housingCost),
                    'hot_water_sewage_meter' => $this->parseNumber($hotWaterSewageMeter),
                    'heating' => $this->parseNumber($heating),
                    'garbage_disposal' => $this->parseNumber($garbageDisposal),
                    'cold_water_meter' => $this->parseNumber($coldWaterMeter),
                    'electricity' => $this->parseNumber($electricity),
                    'hot_water_meter' => $this->parseNumber($hotWaterMeter),
                    'cold_water_sewage_meter' => $this->parseNumber($coldWaterSewageMeter),
                    'previous_debts' => $this->parseNumber($previousDebts),
                    'duty_lighting' => $this->parseNumber($dutyLighting),
                    'capital_repair' => $this->parseNumber($capitalRepair),
                    'total_utilities' => $this->parseNumber($totalUtilities),
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }

        InputDebtData::whereIn('account_number', [
            'ТОО "Управляющая Компания ZD" Код 1781',
            '0001',
            'ТОО "Управляющая компания ZD" Код 1792',
            'Лицевой счет'
        ])->delete();

        return response()->json(['message' => 'Файл успешно обработан и данные загружены'], 200);
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            $date = \DateTime::createFromFormat('d.m.Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }

            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    private function parseNumber($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = preg_replace('/\s+/', '', $value);
        $value = str_replace(',', '.', $value);

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}