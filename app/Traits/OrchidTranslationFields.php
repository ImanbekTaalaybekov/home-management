<?php

namespace App\Traits;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Model;

trait OrchidTranslationFields
{
    private static string $delimiter = ':';

    /**
     * @param array $fields
     * @return array
     */
    protected function translationFields(array $fields): array
    {
        // @TODO
        if (str_contains(request()->route()->getName(), 'platform.resource.')) {
            // from CRUD resource
            $model = request()->route()->getController()->request->findModel();
        } else {
            // from screen
            $model = \Arr::first(request()->route()->parameters());
        }

        $languages = Language::cases();
        $defaultLanguage = Language::default();

        $newFields = [];
        foreach ($fields as $field) {
            $attributes = $field->getAttributes();
            foreach ($languages as $language) {
                $translateField = \Arr::last(explode('.', $attributes['name']));

                $newFields[] = (clone $field)
                    ->required($language == $defaultLanguage)
                    ->name($attributes['name'] . self::$delimiter. $language->value)
                    ->title($language->icon() . ' ' . $attributes['title'])
                    ->value(
                        $model?->translate($translateField, $language->value, false)
                    );
            }
        }

        return $newFields;
    }

}