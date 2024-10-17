<?php

namespace App\Services;

use App\DTO\ResumeData;
use App\DTO\ResumeEducationData;
use App\DTO\ResumeWorkExperienceData;
use App\Models\Resume;
use App\Models\ResumeEducation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ResumeService
{
    public function create(User $user, ResumeData $resumeData): ?Resume
    {
        if ($this->exists($user)) {
            return null;
        }

        $resume = new Resume();

        $resume->user()->associate($user);

        $input = $resumeData->toArray();

        $resume->fill(\Arr::except($input, ['educations', 'experiences', 'image']));

        DB::transaction(function() use ($resume, $resumeData) {

            $resume->save();

            $this->bindExternalData($resume, $resumeData);

            if ($resumeData->image) {
                $resume->addMedia(\Storage::disk('local')->path($resumeData->image))->toMediaCollection('resume_avatar');
            }
        });

        return $resume;
    }

    public function update(Resume $resume, ResumeData $updateData): ?Resume
    {
        DB::transaction(function() use ($resume, $updateData) {
            $resume->update($updateData->toArray());

            $resume->resumeEducation()->delete();
            $resume->resumeWorkExperience()->delete();

            $this->bindExternalData($resume, $updateData);

            if ($updateData->image) {
                $resume->clearMediaCollection('resume_avatar');
                $resume->addMedia(\Storage::disk('local')->path($updateData->image))->toMediaCollection('resume_avatar');
            }
        });

        return $resume;
    }

    public function exists(User $user): bool
    {
        return $user->resume !== null;
    }

    protected function bindExternalData(Resume $resume, ResumeData $data): void
    {
        /**
         * @var $education ResumeEducationData
         */
        foreach ($data->educations as $education) {
            $resume->resumeEducation()->create($education);
        }

        /**
         * @var $experience ResumeWorkExperienceData
         */
        foreach ($data->experiences as $experience) {
            $resume->resumeWorkExperience()->create($experience);
        }
    }
}
