<?php

namespace App\Http\Controllers;

use App\Models\InputDebtDataAlseco;
use App\Models\InputDebtDataUrta;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Settings;

class InputDebtDataController extends Controller
{
    public function uploadAlseco(Request $request)
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
                InputDebtDataAlseco::create([
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

        InputDebtDataAlseco::whereIn('account_number', [
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

    public function uploadUrta(Request $request)
    {
        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        foreach (array_slice($rows, 5) as $row) {
            InputDebtDataUrta::create([
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
                'debt_months_count' => $row['M'],
                'last_payment_date' => $row['N'],
                'debt_amount' => $row['O'],
                'current_charges' => $row['P'],
                'document_type' => $row['Q'],
                'document_date' => $row['R'],
                'comment' => $row['S'],
            ]);
        }

        return response()->json(['message' => 'Файл успешно импортирован']);
    }
}