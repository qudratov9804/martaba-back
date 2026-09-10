<?php

namespace App\Actions\Organizations;

use App\Models\Organization;

class UpdateOrganizationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Organization $organization, array $data): Organization
    {
        $organization->update($data);

        return $organization;
    }
}
