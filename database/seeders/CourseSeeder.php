<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        if (Course::count() > 0) {
            $this->command->info('Courses already seeded. Skipping.');

            return;
        }

        Course::create([
            'title'           => ['ar' => 'مقدمة في الرقية الشرعية', 'en' => 'Introduction to Ruqyah'],
            'description'     => [
                'ar' => 'دورة تمهيدية حول الرقية الشرعية وآدابها',
                'en' => 'An introductory course on Ruqyah and its etiquette',
            ],
            'instructor_name' => 'Sheikh Sample',
            'price'           => 0,
            'start_date'      => null,
            'whatsapp_link'   => 'https://wa.me/0000000000',
            'is_coming_soon'  => true,
            'is_active'       => true,
            'display_order'   => 0,
        ]);

        $this->command->info('Course sample data seeded.');
    }
}
