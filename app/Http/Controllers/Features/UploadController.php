<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function image(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:8192',
        ]);

        $image = $request->file('image');

        $imagePath = $image->store('tmp', ['disk' => 'local']);

        return response()->json(['url' => $imagePath]);
    }

    public function document(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:mp3,acc,mp4,mov,docx,xlsx,xls|max:15360',
        ]);

        $document = $request->file('document');

        $documentPath = $document->store('documents', ['disk' => 'local']);

        return response()->json(['url' => $documentPath]);
    }
}
