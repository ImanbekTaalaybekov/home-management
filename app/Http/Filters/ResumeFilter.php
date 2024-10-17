<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class ResumeFilter extends Filter
{
    public const DESIRED_SALARY_AMOUNT = 'desired_salary_amount';
    public const DESIRED_SALARY_CURRENCY = 'desired_salary_currency';
    public const EMPLOYMENT_TYPES = 'employment_types';
    public const DATE_OF_BIRTH = 'date_of_birth';
    public const DISABILITY_GROUP = 'disability_group';
    public const DISABILITY_TYPE = 'disability_type';


    protected function getCallbacks(): array
    {
        return [
            self::DESIRED_SALARY_AMOUNT => [$this, 'desiredSalaryAmount'],
            self::DESIRED_SALARY_CURRENCY => [$this, 'desiredSalaryCurrency'],
            self::EMPLOYMENT_TYPES => [$this, 'employmentTypes'],
            self::DATE_OF_BIRTH => [$this, 'dateOfBirth'],
            self::DISABILITY_GROUP => [$this, 'disabilityGroup'],
            self::DISABILITY_TYPE => [$this, 'disabilityType'],
            ... parent::getCallbacks()
        ];
    }

    public function desiredSalaryAmount(Builder $builder, $value)
    {
        $builder->where('desired_salary_amount', $value);
    }

    public function desiredSalaryCurrency(Builder $builder, $value)
    {
        $builder->where('desired_salary_currency', $value);
    }

    public function employmentTypes(Builder $builder, $value)
    {
        $builder->whereJsonContains('employment_types', $value);
    }

    public function dateOfBirth(Builder $builder, $value)
    {
        $builder->whereYear('date_of_birth', $value);
    }

    public function disabilityGroup(Builder $builder, $value)
    {
        $builder->where('disability_group', $value);
    }

    public function disabilityType(Builder $builder, $value)
    {
        $builder->where('disability_type', $value);
    }
}
