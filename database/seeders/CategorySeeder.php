<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $science = Category::updateOrCreate(
            ['slug' => 'science'],
            [
                'name' => 'Science',
                'description' => 'Kumpulan artikel ilmu pengetahuan: matematika, fisika, biologi, dan kimia.',
                'icon' => 'bi-mortarboard',
                'order' => 1,
                'is_active' => true,
            ]
        );

        $subs = [
            ['name' => 'Matematika', 'slug' => 'matematika', 'icon' => 'bi-calculator', 'description' => 'Logika, angka, dan struktur — dari aljabar dasar sampai soal-soal terbuka terbesar dalam matematika.'],
            ['name' => 'Fisika', 'slug' => 'fisika', 'icon' => 'bi-atom', 'description' => 'Hukum-hukum yang mengatur alam semesta, dari partikel subatom hingga skala kosmik.'],
            ['name' => 'Biologi', 'slug' => 'biologi', 'icon' => 'bi-heart-pulse', 'description' => 'Kehidupan dalam segala bentuknya: sel, genetika, evolusi, dan ekosistem.'],
            ['name' => 'Kimia', 'slug' => 'kimia', 'icon' => 'bi-droplet-half', 'description' => 'Materi, reaksi, dan ikatan yang membentuk dunia di sekitar kita.'],
        ];

        foreach ($subs as $i => $sub) {
            Category::updateOrCreate(
                ['slug' => $sub['slug']],
                [
                    'parent_id' => $science->id,
                    'name' => $sub['name'],
                    'description' => $sub['description'],
                    'icon' => $sub['icon'],
                    'order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
