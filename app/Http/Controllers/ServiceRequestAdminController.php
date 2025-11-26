<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestCategory;
use App\Models\ServiceRequestMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceRequestAdminController extends Controller
{
    public function indexRequests(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $query = ServiceRequest::with(['user.residentialComplex'])
            ->whereHas('user.residentialComplex', function ($q) use ($admin, $request) {
                $q->where('client_id', $admin->client_id);
                if ($residentialComplexId = $request->query('residential_complex_id')) {
                    $q->where('id', $residentialComplexId);
                }
            });

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $requests = $query
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    public function assignMaster(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'master_id' => 'required|integer',
            'status'    => 'nullable|string',
        ]);

        $serviceRequest = ServiceRequest::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Service request not found'], 404);
        }

        $master = ServiceRequestMaster::where('id', $request->master_id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$master) {
            return response()->json(['message' => 'Master not found'], 404);
        }

        $serviceRequest->master_id = $master->id;

        if ($request->filled('status')) {
            $serviceRequest->status = $request->status;
        } else {
            $serviceRequest->status = 'in_progress';
        }

        $serviceRequest->save();

        return response()->json([
            'message' => 'Master assigned successfully',
            'data'    => $serviceRequest,
        ]);
    }

    public function destroyRequest($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $serviceRequest = ServiceRequest::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Service request not found'], 404);
        }

        $serviceRequest->delete();

        return response()->json(['message' => 'Service request deleted successfully']);
    }

    public function updateRequestStatus(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,done',
        ]);

        $serviceRequest = ServiceRequest::where('id', $id)
            ->whereHas('user.residentialComplex', function ($q) use ($admin) {
                $q->where('client_id', $admin->client_id);
            })
            ->first();

        if (!$serviceRequest) {
            return response()->json(['message' => 'Service request not found'], 404);
        }

        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        return response()->json([
            'message' => 'Статус успешно обновлён',
            'data'    => $serviceRequest,
        ]);
    }

    public function indexCategories()
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $categories = ServiceRequestCategory::where('client_id', $admin->client_id)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function storeCategory(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'name_rus' => 'required|string|max:255',
        ]);

        $category = ServiceRequestCategory::create([
            'name'      => $request->name,
            'name_rus'  => $request->name_rus,
            'client_id' => $admin->client_id,
        ]);

        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'name'     => 'nullable|string|max:255',
            'name_rus' => 'nullable|string|max:255',
        ]);

        $category = ServiceRequestCategory::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $data = [];
        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }
        if ($request->filled('name_rus')) {
            $data['name_rus'] = $request->name_rus;
        }

        if (!empty($data)) {
            $category->update($data);
        }

        return response()->json($category);
    }

    public function destroyCategory($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $category = ServiceRequestCategory::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

    public function indexMasters()
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $masters = ServiceRequestMaster::with('category') // если есть связь
        ->where('client_id', $admin->client_id)
            ->orderBy('name')
            ->get();

        return response()->json($masters);
    }

    public function storeMaster(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'name'                        => 'required|string|max:255',
            'service_request_category_id' => 'nullable|exists:service_request_categories,id',
        ]);

        if ($request->filled('service_request_category_id')) {
            $category = ServiceRequestCategory::where('id', $request->service_request_category_id)
                ->where('client_id', $admin->client_id)
                ->first();

            if (!$category) {
                return response()->json(['message' => 'Category not found or forbidden'], 404);
            }
        }

        $master = ServiceRequestMaster::create([
            'name'                        => $request->name,
            'service_request_category_id' => $request->service_request_category_id,
            'client_id'                   => $admin->client_id,
        ]);

        return response()->json($master, 201);
    }

    public function updateMaster(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $request->validate([
            'name'                        => 'nullable|string|max:255',
            'service_request_category_id' => 'nullable|exists:service_request_categories,id',
        ]);

        $master = ServiceRequestMaster::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$master) {
            return response()->json(['message' => 'Master not found'], 404);
        }

        $data = [];
        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }
        if ($request->has('service_request_category_id')) {
            if ($request->service_request_category_id) {
                $category = ServiceRequestCategory::where('id', $request->service_request_category_id)
                    ->where('client_id', $admin->client_id)
                    ->first();

                if (!$category) {
                    return response()->json(['message' => 'Category not found or forbidden'], 404);
                }

                $data['service_request_category_id'] = $request->service_request_category_id;
            } else {
                $data['service_request_category_id'] = null;
            }
        }

        if (!empty($data)) {
            $master->update($data);
        }

        return response()->json($master);
    }

    public function destroyMaster($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $master = ServiceRequestMaster::where('id', $id)
            ->where('client_id', $admin->client_id)
            ->first();

        if (!$master) {
            return response()->json(['message' => 'Master not found'], 404);
        }

        $master->delete();

        return response()->json(['message' => 'Master deleted successfully']);
    }
}