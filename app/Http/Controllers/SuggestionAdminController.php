<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestionAdminController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $query = Suggestion::with([
            'user.residentialComplex',
            'photos',
        ])
            ->whereHas('user.residentialComplex', function ($q) use ($admin, $request) {
                $q->where('client_id', $admin->client_id);

                if ($residentialComplexId = $request->query('residential_complex_id')) {
                    $q->where('id', $residentialComplexId);
                }
            });

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $suggestions = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($suggestions);
    }

    public function show($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $suggestion = Suggestion::with([
            'user.residentialComplex',
            'photos',
        ])
            ->where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$suggestion) {
            return response()->json(['message' => 'Suggestion not found'], 404);
        }

        return response()->json($suggestion);
    }

    public function updateStatus($id,NotificationService $notificationService)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $suggestion = Suggestion::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$suggestion) {
            return response()->json(['message' => 'Suggestion not found'], 404);
        }

        $suggestion->status = 'done';
        $suggestion->save();

        //Отправка уведомления
        $notificationService->sendPersonalNotification(
            $admin->client_id,
            $suggestion->user->personal_account,
            "Статус предложения обновлён",
            "Ваше предложение №{$suggestion->id} было обработано.",
            [],
            null,
            "technical",
            [
                "path" => "/suggestion/{$suggestion->id}",
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ]
        );

        return response()->json([
            'message'    => 'Suggestion status updated to done',
            'suggestion' => $suggestion,
        ]);
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $suggestion = Suggestion::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$suggestion) {
            return response()->json(['message' => 'Suggestion not found'], 404);
        }

        $suggestion->delete();

        return response()->json(['message' => 'Suggestion deleted successfully']);
    }
}