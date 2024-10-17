<?php
namespace App\OpenApi\Manage;

class PasswordResetController
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/password-reset",
     *     tags={"Пользователь"},
     *     summary="Отправка ключа для сброса пароля на почту",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ключ успешно отправлен",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации - неверная почта. *validation.required - ошибка пустого поля",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="validation.exists"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="validation.exists")),
     *             ),
     *         )
     *     )
     * )
     */

    public function sendResetLink(){}

    /**
     * @OA\Put(
     *     path="/api/v1/user/password",
     *     tags={"Пользователь"},
     *     summary="Изменение пароля пользователя после сброса",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="xxx"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="device", type="string", example="linux"),
     *         )
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
     *     @OA\Response(
     *         response=200,
     *         description="Успешное изменение пароля",
     *         @OA\JsonContent(
     *             @OA\Property(property="auth_token", type="string"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="1"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="language", type="enum", example="ru"),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверный токен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid token"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="validation.required"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="validation.required")),
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="validation.required")),
     *             ),
     *         )
     *     )
     * )
     */

    public function resetPassword(){}

    /**
     * @OA\Post(
     *     path="/api/v1/user/password-verify",
     *     tags={"Пользователь"},
     *     summary="Верификация ключа для сброса пароля",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="xxx"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Верификация успешна",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="validation.required"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", @OA\Items(type="string", example="Invalid token validation")),
     *                 @OA\Property(property="token", type="array", @OA\Items(type="string", example="Invalid token validation")),
     *             ),
     *         )
     *     )
     * )
     */

    public function verifyToken(){}
}
