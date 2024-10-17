<?php

namespace App\OpenApi\Manage;

use App\Http\Controllers\Controller;


class VacancyController extends Controller
{

/**
 *
 * @OA\Get(
 *     path="/api/v1/vacancy",
 *     summary = "Получение списка вакансий по дате обновления",
 *     tags={"Вакансии"},
 *   @OA\Parameter(
 *     name="limit",
 *     in="query",
 *     description="Лимит на странице (default 20)",
 *     required=false,
 *     @OA\Schema(type="integer")
 *       ),
 *   @OA\Parameter(
 *     name="page",
 *     in="query",
 *     description="Номер страницы",
 *     required=false,
 *     @OA\Schema(type="integer")
 *     ),
 *   @OA\Parameter(
 *     name="search",
 *     in="query",
 *     description="Поисковой запрос",
 *     required=false,
 *     @OA\Schema(type="string", minLength=2)
 *     ),
 *   @OA\Parameter(
 *     name="filter",
 *     in="query",
 *     description="Фильтры для поиска",
 *     required=false,
 *     @OA\Schema(
 *         type="object",
 *         @OA\Property(
 *             property="category_id",
 *             type="integer",
 *             description="ID категории"
 *         ),
 *         @OA\Property(
 *             property="favorite",
 *             type="integer",
 *             description="Показать избранные 1-да 0 -нет"
 *         ),
 *        @OA\Property(
 *             property="salary_from_amount",
 *             type="number",
 *             format="float",
 *             description="Заработная плата от"
 *          ),
 *        @OA\Property(
 *             property="salary_to_amount",
 *             type="number",
 *             format="float",
 *             description="Заработная плата до"
 *          ),
 *        @OA\Property(
 *             property="salary_currency",
 *             type="string",
 *             description="Валюта"
 *          ),
 *        @OA\Property(
 *             property="activity",
 *             type="string",
 *             description="сфера деятельности"
 *          ),
 *        @OA\Property(
 *             property="employment_types",
 *             type="array",
 *             description="Виды занятости",
 *             @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})
 *          ),
 *        @OA\Property(
 *             property="experience_level",
 *             type="string",
 *             enum={"without_experience", "small", "medium", "large"},
 *             description="Опыт работы",
 *         ),
 *        @OA\Property(
 *             property="created_at",
 *             type="string",
 *             description="размещено"
 *        ),
 *       ),
 *     style="deepObject",
 *     explode=true
 *     ),
 *    @OA\Response(
 *          response=200,
 *          description="OK",
 *          @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(
 *                   @OA\Property(property="title", type="string", example="John Doe"),
 *                   @OA\Property(property="description", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
 *                   @OA\Property(property="requirements", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
 *                   @OA\Property(property="responsibilities", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
 *                   @OA\Property(property="company", type="object",
 *                                @OA\Property(property="name", type="string", example="Google"),
 *                                @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true), nullable=true),
 *                                @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
 *                                @OA\Property(property="phone", type="string", example="+1234567890"),
 *                                @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
 *                                @OA\Property(property="about", type="string", example="some text"),
 *                                @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *                                @OA\Property(property="responsible_person", type="string", example="John Doe"),
 *                                @OA\Property(property="email", type="string", example="john@mail.com")),
 *                   @OA\Property(property="status", type="string", enum={"moderation", "rejected", "blocked", "published"}, example="published"),
 *                   @OA\Property(property="exclude_disability_types", type="array", example={"visual_impairment"}, @OA\Items(type="string", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"})),
 *                   @OA\Property(property="experience_level", type="string", example="small", enum={"без опыта", "1-3 года", "3-6 лет", "6 и более лет"}),
 *                   @OA\Property(property="skills", type="array", @OA\Items(type="string")),
 *                   @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *                   @OA\Property(property="salary_from_amount_formatted", type="string", example="10 165 СОМ"),
 *                   @OA\Property(property="salary_to_amount_formatted", type="string", example="92 090 СОМ"),
 *                   @OA\Property(property="salary_from_amount", type="integer", example=10165),
 *                   @OA\Property(property="salary_to_amount", type="integer", example=92090),
 *                   @OA\Property(property="salary_currency", type="string", example="KGS"),
 *                   @OA\Property(property="has_favorite", type="boolean", example=true),
 *                   @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
 *                   @OA\Property(property="address", type="string", example="Советская 23"),
 *                   @OA\Property(property="working_conditions", type="string", example="some text"),
 *                   @OA\Property(property="activity", type="string", example="уборщица"),
 *                   @OA\Property(property="created_at", type="date", example="1990-01-01"),
 *                   @OA\Property(property="updated_at", type="date", example="1990-01-01"),
 *                   @OA\Property(property="links", type="object",
 *                                 @OA\Property(property="first", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume?page=1"),
 *                                 @OA\Property(property="last", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume?page=10"),
 *                                 @OA\Property(property="prev", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume?page=1"),
 *                                 @OA\Property(property="next", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume?page=4"),
 *                                 ),
 *                    @OA\Property(property="meta", type="object",
 *                                 @OA\Property(property="current_page", type="integer", example="1"),
 *                                 @OA\Property(property="from", type="integer", example="1"),
 *                                 @OA\Property(property="last_page", type="integer", example="13"),
 *                                 @OA\Property(property="links", type="object",
 *                                              @OA\Property(property="url", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume?page=1"),
 *                                              @OA\Property(property="label", type="integer", example="2"),
 *                                              @OA\Property(property="active", type="boolean", example="false"),
 *                                              ),
 *                                 @OA\Property(property="path", type="string", example="http:\/\/127.0.0.1\/api\/v1\/resume"),
 *                                 @OA\Property(property="per_page", type="integer", example="10"),
 *                                 @OA\Property(property="to", type="integer", example="10"),
 *                                 @OA\Property(property="total", type="integer", example="100"),
 *                                 ),
 *                        ))
 *                       )
 *              )
 * )
 *
 */

 public function index(){}

 /**
 *
 * @OA\Get(
  *     path="/api/v1/vacancy/{vacancy}",
  *     summary = "Получение одной вакансии",
  *     tags={"Вакансии"},
  *     @OA\Parameter(
  *     description="ID вакансии",
  *     in="path",
  *     name="vacancy",
  *     required=true,
  *     example=1
  *     ),
  *    @OA\Response(
  *          response=200,
  *          description="OK",
  *          @OA\JsonContent(
  *             @OA\Property(property="data", type="array", @OA\Items(
  *                   @OA\Property(property="title", type="string", example="John Doe"),
  *                   @OA\Property(property="description", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
  *                   @OA\Property(property="requirements", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
  *                   @OA\Property(property="responsibilities", type="string", example="Eveniet repudiandae minima vel nam voluptatem doloribus. Quo dolorem laborum molestias dolorum adipisci sequi quos."),
  *                   @OA\Property(property="company", type="object",
  *                                 @OA\Property(property="name", type="string", example="Google"),
  *                                 @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true), nullable=true),
  *                                 @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
  *                                 @OA\Property(property="phone", type="string", example="+1234567890"),
  *                                 @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
  *                                 @OA\Property(property="about", type="string", example="some text"),
  *                                 @OA\Property(property="images", type="array", @OA\Items(type="string")),
  *                                 @OA\Property(property="responsible_person", type="string", example="John Doe"),
  *                                 @OA\Property(property="email", type="string", example="john@mail.com")),
  *                   @OA\Property(property="status", type="string", enum={"moderation", "rejected", "blocked", "published"}, example="published"),
  *                   @OA\Property(property="exclude_disability_types", type="array", example={"visual_impairment"}, @OA\Items(type="string", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"})),
  *                   @OA\Property(property="experience_level", type="string", example="small", enum={"без опыта", "1-3 года", "3-6 лет", "6 и более лет"}),
  *                   @OA\Property(property="skills", type="array", @OA\Items(type="string")),
  *                   @OA\Property(property="images", type="array", @OA\Items(type="string")),
  *                   @OA\Property(property="salary_from_amount_formatted", type="string", example="10 165 СОМ"),
  *                   @OA\Property(property="salary_to_amount_formatted", type="string", example="92 090 СОМ"),
  *                   @OA\Property(property="salary_from_amount", type="integer", example=10165),
  *                   @OA\Property(property="salary_to_amount", type="integer", example=92090),
  *                   @OA\Property(property="salary_currency", type="string", example="KGS"),
  *                   @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
  *                   @OA\Property(property="address", type="string", example="Советская 23"),
  *                   @OA\Property(property="working_conditions", type="string", example="some text"),
  *                   @OA\Property(property="activity", type="string", example="уборщица"),
  *                   @OA\Property(property="created_at", type="date", example="1990-01-01"),
  *                   @OA\Property(property="updated_at", type="date", example="1990-01-01"),
  *                        ))
  *               )
  *      )
  * )
  *
  */

    public function vacancy(){}

}
