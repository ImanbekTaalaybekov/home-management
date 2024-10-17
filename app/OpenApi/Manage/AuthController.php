<?php
namespace App\OpenApi\Manage;

class AuthController
{
    /**
     * @OA\Post (
     *     path="/api/v1/auth",
     *     tags={"Пользователь"},
     *     summary="Авторизация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "device"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device", type="string", example="mobile"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешная авторизация",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="int", example="123"),
     *                 @OA\Property(property="email", type="string", example="..."),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date"),
     *             ),
     *             @OA\Property(property="auth_token", type="string", example="xxxxxx"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неверный email или пароль",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid email or password"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="validation.required"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="validation.required")),
     *             ),
     *         )
     *     )
     * )
     */

    public function auth(){}

    /**
     * @OA\Post (
     *     path="/api/v1/register",
     *     tags={"Пользователь"},
     *     summary="Регистрация пользователя",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "device", "city_id"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device", type="string", example="mobile"),
     *             @OA\Property(property="city_id", type="integer", example="1"),
     *             @OA\Property(property="language", type="string", example="ru"),
     *         )
     *     ),
     *      @OA\Parameter(
     *     name="X-Dev-Action-Reverse",
     *       in="header",
     *      description="Для тестирования (rollback)",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      ),
     *      example="true"
     *  ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Успешная регистрация",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="123"),
     *                 @OA\Property(property="email", type="string", example="..."),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date"),
     *                 @OA\Property(property="city_id", type="integer", example="1"),
     *                 @OA\Property(property="language", type="string", example="ru"),
     *             ),
     *
     *             @OA\Property(property="auth_token", type="string", example="xxxxxx"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="validation.required"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="password", type="array", @OA\Items(type="string", example="validation.required")),
     *             ),
     *         )
     *     )
     * )
     */

    public function register(){}

    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     tags={"Пользователь"},
     *     summary="Информация о текущем пользователе",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Данные текущего пользователя",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="string", example="123"),
     *                 @OA\Property(property="email", type="string", example="..."),
     *                 @OA\Property(property="is_verified", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Невалидный токен",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *         )
     *     )
     * )
     */

    public function me(){}

    /**
     * @OA\Put(
     *     path="/api/v1/user",
     *     tags={"Пользователь"},
     *     summary="Обновление информации о пользователе",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *          @OA\Property(property="language", type="string", example="kg", nullable=true),
     *          @OA\Property(property="password", type="string", example="newpassword123"),
     *          @OA\Property(property="city_id", type="integer", example="1", nullable=true),
     *          @OA\Property(
     *              property="notifications",
     *              type="object",
     *              @OA\Property(property="newsletter", type="boolean", example=true),
     *              @OA\Property(property="resume", type="boolean", example=false),
     *          ),
     *      ),
     *  ),
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
     * @OA\Response(
     *      response=200,
     *    description="Успешное обновление информации о пользователе",
     *      @OA\JsonContent(
     *          @OA\Property(property="notification", type="object",
     *              @OA\Property(property="newsletter", type="boolean", example=true),
     *              @OA\Property(property="resume", type="boolean", example=false),
     *          ),
     *          @OA\Property(property="user", type="object",
     *              @OA\Property(property="id", type="integer", example=3),
     *              @OA\Property(property="email", type="string", example="ashtyn.jones@example.org"),
     *              @OA\Property(property="is_verified", type="boolean", example=true),
     *              @OA\Property(property="language", type="string", example="kg"),
     *              @OA\Property(property="city_id", type="integer", example="1"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-02-26T11:36:36.000000Z"),
     *          ),
     *      ),
     *  ),
     *     @OA\Response(
     *         response=401,
     *         description="Неаутентифицированный запрос",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not authenticated"),
     *         )
     *     )
     * )
     */

    public function update(){}

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     tags={"Пользователь"},
     *     summary="Выход пользователя",
     *     description="Логаут пользователя. Удаление Токена авторизации",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный выход из системы",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */

    public function logout(){}

}
