<?php

namespace App\Http\Controllers;

use App\Models\InputDebtDataAlseco;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Settings;

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