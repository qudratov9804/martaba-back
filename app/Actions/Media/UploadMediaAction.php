<?php

namespace App\Actions\Media;

use App\Enums\MediaVisibility;
use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadMediaAction
{
    public function handle(User $uploader, UploadedFile $file, MediaVisibility $visibility = MediaVisibility::Private): Media
    {
        $disk = $visibility === MediaVisibility::Public ? 'public' : 'local';
        $path = $file->store('media/'.$uploader->organization_id, $disk);

        return Media::create([
            'organization_id' => $uploader->organization_id,
            'uploaded_by' => $uploader->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'visibility' => $visibility,
            'checksum' => hash_file('sha256', $file->getRealPath()),
        ]);
    }
}
