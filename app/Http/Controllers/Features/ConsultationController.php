<?php

namespace App\Http\Controllers\Features;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConsultationRequest;
use App\Http\Resources\Features\ConsultationResource;
use App\Models\Consultation;
use App\Services\ConsultationService;

class ConsultationController extends Controller
{
    public $service;

    public function __construct(ConsultationService $service)
    {
        $this->service = $service;
    }

    public function store(ConsultationRequest $request)
    {
        $data = $request->validated();

        $consultation = $this->service->create($data);

        return new ConsultationResource($consultation);
    }
}
