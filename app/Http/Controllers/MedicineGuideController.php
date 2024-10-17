<?php

namespace App\Http\Controllers;

use App\Imports\MedicineGuideImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MedicineGuideController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx']);
        $file = $request->file('file');

        Excel::import(new MedicineGuideImport, $file);

        return response()->json(['message' => 'Данные успешно загружены'], 200);
    }
}
