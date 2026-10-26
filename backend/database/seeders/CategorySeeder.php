<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Desenvolvimento Web',
                'description' => 'Websites, aplicações web e plataformas digitais.',
            ],
            [
                'name' => 'Aplicações Mobile',
                'description' => 'Aplicações Android, iOS e multiplataforma.',
            ],
            [
                'name' => 'Sistemas Empresariais',
                'description' => 'Sistemas de gestão e soluções empresariais.',
            ],
            [
                'name' => 'E-commerce',
                'description' => 'Lojas online e plataformas de comércio electrónico.',
            ],
            [
                'name' => 'Consultoria e Infraestrutura',
                'description' => 'Consultoria técnica, cloud, DevOps e infraestrutura.',
            ],
        ];

        foreach ($categories as $data) {
            $slug = Str::slug($data['name']);

            Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'is_active' => true,
                ],
            );
        }
    }
}
