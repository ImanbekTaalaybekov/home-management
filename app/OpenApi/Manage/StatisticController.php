<?php
namespace App\OpenApi\Manage;

class StatisticController
{
    /**
     * @OA\Get(
     *     path="/api/v1/stat/{cityId}",
     *     tags={"Статистика"},
     *     summary="Получить статистику по городу",
     *     description="Возвращает количество зданий, резюме и вакансий для указанного города.",
     *     @OA\Parameter(
     *         name="cityId",
     *         in="path",
     *         description="Идентификатор города",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешное выполнение",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="building", type="integer", example="5"),
     *             @OA\Property(property="resume", type="integer", example="10"),
     *             @OA\Property(property="vacancy", type="integer", example="7")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Город не найден")
     * )
     */
    public function getStatisticByCity($cityId){}



}
