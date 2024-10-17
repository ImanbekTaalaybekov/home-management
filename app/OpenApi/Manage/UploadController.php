<?php

namespace App\OpenApi\Manage;

use App\Http\Controllers\Controller;


class UploadController extends Controller
{

    /**
     *
     * @OA\Post(
     *     path="/api/v1/upload/image",
     *     summary="Загрузка изображения",
     *     tags={"Загрузка файлов"},
     *
     *     @OA\RequestBody(
     *        description="File to upload",
     *        required=true,
     *         @OA\MediaType(
     *         mediaType="multipart/form-data",
     *               @OA\Schema(
     *                  type="object",
     *                  @OA\Property(property="image", description="image to upload", type="string", format="binary"),
     *               )
     *         )
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
     *                   @OA\Property(property="url", type="string", description="The URL of the uploaded image"),
     *                      ),
     *                ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *           type="object",
     *                   @OA\Property(property="error", type="object",
     *                      @OA\Property(property="image", type="array", @OA\Items(type="string", example="The image field is required."))
     *                      ),
     *                   )
     *                ),
     * )
     */

    public function image(){}

    /**
     *
     * @OA\Post(
     *     path="/api/v1/upload/document",
     *     summary="Загрузка документа",
     *     tags={"Загрузка файлов"},
     *
     *     @OA\RequestBody(
     *        description="File to upload",
     *        required=true,
     *         @OA\MediaType(
     *         mediaType="multipart/form-data",
     *               @OA\Schema(
     *                  type="object",
     *                  @OA\Property(property="document", description="document to upload", type="string", format="binary"),
     *               )
     *         )
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
     *                   @OA\Property(property="url", type="string", description="The URL of the uploaded document"),
     *                      ),
     *                ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *           type="object",
     *                   @OA\Property(property="error", type="object",
     *                      @OA\Property(property="document", type="array", @OA\Items(type="string", example="The document field is required."))
     *                      ),
     *                   )
     *                ),
     * )
     */

    public function document(){}
}
