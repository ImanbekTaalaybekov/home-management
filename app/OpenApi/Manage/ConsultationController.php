<?php

namespace App\OpenApi\Manage;

class ConsultationController
{
    /**
     *
     * @OA\Post(
     *     path="/api/v1/consultation",
     *     summary="Создание заявки",
     *     tags={"Консультации"},
     *
     *     @OA\RequestBody(
     *        description="Data for creating a new request for consultation",
     *        required=true,
     *         @OA\JsonContent(
     *           allOf={
     *               @OA\Schema(
     *                  @OA\Property(property="fullname", type="string", example="John Doe"),
     *                  @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}),
     *                  @OA\Property(property="disability_type", type="string", example="mental_health_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}),
     *                  @OA\Property(property="question", type="string", example="some text"),
     *                  @OA\Property(property="files", type="string", nullable=true),
     *               )
     *            }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *                  @OA\Property(property="fullname", type="string", example="John Doe"),
     *                  @OA\Property(property="disability_group", type="integer", example=2, enum={1, 2, 3}),
     *                  @OA\Property(property="disability_type", type="string", example="mental_health_disorder", enum={"visual_impairment", "hearing_impairment", "speech_disorder", "physical_disability", "intellectual_disability", "mental_health_disorder", "neurological_disorder", "chronic_illness", "multiple_disabilities", "autism_spectrum_disorder", "other"}),
     *                  @OA\Property(property="question", type="string", example="some text"),
     *                  @OA\Property(property="files", type="string", nullable=true),
     *              )
     *         ),
     *     ),
     * )
     */

    public function store(){}

}
