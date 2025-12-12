<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\ResidentialComplex;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;

class   PollAdminController extends Controller
{
    public function store(Request $request,NotificationService $notificationService)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'residential_complex_id' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if ($request->filled('residential_complex_id')) {
            $complex = ResidentialComplex::where('id', $request->residential_complex_id)
                ->where('client_id', $admin->client_id)
                ->first();

            if (!$complex) {
                return response()->json([
                    'message' => 'ЖК не принадлежит текущему клиенту'
                ], 403);
            }
        }

        $poll = Poll::create([
            'title' => $request->title,
            'description' => $request->description,
            'residential_complex_id' => $request->residential_complex_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        // Уведы всем пользователям жк (residential_complex_id) при создании нового голосования
        if (!empty($validated['residential_complex_id'])) {
            $notificationService->sendComplexNotification(
                clientId: $admin->client_id,
                complexId: (int)$validated['residential_complex_id'],
                title: 'Новое голосование',
                message: "Открыт новый опрос: {$poll->title}",
                photos: [],
                document: null,
                category: 'poll',
                data: [
                    'path' => "/polls/{$poll->id}",
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            );
        } else {
            // Если ЖК не указан — можно разослать всем пользователям клиента (по всем ЖК клиента)
            $notificationService->sendGlobalNotification(
                clientId: $admin->client_id,
                title: 'Новое голосование',
                message: "Открыт новый опрос: {$poll->title}",
                photos: [],
                document: null,
                category: 'poll',
                data: [
                    'path' => "/polls/{$poll->id}",
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]
            );
        }

        return response()->json([
            'message' => 'Опрос успешно создан',
            'data' => $poll,
        ], 201);
    }

    public function index(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $query = Poll::with('residentialComplex')
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            });

        if ($request->filled('residential_complex_id')) {
            $query->where('residential_complex_id', $request->residential_complex_id);
        }

        $polls = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($polls);
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $poll = Poll::where('id', $id)
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            })
            ->first();

        if (!$poll) {
            return response()->json(['message' => 'Опрос не найден'], 404);
        }

        $poll->delete();

        return response()->json([
            'message' => 'Опрос удалён'
        ]);
    }

    public function generateProtocol(Poll $poll)
    {
        $votes = $poll->votes()->with('user')->get();

        $yesVotes = $votes->where('vote', 'yes');
        $noVotes = $votes->where('vote', 'no');
        $abstainVotes = $votes->where('vote', 'abstain');

        $data = [
            'poll' => $poll,
            'totalVotes' => $votes->count(),
            'yesCount' => $yesVotes->count(),
            'noCount' => $noVotes->count(),
            'abstainCount' => $abstainVotes->count(),
            'yesVoters' => $yesVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'noVoters' => $noVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'abstainVoters' => $abstainVotes->map(fn($vote) => $vote->user->name ?? '—'),
            'residentialComplex' => $poll->residentialComplex,
            'votes' => $votes,
        ];

        $pdfContent = Pdf::loadView('pdf.poll_protocol', $data)->output();
        $generatedPdfPath = storage_path("app/temp_generated_protocol_{$poll->id}.pdf");
        file_put_contents($generatedPdfPath, $pdfContent);

        $headerPdfPath = base_path('admin_panel/protocol/protocol-head.pdf');

        $mergedPdf = new Fpdi();

        foreach ([$headerPdfPath, $generatedPdfPath] as $file) {
            $pageCount = $mergedPdf->setSourceFile($file);
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $mergedPdf->importPage($i);
                $size = $mergedPdf->getTemplateSize($templateId);

                $mergedPdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $mergedPdf->useTemplate($templateId);
            }
        }

        $finalPath = storage_path("app/final_protocol_{$poll->id}.pdf");
        $mergedPdf->Output($finalPath, 'F');

        @unlink($generatedPdfPath);

        return response()->download($finalPath)->deleteFileAfterSend(true);
    }
}