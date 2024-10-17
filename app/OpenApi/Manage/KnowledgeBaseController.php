<?php
namespace App\OpenApi\Manage;

class KnowledgeBaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/knowledge-base",
     *     tags={"База знаний"},
     *     summary="Получение списка категорий базы знаний",
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Язык контента (kg или ru)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Поиск по базе знаний, включая и документы, статьи и контакты.",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Список категорий базы знаний",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/KnowledgeCategoryResource"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Категории не найдены",
     *     ),
     * )
     */
    public function knowledgeCategory(){}

    /**
     * @OA\Get(
     *     path="/api/v1/knowledge-base/{id}/object",
     *     tags={"База знаний"},
     *     summary="Получение объекта базы знаний по идентификатору категории",
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Язык контента (kg или ru)",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Идентификатор категории (id подкатегории, у которого children NULL)",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Объект базы знаний",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 oneOf={
     *                     @OA\Schema(
     *                         @OA\Property(property="object_type", type="string", example="contact"),
     *                         @OA\Property(
     *                             property="object",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Тестовый контак 1"),
     *                             @OA\Property(property="phone", type="string", example="+1-609-873-6388"),
     *                             @OA\Property(property="email", type="string", example="dsawayn@beier.com"),
     *                             @OA\Property(property="address", type="string", example="6240 Ryleigh Spur\nNorth Damon, KS 92793"),
     *                             @OA\Property(property="additional_contacts", type="null"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-01T09:51:59.000000Z")
     *                         )
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="object_type", type="string", example="article"),
     *                         @OA\Property(
     *                             property="object",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Кыргызча 1"),
     *                             @OA\Property(property="description", type="string", example="Описаниесы 1"),
     *                             @OA\Property(property="image_paths", type="null"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-01T09:51:59.000000Z"),
     *                             @OA\Property(
     *                                 property="translations",
     *                                 type="object",
     *                                 @OA\Property(
     *                                     property="audio_files",
     *                                     type="object",
     *                                     @OA\Property(property="kg", type="string", example="http://localhost/storage/49/image_kg.jpg"),
     *                                     @OA\Property(property="ru", type="string", example="http://localhost/storage/50/image_ru.jpg")
     *                                 ),
     *                                 @OA\Property(property="pdf_files", type="null")
     *                             )
     *                         )
     *                     ),
     *                     @OA\Schema(
     *                         @OA\Property(property="object_type", type="string", example="document"),
     *                         @OA\Property(
     *                             property="object",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="title", type="string", example="Документи 2"),
     *                             @OA\Property(
     *                                 property="translations",
     *                                 type="object",
     *                                 @OA\Property(
     *                                     property="files",
     *                                     type="object",
     *                                     @OA\Property(property="kg", type="string", example="http://localhost/storage/47/doc_kg.jpg"),
     *                                     @OA\Property(property="ru", type="string", example="http://localhost/storage/48/doc_ru.jpg")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Объект или категория не найдены",
     *     ),
     * )
     */
    public function knowledgeObject(){}


}
