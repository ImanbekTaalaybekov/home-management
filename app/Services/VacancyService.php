<?php

namespace App\Services;

use App\DTO\VacancyData;
use App\Models\Company;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

class VacancyService
{
  public function create(Company $company, VacancyData $vacancyData)
  {
      $vacancy = new Vacancy();

      $vacancy->company()->associate($company);

      $input = $vacancyData->toArray();

      $vacancy->fill(\Arr::except($input, ['images']));

      DB::transaction(function() use ($vacancy, $vacancyData) {

          $vacancy->save();

          if ($vacancyData->images) {
              $vacancy->addMedia(\Storage::disk('local')->path($vacancyData->images))->toMediaCollection('vacancy');
          }
      });

      return $vacancy;
  }
}
