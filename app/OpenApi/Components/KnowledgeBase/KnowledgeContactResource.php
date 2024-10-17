<?php

namespace App\OpenApi\Components\KnowledgeBase;

/**
 * @OA\Schema(
 *     title="contact",
 *     description="Ответ выдываемый для contact",
 * )
 */
class KnowledgeContactResource {

    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID",
     *     description="The unique identifier for the contact",
     * )
     *
     * @var int
     */
    private int $id;

    /**
     * @OA\Property(
     *     title="Phone",
     *     description="The phone number of the contact",
     * )
     *
     * @var string
     */
    private string $phone;

    /**
     * @OA\Property(
     *     title="Email",
     *     description="The email address of the contact",
     * )
     *
     * @var string
     */
    private string $email;

    /**
     * @OA\Property(
     *     title="Address",
     *     description="The address of the contact",
     * )
     *
     * @var string
     */
    private string $address;

    /**
     * @OA\Property(
     *     title="Additional Contacts",
     *     description="Additional contact information",
     *     type="array",
     *     @OA\Items(type="string"),
     * )
     *
     * @var array<string>
     */
    private array $additional_contacts;

    /**
     * @OA\Property(
     *     title="Created At",
     *     description="The date and time when the contact was created",
     *     example="2023-11-11T12:36:55.305087Z",
     * )
     *
     * @var string
     */
    private string $created_at;
}
