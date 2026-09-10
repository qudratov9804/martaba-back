<?php

namespace App\Actions\Organizations;

use App\Models\Organization;

class CreateOrganizationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Organization
    {
        return Organization::create($data);
    }
}
