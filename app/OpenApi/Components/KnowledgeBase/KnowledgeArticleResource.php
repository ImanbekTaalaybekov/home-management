<?php

namespace App\OpenApi\Components\KnowledgeBase;

/**
 * @OA\Schema(
 *     title="article",
 *     description="Ответ выдываемый для article",
 * )
 */
class KnowledgeArticleResource
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID",
     *     description="The unique identifier for the article",
     * )
     *
     * @var int
     */
    private int $id;

    /**
     * @OA\Property(
     *     title="Title",
     *     description="The title of the article",
     * )
     *
     * @var string
     */
    private string $title;

    /**
     * @OA\Property(
     *     title="Description",
     *     description="The description of the article",
     * )
     *
     * @var string
     */
    private string $description;

    /**
     * @OA\Property(
     *     title="Image Paths",
     *     description="The path of audio files",
     *     example=null
     * )
     *
     * @var string
     */
    private string $image_paths;

    /**
     * @OA\Property(
     *     title="Translations",
     *     description="Translations of files",
     *     type="object",
     *     @OA\Property(
     *         property="audio_files",
     *         type="object",
     *         description="URLs of the audio files associated with the article",
     *         @OA\Property(
     *             property="kg",
     *             type="string",
     *             description="URL of the audio file in Kyrgyz",
     *             example="http://localhost/storage/18/image_kg.jpg",
     *         ),
     *         @OA\Property(
     *             property="ru",
     *             type="string",
     *             description="URL of the audio file in Russian",
     *             example="http://localhost/storage/19/image_ru.jpg",
     *         ),
     *     ),
     *     @OA\Property(
     *         property="pdf_files",
     *         type="null",
     *         description="URLs of the PDF documents associated with the article",
     *         example=null,
     *     ),
     * )
     *
     * @var array<string>
     */
    private array $translations;


    /**
     * @OA\Property(
     *     title="Created At",
     *     description="The date and time when the article was created",
     *     example="2023-11-11T12:36:55.305087Z",
     * )
     *
     * @var string
     */
    private string $created_at;
}
