<?php

namespace App\Actions\Categories;

use App\Models\Category;

class CreateCategoryAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(int $organizationId, array $data): Category
    {
        return Category::create([
            ...$data,
            'organization_id' => $organizationId,
        ]);
    }
}
