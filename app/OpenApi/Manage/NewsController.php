<?php
namespace App\OpenApi\Manage;

class NewsController
{
    /**
     * @OA\Get(
     *     path="/api/v1/news",
     *     tags={"Новости"},
     *     summary="Получить список новостей",
     *     description="Возвращает список новостей с пагинацией и возможностью поиска.",
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Количество элементов на странице (20 default)",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Номер страницы",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
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
     *         description="Успешное выполнение",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="published_at", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="small", type="string"),
     *                         @OA\Property(property="preview", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="is_published", type="boolean")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=400, description="Неверный запрос"),
     *     @OA\Response(response=404, description="Ресурс не найден")
     * )
     */
    public function index(){}

    /**
     * @OA\Get(
     *     path="/api/v1/news/{id}",
     *     tags={"Новости"},
     *     summary="Получить новость по ID",
     *     description="Возвращает отдельную новость по ее идентификатору.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Идентификатор новости",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное выполнение",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="published_at", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="small", type="string"),
     *                         @OA\Property(property="preview", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="is_published", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Ресурс не найден")
     * )
     */
    public function show(){}
}
