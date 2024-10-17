<?php

namespace App\OpenApi\Manage;

class EnumController
{

    /**
     *
     * @OA\Get(
     *     path="/api/v1/schema/enums",
     *     summary = "Получение списка всех enum значений",
     *     tags={"Enum"},
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                    @OA\Property(property="common", type="object",
     *                                @OA\Property(property="name", type="object",
     *                                            @OA\Property(property="about", type="string", example="about"),
     *                                            @OA\Property(property="key", type="string", example="employment_type"),
     *                                            @OA\Property(property="values", type="object",
     *                                                         @OA\Property(property="title", type="object",
     *                                                                      @OA\Property(property="ru", type="string", example="Частичная занятость"),
     *                                                                      @OA\Property(property="kg", type="string", example="Частичная занятость"),
     *                                                                     ),
     *                                                        ),
     *                                            ),
     *                                ),
     *                    @OA\Property(property="associated", type="object",
     *                                @OA\Property(property="model", type="object",
     *                                             @OA\Property(property="name", type="object",
     *                                             @OA\Property(property="about", type="string", example="Виды занятости"),
     *                                             @OA\Property(property="key", type="string", example="employment_type"),
     *                                             @OA\Property(property="values", type="object",
     *                                                         @OA\Property(property="title", type="object",
     *                                                                      @OA\Property(property="ru", type="string", example="Частичная занятость"),
     *                                                                      @OA\Property(property="kg", type="string", example="Частичная занятость"),
     *                                                                     ),
     *                                                        ),
     *                                            ),
     *                                            ),
     *                                ),
     *             ))
     *          )
     *      )
     * ),
     *
     */

    public function index(){}
}
