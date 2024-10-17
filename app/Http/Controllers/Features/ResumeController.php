<?php

namespace App\Http\Controllers\Features;

use App\DTO\ResumeData;
use App\Http\Controllers\Controller;
use App\Http\Filters\ResumeFilter;
use App\Http\Requests\ResumeFilterRequest;
use App\Http\Resources\Features\ResumeResource;
use App\Models\Resume;
use App\Services\ResumeService;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public $service;

    public function __construct(ResumeService $service)
    {
        $this->service = $service;
    }

    public function index(ResumeFilterRequest $request)
    {
        $data = $request->filteredData();

        $limit = request()->query('limit', 20);
        $page = request()->query('page', 1);
        $search = data_get($request->validated(), 'search', null);

        $filter = app()->make(ResumeFilter::class, ['queryParams' => array_filter($data)]);

        if ($search !== null) {
            $searchedResumeIds = Resume::search($search)->get()->pluck('id')->toArray();
            $resumes = Resume::filter($filter)->published()->whereIn('id', $searchedResumeIds);
            $resumes->orderByPosition($searchedResumeIds);
        } else {
            $resumes = Resume::filter($filter)->published();
        }

        return ResumeResource::collection($resumes->paginate($limit, ['*'], 'page', $page));
    }

    public function store(Request $request)
    {
        $input = $request->post(default: []);

        $resumeData = ResumeData::from($input);

        $userResume = $request->user()->resume;
        if ($userResume !== null) {
            $resume = $this->service->update($userResume, $resumeData);
        } else {
            $resume = $this->service->create($request->user(), $resumeData);
        }

        return new ResumeResource($resume);
    }

    public function update(Request $request)
    {
        $input = $request->post(default: []);

        $resumeData = ResumeData::from($input);

        $resume = $request->user()->resume;

        $resume = $this->service->update($resume, $resumeData);

        return new ResumeResource($resume);
    }

    public function resume(Resume $resume)
    {
        return new ResumeResource($resume);
    }

    public function delete(Request $request)
    {
        $request->user()->resume()->delete();

        return response()->json(['success' => true]);
    }

    public function currentUser(Request $request)
    {
        $user = $request->user();

        $resume = $user->resume()->first();

        if ($resume === null) {
            return response()->json(['data' => null], 200);
        }

        return new ResumeResource($resume);
    }
}
