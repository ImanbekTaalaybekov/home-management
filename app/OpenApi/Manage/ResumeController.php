<?php

namespace App\OpenApi\Manage;

class ResumeController
{

    /**
     *
     * @OA\Get(
     *     path="/api/v1/resume",
     *     summary = "Получение списка резюме по дате обновления",
     *     tags={"Резюме"},
     *   @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Лимит на странице (default 20)",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Номер страницы",
     *     required=false,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="search",
     *     in="query",
     *     description="Поисковой запрос",
     *     required=false,
     *     @OA\Schema(type="string", minLength=2)
     *   ),
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
     *             property="desired_salary_amount",
     *             type="number",
     *             format="float",
     *             description="Желаемая заработная плата"
     *          ),
     *        @OA\Property(
     *             property="desired_salary_currency",
     *             type="string",
     *             description="Валюта"
     *          ),
     *        @OA\Property(
     *             property="employment_types",
     *             type="array",
     *             description="Виды занятости",
     *             @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})
     *          ),
     *        @OA\Property(
     *             property="date_of_birth",
     *             type="integer",
     *             description="Год рождения"
     *          ),
     *        @OA\Property(
     *             property="disability_group",
     *             type="integer",
     *             enum={1, 2, 3},
     *             description="Группа инвалидности",
     *         ),
     *        @OA\Property(
     *             property="disability_type",
     *             type="string",
     *             enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"},
     *             description="Вид инвалидности",
     *         ),
     *       ),
     *     style="deepObject",
     *     explode=true
     *     ),
     *    @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                    @OA\Property(property="id", type="integer", example=1),
     *                    @OA\Property(property="fullname", type="string", example="John Doe"),
     *                    @OA\Property(property="phone", type="string", example="+1234567890"),
     *                    @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="upstream"))),
     *                    @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
     *                    @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true)),
     *                    @OA\Property(property="employment_types", type="array", example={"remote"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                    @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                    @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                    @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                    @OA\Property(property="company_name", type="string", example="Toyota"), @OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                    @OA\Property(property="disability_group", type="integer", enum={1, 2, 3}, nullable=true),
     *                    @OA\Property(property="disability_type", type="string", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                    @OA\Property(property="additional_info", type="string", example="some text"),
     *                    @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                    @OA\Property(property="has_favorite", type="boolean", example=true),
     *                    @OA\Property(property="avatar", type="string", nullable=true),
     *                    @OA\Property(property="desired_salary", type="decimal", example=38741),
     *                    @OA\Property(property="desired_salary_formatted", type="string", example="38 741 EUR"),
     *                    @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                    @OA\Property(property="created_at", type="date", example="1990-01-01"),
     *                    @OA\Property(property="updated_at", type="date", example="1990-01-01"),
     *                    @OA\Property(property="links", type="object",
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
     *             ))
     *          )
     *      )
     * ),
     *
     */

    public function index(){}

    /**
     *
     * @OA\Get(
     *     path="/api/v1/resume/{resume}",
     *     summary = "Получение одного резюме",
     *     tags={"Резюме"},
     *     @OA\Parameter(
     *     description="ID резюме",
     *     in="path",
     *     name="resume",
     *     required=true,
     *     example=1
     *     ),
     *    @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="fullname", type="string", example="John Doe"),
     *                     @OA\Property(property="phone", type="string", example="+1234567890"),
     *                     @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
     *                     @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
     *                     @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true)),
     *                     @OA\Property(property="employment_types", type="array", example={"remote"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                     @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                     @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                     @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                     @OA\Property(property="company_name", type="string", example="Toyota"), @OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                     @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}, nullable=true),
     *                     @OA\Property(property="disability_type", type="string", example="speech_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                     @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                     @OA\Property(property="avatar", type="string", nullable=true),
     *                     @OA\Property(property="has_favorite", type="boolean", example=true),
     *                     @OA\Property(property="desired_salary", type="decimal", example=38741),
     *                     @OA\Property(property="desired_salary_formatted", type="string", example="38 741 EUR"),
     *                     @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                     @OA\Property(property="created_at", type="date", example="1990-01-01"),
     *                     @OA\Property(property="updated_at", type="date", example="1990-01-01"),
     *             ))
     *          )
     *      )
     * )
     *
     */

    public function resume(){}

    /**
     *
     * @OA\Post(
     *     path="/api/v1/resume",
     *     summary="Создание резюме",
     *     tags={"Резюме"},
     *
     *     @OA\RequestBody(
     *        description="Data for creating a new resume",
     *        required=true,
     *         @OA\JsonContent(
     *           allOf={
     *               @OA\Schema(
     *                   @OA\Property(property="fullname", type="string", example="John Doe"),
     *                   @OA\Property(property="phone", type="string", example="+1234567890"),
     *                   @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
     *                   @OA\Property(property="city_id", type="integer", example=2),
     *                   @OA\Property(property="employment_types", type="array", example={"contract"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                   @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                   @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                   @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                   @OA\Property(property="company_name", type="string", example="Toyota"), @OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                   @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}, nullable=true),
     *                   @OA\Property(property="disability_type", type="string", example="mental_health_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                   @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                   @OA\Property(property="avatar", type="string", nullable=true),
     *                   @OA\Property(property="desired_salary_amount", type="integer", example=38741),*
     *                   @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                   @OA\Property(property="status", type="string", nullable=true, example=null),
     *               )
     *            }
     *         )
     *     ),
     *       @OA\Parameter(
     *       name="X-Dev-Action-Reverse",
     *       in="header",
     *       description="Для тестирования (rollback)",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       ),
     *       example="true"
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *                   @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="Fullname", type="string", example="John Doe"),
     *                   @OA\Property(property="phone", type="string", example="+1234567890"),
     *                   @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
     *                   @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
     *                   @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true)),
     *                   @OA\Property(property="employment_types", type="array", example={"remote"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                   @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                   @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                   @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                   @OA\Property(property="company_name", type="string", example="company"),@OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                   @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}, nullable=true),
     *                   @OA\Property(property="disability_type", type="string", example="mental_health_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                   @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                   @OA\Property(property="avatar", type="string", ),
     *                   @OA\Property(property="has_favorite", type="boolean", example=true),
     *                   @OA\Property(property="desired_salary", type="decimal", example=38741),
     *                   @OA\Property(property="desired_salary_formatted", type="string", example="38 741 EUR"),
     *                   @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                   @OA\Property(property="created_at", type="date", example="1990-01-01"),
     *                   @OA\Property(property="updated_at", type="date", example="1990-01-01"),
     *                   @OA\Property(property="status", type="string", example="rejected"),
     *                         )
     *                      ),
     *                ),
     * )
     */


    public function store(){}


    /**
     *
     * @OA\Put(
     *     path="/api/v1/resume",
     *     summary="Обновление резюме пользователя",
     *     tags={"Резюме"},
     *
     *     @OA\RequestBody(
     *        description="User resume update data",
     *        required=true,
     *         @OA\JsonContent(
     *           allOf={
     *               @OA\Schema(
     *                  @OA\Property(property="fullname", type="string", example="John Doe"),
     *                  @OA\Property(property="phone", type="string", example="+1234567890"),
     *                  @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
     *                  @OA\Property(property="city_id", type="integer", example=2),
     *                  @OA\Property(property="employment_types", type="array", example={"contract"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                  @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                  @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                  @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                  @OA\Property(property="company_name", type="string", example="Toyota"), @OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                  @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}, nullable=true),
     *                  @OA\Property(property="disability_type", type="string", example="mental_health_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                  @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                  @OA\Property(property="avatar", type="string", nullable=true),
     *                  @OA\Property(property="desired_salary_amount", type="integer", example=38741),*
     *                  @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                  @OA\Property(property="status", type="string", nullable=true, example=null),
     *                        )
     *                 }
     *                       )
     *     ),
     *      @OA\Parameter(
     *      name="X-Dev-Action-Reverse",
     *        in="header",
     *       description="Для тестирования (rollback)",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       ),
     *       example="true"
     *   ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *                   @OA\Property(
     *                       property="data",
     *                       type="object",
     *                            @OA\Property(property="id", type="integer", example=1),
     *                            @OA\Property(property="fullname", type="string", example="John Doe"),
     *                            @OA\Property(property="phone", type="string", example="+1234567890"),
     *                            @OA\Property(property="additional_contacts", type="array", @OA\Items(type="object", @OA\Property(property="label", type="string", example="Telegram"), @OA\Property(property="value", type="string", example="@upstream"))),
     *                            @OA\Property(property="city", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="title", type="string", example="Нарын")),
     *                            @OA\Property(property="user", type="object", @OA\Property(property="id", type="integer", example=2), @OA\Property(property="name", type="string", example="Gabriel Altenwerth"), @OA\Property(property="language", type="string", example="ru", nullable=true)),
     *                            @OA\Property(property="employment_types", type="array", example={"remote"}, @OA\Items(type="string", enum={"full-time", "part-time", "contract", "temporary", "seasonal", "remote", "internship", "project-based", "volunteer"})),
     *                            @OA\Property(property="educations", type="array", @OA\Items(type="object",
     *                            @OA\Property(property="institution_name", type="string", example="Princeton"), @OA\Property(property="specialty", type="string", example="Economics"), @OA\Property(property="end_year", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                            @OA\Property(property="experiences", type="array", @OA\Items(type="object",
     *                            @OA\Property(property="company_name", type="string", example="Toyota"), @OA\Property(property="role_name", type="string", example="photographer"), @OA\Property(property="start_date", type="string", format="date", example="1992-05-12"), @OA\Property(property="end_date", type="string", format="date", example="1992-05-12", nullable=true), @OA\Property(property="location", type="string", example="Tokyo", nullable=true), @OA\Property(property="additional_info", type="string", example="some text", nullable=true))),
     *                            @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}, nullable=true),
     *                            @OA\Property(property="disability_type", type="string", example="speech_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}, nullable=true),
     *                            @OA\Property(property="date_of_birth", type="date", example="1990-01-01", nullable=true),
     *                            @OA\Property(property="avatar", type="string", nullable=true),
     *                            @OA\Property(property="has_favorite", type="boolean", example=true),
     *                            @OA\Property(property="desired_salary", type="decimal", example=38741),
     *                            @OA\Property(property="desired_salary_formatted", type="string", example="38 741 EUR"),
     *                            @OA\Property(property="desired_salary_currency", type="string", example="EUR"),
     *                            @OA\Property(property="created_at", type="date", example="1990-01-01"),
     *                            @OA\Property(property="updated_at", type="date", example="1990-01-01"),
     *                            @OA\Property(property="status", type="string", example="rejected"),
     *                              )
     *                       ),
     *                 ),
     * )
     */

    public function update(){}

    /**
     *
     * @OA\Delete(
     *     path="/api/v1/resume",
     *     summary = "Удаление резюме пользователя",
     *     tags={"Резюме"},
     *    @OA\Parameter(
     *      name="X-Dev-Action-Reverse",
     *        in="header",
     *       description="Для тестирования (rollback)",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       ),
     *       example="true"
     *   ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="done")
     *          )
     *      )
     *
     * )
     *
     */

    public function delete(){}

    /**
     *
     * @OA\GET(
     *     path="/api/v1/resume/current-user",
     *     summary = "Просмотр резюме текущего пользователя",
     *     tags={"Резюме"},
     *    @OA\Parameter(
     *      name="X-Dev-Action-Reverse",
     *        in="header",
     *       description="Для тестирования (rollback)",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       ),
     *       example="true"
     *   ),
     * @OA\Response(
     *      response=200,
     *      description="OK",
     *      @OA\JsonContent(
     *          @OA\Property(property="data", type="object",
     *              @OA\Property(property="id", type="integer", example=13),
     *              @OA\Property(property="fullname", type="string", example="Magdalena Spinka"),
     *              @OA\Property(property="phone", type="string", example="+1-986-518-5875"),
     *              @OA\Property(property="additional_contacts", type="array", @OA\Items(
     *                  @OA\Property(property="label", type="string", example="Mrs. Glenna Leffler"),
     *                  @OA\Property(property="value", type="string", example="Mr. Dedric Bartoletti")
     *              )),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=11),
     *                  @OA\Property(property="email", type="string", nullable=true),
     *                  @OA\Property(property="is_verified", type="boolean", example=false),
     *                  @OA\Property(property="language", type="string", example="ru"),
     *                  @OA\Property(property="created_at", type="string", example="21 марта 2024"),
     *                  @OA\Property(property="city_id", type="integer", example=6),
     *                  @OA\Property(property="has_resume", type="boolean", example=true)
     *              ),
     *              @OA\Property(property="city", type="object",
     *                  @OA\Property(property="id", type="integer", example=3),
     *                  @OA\Property(property="title", type="string", example="Джалал-Абад")
     *              ),
     *              @OA\Property(property="desired_salary", type="integer", example=90135),
     *              @OA\Property(property="desired_salary_formatted", type="string", example="$90 135"),
     *              @OA\Property(property="desired_salary_currency", type="string", example="USD"),
     *              @OA\Property(property="employment_types", type="array", @OA\Items(type="string", example="contract")),
     *              @OA\Property(property="date_of_birth", type="string", example="1971-05-20"),
     *              @OA\Property(property="disability_group", type="integer", example=2),
     *              @OA\Property(property="disability_type", type="string", example="hearing_impaired"),
     *              @OA\Property(property="additional_info", type="string", example="Eos provident consequuntur suscipit tenetur illum. Vel occaecati quisquam praesentium eum doloribus. Quae nam quaerat consequuntur sit asperiores veniam."),
     *              @OA\Property(property="status", type="string", example="published"),
     *              @OA\Property(property="avatar", type="object",
     *                  @OA\Property(property="small", type="string", example="http://localhost/storage/59/conversions/3-small.webp"),
     *                  @OA\Property(property="preview", type="string", example="http://localhost/storage/59/conversions/3-preview.webp")
     *              ),
     *              @OA\Property(property="has_favorite", type="boolean", example=false),
     *              @OA\Property(property="created_at", type="string", example="21 марта 2024"),
     *              @OA\Property(property="updated_at", type="string", example="21 марта 2024")
     *          )
     *      )
     *  )
     *)
     *
     */

    public function currentUser(){}
}
