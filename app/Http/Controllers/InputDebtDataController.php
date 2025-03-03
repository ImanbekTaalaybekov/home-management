<?php

namespace App\Http\Controllers;

use App\Models\InputDebtDataAlseco;
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
            return \Carbon\Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}