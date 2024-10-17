<?php

namespace App\OpenApi\Components\KnowledgeBase;

/**
 * @OA\Schema(
 *     title="document",
 *     description="Ответ выдываемый для document",
 * )
 */
class KnowledgeDocumentResource
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID",
     *     description="The unique identifier for the document",
     * )
     */
    public int $id;

    /**
     * @OA\Property(
     *     title="Title",
     *     description="The title of the document",
     * )
     */
    public string $title;

    /**
     * @OA\Property(
     *     title="Translations",
     *     description="Translations of the document",
     *     type="object",
     *     @OA\Property(
     *         property="files",
     *         type="object",
     *         @OA\Property(
     *             property="kg",
     *             type="string",
     *             description="URL of the document file in Kyrgyz",
     *             example="http://localhost/storage/16/doc_kg.jpg",
     *         ),
     *         @OA\Property(
     *             property="ru",
     *             type="string",
     *             description="URL of the document file in Russian",
     *             example="http://localhost/storage/17/doc_ru.jpg",
     *         ),
     *     ),
     * )
     */
    public array $translations = [];
}

