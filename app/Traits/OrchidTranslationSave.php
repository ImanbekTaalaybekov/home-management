<?php

namespace App\Traits;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Model;

trait OrchidTranslationSave
{
    private static string $delimiter = ':';

    /**
     * @param Model $model
     * @param array $data
     * @return void
     */
    protected function fillTranslation(Model $model, array $data): void
    {
        $translationValues = [];

        foreach ($model->getTranslatableAttributes() as $field) {

            $translationValues[$field] = [];

            foreach (Language::cases() as $language) {
                $translationValues[$field][$language->value] = \Arr::get($data, $field . self::$delimiter . $language->value);
            }
        }

        foreach ($translationValues as $field => $values) {
            $model->replaceTranslations($field, $values);
        }
    }
}