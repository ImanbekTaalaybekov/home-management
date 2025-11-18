<?php

namespace App\Http\Controllers;

use App\Models\AppVersion;
use Illuminate\Http\Request;

class AppVersionController extends Controller
{
    public function updateVersion(Request $request)
    {
        $validated = $request->validate([
            'version'  => 'required|string',
            'platform' => 'required|in:ios,android',
        ]);

        $appVersion = AppVersion::updateOrCreate(
            ['platform' => $validated['platform']],
            ['version' => $validated['version']]
        );

        return response()->json([
            'message' => 'Версия сохранена/обновлена',
            'data'    => $appVersion,
        ]);
    }

    public function showLastVersion(Request $request)
    {
        $platform = $request->query('platform');
        if ($platform) {
            $version = AppVersion::where('platform', $platform)->first();

            return response()->json([
                'platform' => $platform,
                'version'  => $version->version ?? null,
            ]);
        }

        $ios = AppVersion::where('platform', 'ios')->first();
        $android = AppVersion::where('platform', 'android')->first();

        return response()->json([
            'ios'      => $ios->version ?? null,
            'android'  => $android->version ?? null,
        ]);
    }
}