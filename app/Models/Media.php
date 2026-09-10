<?php

namespace App\Models;

use App\Enums\MediaVisibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'organization_id', 'uploaded_by', 'disk', 'path', 'original_name',
    'mime_type', 'extension', 'size', 'visibility', 'checksum', 'metadata_json',
])]
class Media extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'visibility' => MediaVisibility::class,
            'metadata_json' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Public media can be linked to directly. Private media must always be
     * served through the authorized download endpoint instead of a raw URL.
     */
    public function publicUrl(): ?string
    {
        if ($this->visibility !== MediaVisibility::Public) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->path);
    }
}
