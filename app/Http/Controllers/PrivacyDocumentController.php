<?php

namespace App\Http\Controllers;

use App\Models\PrivacyDocument;
use Illuminate\Http\Request;

class PrivacyDocumentController extends Controller
{
    public function getUserAgreement()
    {
        $document = PrivacyDocument::where('name', 'user-agreement')->first();

        if (!$document || !$document->file) {
            return response()->json(['error' => 'Документ не найден'], 404);
        }

        $filePath = storage_path('app_documents/' . $document->file);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        return response()->file($filePath);
    }

    public function getPrivacyPolicy()
    {
        $document = PrivacyDocument::where('name', 'privacy-policy')->first();

        if (!$document || !$document->file) {
            return response()->json(['error' => 'Документ не найден'], 404);
        }

        $filePath = storage_path('app_documents/' . $document->file);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Файл не найден'], 404);
        }

        return response()->file($filePath);
    }
}
