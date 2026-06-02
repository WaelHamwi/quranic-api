<?php

namespace Database\Seeders;

use App\Models\TahsinatCategory;
use App\Models\TahsinatItem;
use Illuminate\Database\Seeder;

class TahsinatSeeder extends Seeder
{
    public function run(): void
    {
        if (TahsinatCategory::count() > 0) {
            $this->command->info('Tahsinat already seeded. Skipping.');

            return;
        }

        $categories = [
            [
                'name'          => ['ar' => 'تحصين النفس', 'en' => 'Self Protection'],
                'slug'          => 'self-protection',
                'is_self'       => true,
                'is_for_others' => false,
            ],
            [
                'name'          => ['ar' => 'تحصين الآخرين', 'en' => 'Protecting Others'],
                'slug'          => 'protecting-others',
                'is_self'       => false,
                'is_for_others' => true,
            ],
        ];

        foreach ($categories as $order => $cat) {
            $category = TahsinatCategory::create([
                'name'          => $cat['name'],
                'slug'          => $cat['slug'],
                'is_self'       => $cat['is_self'],
                'is_for_others' => $cat['is_for_others'],
                'random_order'  => false,
                'display_order' => $order,
                'is_active'     => true,
            ]);

            for ($i = 1; $i <= 3; $i++) {
                TahsinatItem::create([
                    'tahsinat_category_id' => $category->id,
                    'label'                => ['ar' => "آية رقم {$i}", 'en' => "Verse {$i}"],
                    'text'                 => [
                        'ar' => "نص الآية القرآنية رقم {$i} للتحصين",
                        'en' => "Sample protective Quranic verse {$i}",
                    ],
                    'repetitions'          => 3,
                    'hint'                 => ['ar' => 'تقرأ بتدبر', 'en' => 'Recite with reflection'],
                    'display_order'        => $i,
                ]);
            }
        }

        $this->command->info('Tahsinat sample data seeded (2 categories).');
    }
}
