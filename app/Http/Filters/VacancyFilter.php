<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class VacancyFilter extends Filter
{
    public const DISABILITY_TYPE = 'disability_type';
    public const SALARY_FROM_AMOUNT = 'salary_from_amount';
    public const SALARY_TO_AMOUNT = 'salary_to_amount';
    public const SALARY_CURRENCY = 'salary_currency';
    public const ACTIVITY = 'activity';
    public const EMPLOYMENT_TYPES = 'employment_types';
    public const EXPERIENCE_LEVEL = 'experience_level';
    public const CREATED_AT = 'created_at';

    protected function getCallbacks(): array
    {
        return [
            self::DISABILITY_TYPE => [$this, 'disabilityType'],
            self::SALARY_FROM_AMOUNT => [$this, 'salaryFromAmount'],
            self::SALARY_TO_AMOUNT => [$this, 'salaryToAmount'],
            self::SALARY_CURRENCY => [$this, 'salaryCurrency'],
            self::ACTIVITY => [$this, 'activity'],
            self::EMPLOYMENT_TYPES => [$this, 'employmentTypes'],
            self::EXPERIENCE_LEVEL => [$this, 'experienceLevel'],
            self::CREATED_AT => [$this, 'createdAt'],
            ... parent::getCallbacks()
        ];
    }



    public function salaryFromAmount(Builder $builder, $value)
    {
        $builder->where('salary_from_amount', '>', $value);
    }

    public function salaryToAmount(Builder $builder, $value)
    {
        $builder->where('salary_to_amount', '<', $value);
    }

    public function salaryCurrency(Builder $builder, $value)
    {
        $builder->where('salary_currency', $value);
    }

    public function activity(Builder $builder, $value)
    {
        $builder->where('activity', 'like', "%{$value}%");
    }

    public function employmentTypes(Builder $builder, $value)
    {
        $builder->whereJsonContains('employment_types', $value);
    }

    public function experienceLevel(Builder $builder, $value)
    {
        $builder->where('experience_level', $value);
    }

    public function createdAt(Builder $builder, $value)
    {
        switch ($value) {
            case 'today':
                $builder->whereDate('created_at', Carbon::today());
                break;

            case 'week':
                $builder->whereDate('created_at', '>=', Carbon::today()->subDays(7));
                break;

            case 'month':
                $builder->whereDate('created_at', '>=', Carbon::today()->subMonth());
                break;

            case 'three_month':
                $builder->whereDate('created_at', '>=', Carbon::today()->subMonths(3));
                break;

            case 'half_year':
                $builder->whereDate('created_at', '>=', Carbon::today()->subMonths(6));
                break;
        }
    }
}
