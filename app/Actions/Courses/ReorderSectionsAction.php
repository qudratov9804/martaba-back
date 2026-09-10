<?php

namespace App\Actions\Courses;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ReorderSectionsAction
{
    /**
     * @param  list<int>  $sectionIds
     */
    public function handle(Course $course, array $sectionIds): void
    {
        DB::transaction(function () use ($course, $sectionIds) {
            foreach ($sectionIds as $index => $sectionId) {
                $course->sections()->whereKey($sectionId)->update(['sort_order' => $index]);
            }
        });
    }
}
