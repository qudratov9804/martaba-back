<?php

namespace App\Actions\Courses;

use App\Models\CourseSection;
use Illuminate\Support\Facades\DB;

class ReorderLessonsAction
{
    /**
     * @param  list<int>  $lessonIds
     */
    public function handle(CourseSection $section, array $lessonIds): void
    {
        DB::transaction(function () use ($section, $lessonIds) {
            foreach ($lessonIds as $index => $lessonId) {
                $section->lessons()->whereKey($lessonId)->update(['sort_order' => $index]);
            }
        });
    }
}
