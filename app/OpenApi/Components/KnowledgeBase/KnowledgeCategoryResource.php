<?php

namespace App\OpenApi\Components\KnowledgeBase;

/**
 * @OA\Schema(
 *     title="Knowledge Category Resource",
 *     description="Resource representing a knowledge category",
 * )
 */
class KnowledgeCategoryResource {

    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID",
     *     description="The unique identifier for the category",
     * )
     *
     * @var int
     */
    private int $id;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="The name of the category",
     * )
     *
     * @var string
     */
    private string $name;

    /**
     * @OA\Property(
     *     title="Description",
     *     description="The description of the category",
     * )
     *
     * @var string
     */
    private string $description;

    /**
     * @OA\Property(
     *     title="Icon",
     *     description="The icon representing the category",
     * )
     *
     * @var string
     */
    private string $icon;

    /**
     * @OA\Property(
     *     title="Children",
     *     description="Children categories of the current category",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/KnowledgeCategoryChildrenResource"),
     * )
     *
     * @var array<\App\OpenApi\Components\KnowledgeCategoryResource>
     */
    private array $children;
}
