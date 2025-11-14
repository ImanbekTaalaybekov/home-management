<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function updateVersion(Request $request)
    {
        $version = $request->query('version');

        if (!$version) {
            return response()->json([
                'error' => 'Не указана версия'
            ], 422);
        }

        $appVersion = AppVersion::updateOrCreate(
            ['version' => $version],
            []
        );

        return response()->json([
            'message' => 'Версия сохранена/обновлена',
            'data'    => $appVersion,
        ]);
    }

    public function showLastVersion()
    {
        $last = AppVersion::select('version')
            ->orderByRaw("
            CAST(split_part(version, '.', 1) AS INTEGER) DESC,
            CAST(split_part(version, '.', 2) AS INTEGER) DESC,
            CAST(
                COALESCE(NULLIF(split_part(version, '.', 3), ''), '0')
                AS INTEGER
            ) DESC
        ")
            ->first();

        return response()->json([
            'version' => $last->version ?? null,
        ]);
    }
}