<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintAdminController extends Controller
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

        $query = Complaint::with(['user.residentialComplex'])
            ->whereHas('user.residentialComplex', function ($q) use ($admin, $request) {
                $q->where('client_id', $admin->client_id);
                if ($residentialComplexId = $request->query('residential_complex_id')) {
                    $q->where('id', $residentialComplexId);
                }
            });

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $complaints = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($complaints);
    }

    public function show($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $complaint = Complaint::with(['user.residentialComplex'])
            ->where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], 404);
        }

        return response()->json($complaint);
    }

    public function updateStatus($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $complaint = Complaint::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], 404);
        }

        $complaint->status = 'done';
        $complaint->save();

        return response()->json([
            'message'   => 'Complaint status updated to done',
            'complaint' => $complaint,
        ]);
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $complaint = Complaint::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$complaint) {
            return response()->json(['message' => 'Complaint not found'], 404);
        }

        $complaint->delete();

        return response()->json(['message' => 'Complaint deleted successfully']);
    }
}
