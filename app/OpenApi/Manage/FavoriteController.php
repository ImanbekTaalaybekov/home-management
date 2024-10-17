<?php

namespace App\OpenApi\Manage;

use App\Http\Controllers\Controller;


class FavoriteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/favorite/{type}/{id}",
     *     tags={"Избранное"},
     *     summary="Добавить в избранное",
     *     description="Добавляет указанный элемент в избранное.",
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Тип элемента",
     *         required=true,
     *         @OA\Schema(type="string", enum={"resume", "building", "vacancy"})
     *     ),
     *           @OA\Parameter(
     *      name="X-Dev-Action-Reverse",
     *        in="header",
     *       description="Для тестирования (rollback)",
     *       required=false,
     *       @OA\Schema(
     *           type="string"
     *       ),
     *       example="true"
     *   ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID элемента",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно добавлено в избранное",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Added to favorites")
     *         )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Уже в Favorites",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Alredy added to favorite")
     *          )
     *      ),
     * )
     */
    public function addToFavorites(){}

    /**
     * @OA\Delete(
     *     path="/api/v1/favorite/{type}/{id}",
     *     tags={"Избранное"},
     *     summary="Удалить из избранного",
     *     description="Удаляет указанный элемент из избранного.",
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="Тип элемента",
     *         required=true,
     *         @OA\Schema(type="string", enum={"product", "article", "post"})
     *     ),
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
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID элемента",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешно удалено из избранного",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Removed from favorites")
     *         )
     *     ),
     *     *     @OA\Response(
     * *         response=401,
     * *         description="Нет в favorites",
     * *         @OA\JsonContent(
     * *             type="object",
     * *             @OA\Property(property="message", type="string", example="Not in Favorites")
     * *         )
     * *     ),
     * )
     */
    public function removeFromFavorites(){}
}
