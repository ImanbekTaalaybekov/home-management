<?php
namespace App\OpenApi\Manage;

class BuildingCategoryController
{
    /**
     * @OA\Get(
     *     path="/api/v1/building-categories",
     *     tags={"Заведения"},
     *     summary="Получение списка категорий зданий",
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Поисковой запрос",
     *         required=false,
     *         @OA\Schema(type="string", minLength=2)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список категорий зданий",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="object", example="Гостиница"),
     *                     @OA\Property(property="icon", type="string", example="hotel")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(){}

}
