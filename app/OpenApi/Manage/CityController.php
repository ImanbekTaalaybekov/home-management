<?php
namespace App\OpenApi\Manage;

class CityController
{
    /**
     * @OA\Get(
     *     path="/api/v1/cities",
     *     tags={"Города"},
     *     summary="Получение списка городов с сортировкой по приоритетам",
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
     *         description="Список городов",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example="1"),
     *                     @OA\Property(property="title", type="string", example="Нарын"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(){}

}
