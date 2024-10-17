<?php
namespace App\OpenApi\Manage;

class BuildingController
{
    /**
     * @OA\Get(
     *     path="/api/v1/buildings",
     *     tags={"Заведения"},
     *     summary="Получение списка зданий с фильтрацией",
     *     @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Лимит на странице (default 20)",
     *     required=false,
     *     @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Номер страницы",
     *     required=false,
     *     @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *     name="search",
     *     in="query",
     *     description="Поисковой запрос",
     *     required=false,
     *     @OA\Schema(type="string", minLength=2)
     *     ),
     *     @OA\Parameter(
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
     *       ),
     *     style="deepObject",
     *     explode=true
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Язык предпочтения клиента (kg или ru)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"kg", "ru"}
     *         ),
     *         example="ru"
     *     ),
     *      @OA\Parameter(
     *      name="X-Dev-Action-Reverse",
     *       in="header",
     *       description="Для тестирования (rollback)",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      ),
     *      example="любое значение"
     *  ),
     *     @OA\Response(
     *         response=200,
     *         description="Список зданий",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=19),
     *                     @OA\Property(property="title", type="string", example="Китеп Дүкөнү Окуу-Шаар"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="Билим, сергилиме, каралуу"),
     *                     @OA\Property(property="phone_numbers", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="label", type="string", example="Администратор"),
     *                             @OA\Property(property="value", type="string", example="1-515-985-9211"),
     *                         )
     *                     ),
     *                     @OA\Property(property="address", type="string", example="8627 Noemie Isle\nCassinfort, NM 13748"),
     *                     @OA\Property(property="has_favorite", type="boolean", example=true),
     *                     @OA\Property(property="working_hours", type="string", example="13.00 - 17.00 пн-пт"),
     *                     @OA\Property(property="city", type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="title", type="string", example="Нарын")
     *                     ),
     *                     @OA\Property(property="category", type="object",
     *                         @OA\Property(property="id", type="integer", example=6),
     *                         @OA\Property(property="title", type="string", example="Башкалар"),
     *                         @OA\Property(property="icon", type="string", example="others")
     *                     ),
     *                     @OA\Property(property="images", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="small", type="string", example="http://localhost/storage/19/conversions/image_test-small.webp"),
     *                             @OA\Property(property="preview", type="string", example="http://localhost/storage/19/conversions/image_test-preview.webp"),
     *                             @OA\Property(property="large", type="string", example="http://localhost/storage/19/conversions/image_test-large.webp")
     *                         ),
     *                         @OA\Items(
     *                             @OA\Property(property="small", type="string", example="http://localhost/storage/20/conversions/image_test-small.webp"),
     *                             @OA\Property(property="preview", type="string", example="http://localhost/storage/20/conversions/image_test-preview.webp"),
     *                             @OA\Property(property="large", type="string", example="http://localhost/storage/20/conversions/image_test-large.webp")
     *                         )
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-30T16:02:40.000000Z"),
     *                     @OA\Property(property="links", type="object",
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
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index(){}

    /**
     * @OA\Get(
     *     path="/api/v1/buildings/{id}",
     *     tags={"Заведения"},
     *     summary="Получение информации о здании по ID",
     *     description="Возвращает информацию о конкретном здании по его идентификатору.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Идентификатор здания",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Язык предпочтения клиента (kg или ru)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             enum={"kg", "ru"}
     *         ),
     *         example="ru"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Информация о здании",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=19),
     *             @OA\Property(property="title", type="string", example="Китеп Дүкөнү Окуу-Шаар"),
     *             @OA\Property(property="description", type="string", nullable=true, example="Билим, сергилиме, каралуу"),
     *             @OA\Property(property="phone_numbers", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="label", type="string", example="Администратор"),
     *                     @OA\Property(property="value", type="string", example="1-515-985-9211"),
     *                 )
     *             ),
     *             @OA\Property(property="address", type="string", example="8627 Noemie Isle\nCassinfort, NM 13748"),
     *             @OA\Property(property="has_favorite", type="boolean", example=true),
     *             @OA\Property(property="working_hours", type="string", example="13.00 - 17.00 пн-пт"),
     *             @OA\Property(property="city", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="title", type="string", example="Нарын")
     *             ),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=6),
     *                 @OA\Property(property="title", type="string", example="Башкалар"),
     *                 @OA\Property(property="icon", type="string", example="others")
     *             ),
     *             @OA\Property(property="images", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="small", type="string", example="http://localhost/storage/19/conversions/image_test-small.webp"),
     *                     @OA\Property(property="preview", type="string", example="http://localhost/storage/19/conversions/image_test-preview.webp"),
     *                     @OA\Property(property="large", type="string", example="http://localhost/storage/19/conversions/image_test-large.webp")
     *                 ),
     *                 @OA\Items(
     *                     @OA\Property(property="small", type="string", example="http://localhost/storage/20/conversions/image_test-small.webp"),
     *                     @OA\Property(property="preview", type="string", example="http://localhost/storage/20/conversions/image_test-preview.webp"),
     *                     @OA\Property(property="large", type="string", example="http://localhost/storage/20/conversions/image_test-large.webp")
     *                 )
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-30T16:02:40.000000Z")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Здание не найдено")
     * )
     */
    public function show(){}
}
