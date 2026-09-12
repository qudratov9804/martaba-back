<?php

namespace App\Actions\Courses;

use App\Models\CourseLesson;
use Illuminate\Support\Facades\DB;

class ReorderContentsAction
{
    /**
     * @param  list<int>  $contentIds
     */
    public function handle(CourseLesson $lesson, array $contentIds): void
    {
        DB::transaction(function () use ($lesson, $contentIds) {
            foreach ($contentIds as $index => $contentId) {
                $lesson->contents()->whereKey($contentId)->update(['sort_order' => $index]);
            }
        });
    }
}
