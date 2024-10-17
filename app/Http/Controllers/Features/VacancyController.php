<?php

namespace App\Http\Controllers\Features;

use App\DTO\VacancyData;
use App\Enums\ResumeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Filters\VacancyFilter;
use App\Http\Requests\VacancyFilterRequest;
use App\Http\Resources\Features\CompanyResource;
use App\Http\Resources\Features\VacancyResource;
use App\Models\City;
use App\Models\Company;
use App\Models\Vacancy;
use App\Services\VacancyService;
use Illuminate\Http\Request;

class VacancyController extends Controller
{

    public function __construct(protected VacancyService $service)
    {

    }

    public function index(VacancyFilterRequest $request)
    {
        $data = $request->filteredData();

        $limit = request()->query('limit', 20);
        $page = request()->query('page', 1);
        $search = data_get($request->validated(), 'search', null);

        $filter = app()->make( VacancyFilter::class, ['queryParams' => array_filter($data)]);

        if($search !== null){
            $searchedVacancyIds = Vacancy::search($search)->get()->pluck('id')->toArray();
            $vacancies = Vacancy::filter($filter)->published()->whereIn('id', $searchedVacancyIds);
            $vacancies->orderByPosition($searchedVacancyIds);
        } else {
            $vacancies = Vacancy::filter($filter)->published();
        }

        return VacancyResource::collection($vacancies->paginate($limit, ['*'], 'page', $page));
    }

    public function vacancy(Vacancy $vacancy)
    {
        return new VacancyResource($vacancy);
    }

    public function show(Request $request)
    {
        $company = Company::where('publish_key', $request->publish_key)->firstOrFail();

        $company = new CompanyResource($company);

        $vacancy = new Vacancy;

        $cities = City::all();

        return view('vacancy.create_vacancy', ['company' => $company, 'vacancy' => $vacancy, 'cities' => $cities]);

    }

    public function store(Request $request)
    {
        $company = Company::where('publish_key', $request->publish_key)->firstOrFail();

        $input = $request->post(default: []);

        $input['company_id'] = $company->getKey();

        $input['status'] = ResumeStatusEnum::MODERATION->value;

        $input["skills"] = explode(',', $input["skills"]);

        VacancyData::validate($input);

        $vacancyData = VacancyData::from($input);

        $vacancy = $this->service->create($company, $vacancyData);

        return new VacancyResource($vacancy);
    }

    public function delete(Vacancy $vacancy, Request $request)
    {
        abort_unless($request->publish_key == $vacancy->company->publish_key, 403);

        $vacancy->delete();

        return response()->json(['success' => true]);

    }
}
