<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Disease;
use App\Models\DiseaseAlias;
use App\Models\Recording;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private const SAMPLE_AUDIO = 'https://download.quranicaudio.com/quran/mishaari_raashid_al_3afaasee';

    public function run(): void
    {
        if (Category::count() > 0) {
            $this->command->info('Hospital categories already seeded. Skipping.');

            return;
        }

        $data = [
            [
                'name' => ['ar' => 'الأمراض الروحية', 'en' => 'Spiritual Afflictions'],
                'slug' => 'spiritual-afflictions',
                'icon' => 'heroicon-o-sparkles',
                'subcategories' => [
                    [
                        'name' => ['ar' => 'الجن والمس', 'en' => 'Jinn & Possession'],
                        'slug' => 'jinn-and-possession',
                        'diseases' => [
                            [
                                'name'        => ['ar' => 'السحر', 'en' => 'Magic (Sihr)'],
                                'slug'        => 'magic-sihr',
                                'description' => ['ar' => 'رقية شرعية لعلاج السحر', 'en' => 'Ruqyah for treating magic'],
                                'general_recording_sessions' => [1, 2, 3],
                                'aliases'     => [['ar' => 'سحر', 'en' => 'Black Magic'], ['ar' => 'شعوذة', 'en' => 'Sorcery']],
                            ],
                            [
                                'name'        => ['ar' => 'المس', 'en' => 'Touch (Mass)'],
                                'slug'        => 'touch-mass',
                                'description' => ['ar' => 'رقية شرعية لعلاج المس', 'en' => 'Ruqyah for treating possession'],
                                'general_recording_sessions' => [],
                                'aliases'     => [['ar' => 'مس شيطاني', 'en' => 'Demonic Touch']],
                            ],
                        ],
                    ],
                    [
                        'name' => ['ar' => 'العين والحسد', 'en' => 'Evil Eye & Envy'],
                        'slug' => 'evil-eye-and-envy',
                        'diseases' => [
                            [
                                'name'        => ['ar' => 'العين', 'en' => 'Evil Eye'],
                                'slug'        => 'evil-eye',
                                'description' => ['ar' => 'رقية شرعية لعلاج العين', 'en' => 'Ruqyah for treating the evil eye'],
                                'general_recording_sessions' => [1, 2, 3],
                                'aliases'     => [['ar' => 'إصابة بالعين', 'en' => 'Eye Strike']],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => ['ar' => 'الأمراض النفسية', 'en' => 'Psychological Complaints'],
                'slug' => 'psychological-complaints',
                'icon' => 'heroicon-o-heart',
                'subcategories' => [
                    [
                        'name' => ['ar' => 'القلق والاكتئاب', 'en' => 'Anxiety & Depression'],
                        'slug' => 'anxiety-and-depression',
                        'diseases' => [
                            [
                                'name'        => ['ar' => 'القلق', 'en' => 'Anxiety'],
                                'slug'        => 'anxiety',
                                'description' => ['ar' => 'رقية وأذكار لتهدئة القلق', 'en' => 'Ruqyah and adhkar to ease anxiety'],
                                'general_recording_sessions' => [],
                                'aliases'     => [['ar' => 'توتر', 'en' => 'Stress']],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($data as $catIndex => $catData) {
            $category = Category::create([
                'name'          => $catData['name'],
                'slug'          => $catData['slug'],
                'icon'          => $catData['icon'],
                'display_order' => $catIndex,
                'is_active'     => true,
            ]);

            foreach ($catData['subcategories'] as $subIndex => $subData) {
                $subcategory = Subcategory::create([
                    'category_id'   => $category->id,
                    'name'          => $subData['name'],
                    'slug'          => $subData['slug'],
                    'display_order' => $subIndex,
                    'is_active'     => true,
                ]);

                foreach ($subData['diseases'] as $disIndex => $disData) {
                    $disease = Disease::create([
                        'subcategory_id' => $subcategory->id,
                        'name'           => $disData['name'],
                        'slug'           => $disData['slug'],
                        'description'    => $disData['description'],
                        'display_order'  => $disIndex,
                        'is_active'      => true,
                    ]);

                    foreach ($disData['aliases'] as $alias) {
                        DiseaseAlias::create(['disease_id' => $disease->id, 'alias' => $alias]);
                    }

                    for ($session = 1; $session <= 3; $session++) {
                        Recording::create([
                            'disease_id'       => $disease->id,
                            'session_number'   => $session,
                            'title'            => [
                                'ar' => "الجلسة {$session}",
                                'en' => "Session {$session}",
                            ],
                            'audio_path'       => self::SAMPLE_AUDIO . '/' . str_pad((string) $session, 3, '0', STR_PAD_LEFT) . '.mp3',
                            'duration_seconds' => 300,
                            'is_general'       => in_array($session, $disData['general_recording_sessions']),
                            'plays_count'      => 0,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Hospital sample data seeded (categories, diseases, recordings).');
    }
}
