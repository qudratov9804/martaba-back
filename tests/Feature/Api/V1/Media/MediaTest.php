<?php

use App\Models\Media;
use App\Models\Organization;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('public');
    Storage::fake('local');
});

test('a teacher can upload a public media file', function () {
    $organization = Organization::factory()->create();
    $teacher = actingAsTeacher($organization);

    $file = UploadedFile::fake()->image('thumbnail.jpg', 800, 600);

    $response = $this->withHeaders(bearerHeaderFor($teacher))
        ->postJson('/api/v1/media', ['file' => $file, 'visibility' => 'public']);

    $response->assertCreated()->assertJsonPath('data.original_name', 'thumbnail.jpg');
    $this->assertDatabaseHas('media', ['uploaded_by' => $teacher->id, 'visibility' => 'public']);

    $media = Media::first();
    Storage::disk('public')->assertExists($media->path);
});

test('a student cannot upload media', function () {
    $organization = Organization::factory()->create();
    $student = actingAsStudent($organization);

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->withHeaders(bearerHeaderFor($student))->postJson('/api/v1/media', ['file' => $file]);

    $response->assertStatus(403);
});

test('a teacher cannot delete another teachers media', function () {
    $organization = Organization::factory()->create();
    $teacherA = actingAsTeacher($organization);
    $teacherB = actingAsTeacher($organization);

    $file = UploadedFile::fake()->image('a.jpg');
    $this->withHeaders(bearerHeaderFor($teacherA))->postJson('/api/v1/media', ['file' => $file]);
    $media = Media::first();

    $response = $this->withHeaders(bearerHeaderFor($teacherB))->deleteJson("/api/v1/media/{$media->id}");

    $response->assertStatus(403);
});

test('uploading an invalid file type is rejected', function () {
    $organization = Organization::factory()->create();
    $teacher = actingAsTeacher($organization);

    $file = UploadedFile::fake()->create('script.exe', 10);

    $response = $this->withHeaders(bearerHeaderFor($teacher))->postJson('/api/v1/media', ['file' => $file]);

    $response->assertStatus(422)->assertJsonValidationErrors(['file']);
});
