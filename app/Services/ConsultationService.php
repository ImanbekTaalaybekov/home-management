<?php

namespace App\Services;

use App\Models\Consultation;
use Illuminate\Support\Facades\DB;

class ConsultationService
{
    public function create($data): ?Consultation
    {
        $consultation = new Consultation();

        $consultation->fill($data);

        DB::transaction(function() use ($consultation) {

            $consultation->save();

        });

        return $consultation;
    }
}
