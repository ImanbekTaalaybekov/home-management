<?php

namespace App\Enums;

use Orchid\Platform\ItemPermission;

enum OrchidPermissionEnum: string
{
    case CITY_PERMISSION = 'platform.cities.edit'; // города
    case COMPANY_PERMISSION = 'platform.companies.edit'; // компании
    case BUILDING_PERMISSION = 'platform.buildings.edit'; // заведения
    case BUILDING_CATEGORY_PERMISSION = 'platform.building-categories.edit'; // категории заведений
    case CONSULTATION_PERMISSION = 'platform.consultations.edit'; // онлайн консультация
    case KNOWLEDGE_BASE_PERMISSION = 'platform.knowledge-base.edit'; // база знаний
    case NEWS_PERMISSION = 'platform.news.edit'; // новости
    case NEWS_CATEGORY_PERMISSION = 'platform.news-category.edit'; // категории новостей
    case RESUME_PERMISSION = 'platform.resume.edit'; // резюме
    case VACANCY_PERMISSION = 'platform.vacancy.edit'; // резюме
    case USER_PERMISSION = 'platform.systems.users'; // пользователи
    case ROLE_PERMISSION = 'platform.systems.roles'; // роли

    public static function rules(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group(__('Гео'))
                ->addPermission(self::CITY_PERMISSION->value, __('Города')),

            ItemPermission::group(__('Организации'))
                ->addPermission(self::COMPANY_PERMISSION->value, __('Компании')),

            ItemPermission::group(__('Контент'))
                ->addPermission(self::NEWS_CATEGORY_PERMISSION->value, __('Категории новостей'))
                ->addPermission(self::NEWS_PERMISSION->value, __('Новости'))
                ->addPermission(self::KNOWLEDGE_BASE_PERMISSION->value, __('База знаний')),

            ItemPermission::group(__('Места'))
                ->addPermission(self::BUILDING_PERMISSION->value, __('Заведения'))
                ->addPermission(self::BUILDING_CATEGORY_PERMISSION->value, __('Категории заведений')),

            ItemPermission::group(__('Карьера'))
                ->addPermission(self::VACANCY_PERMISSION->value, __('Вакансии'))
                ->addPermission(self::RESUME_PERMISSION->value, __('Резюме')),

            ItemPermission::group(__('Поддержка'))
                ->addPermission(self::CONSULTATION_PERMISSION->value, __('Онлайн консультации')),

        ];
    }
}
