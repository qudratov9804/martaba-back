<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'name', 'layout', 'background_path', 'logo_path',
    'signature_path', 'settings_json', 'is_default',
])]
class CertificateTemplate extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'settings_json' => 'array',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
