<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CanvasFormatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Sizes
        $sizes = [
            ['width' => 20, 'height' => 20, 'unit' => 'cm'],
            ['width' => 30, 'height' => 30, 'unit' => 'cm'],
            ['width' => 40, 'height' => 40, 'unit' => 'cm'],
            ['width' => 50, 'height' => 50, 'unit' => 'cm'],
            ['width' => 20, 'height' => 30, 'unit' => 'cm'],
            ['width' => 30, 'height' => 45, 'unit' => 'cm'],
            ['width' => 40, 'height' => 60, 'unit' => 'cm'],
            ['width' => 30, 'height' => 20, 'unit' => 'cm'],
            ['width' => 45, 'height' => 30, 'unit' => 'cm'],
            ['width' => 60, 'height' => 40, 'unit' => 'cm'],
            ['width' => 30, 'height' => 90, 'unit' => 'cm'],
            ['width' => 90, 'height' => 30, 'unit' => 'cm'],
        ];

        $sizeModels = [];
        foreach ($sizes as $size) {
            $sizeModels[] = \App\Models\CanvasSize::firstOrCreate(
                ['width' => $size['width'], 'height' => $size['height'], 'unit' => $size['unit']],
                ['is_active' => true]
            );
        }

        $formats = [
            ['name' => 'Kare', 'slug' => 'kare', 'size_indices' => [0, 1, 2, 3]],
            ['name' => 'Yatay', 'slug' => 'yatay', 'size_indices' => [7, 8, 9]],
            ['name' => 'Dikey', 'slug' => 'dikey', 'size_indices' => [4, 5, 6]],
            ['name' => 'Yatay 2/1', 'slug' => 'yatay-2-1', 'size_indices' => []],
            ['name' => 'Panorama 3/1', 'slug' => 'panorama-3-1', 'size_indices' => [11]],
            ['name' => 'Dikey 1/2', 'slug' => 'dikey-1-2', 'size_indices' => []],
            ['name' => 'Dikey 1/3', 'slug' => 'dikey-1-3', 'size_indices' => [10]],
            ['name' => '5 parçalı simetrik', 'slug' => '5-parcali-simetrik', 'size_indices' => []],
            ['name' => '3 parçalı simetrik', 'slug' => '3-parcali-simetrik', 'size_indices' => []],
            ['name' => '4 parçalı kare', 'slug' => '4-parcali-kare', 'size_indices' => []],
            ['name' => '3 parçalı yatay', 'slug' => '3-parcali-yatay', 'size_indices' => []],
            ['name' => '2 parçalı yatay', 'slug' => '2-parcali-yatay', 'size_indices' => []],
            ['name' => '3 parçalı panorama', 'slug' => '3-parcali-panorama', 'size_indices' => []],
            ['name' => '2 parçalı dikey', 'slug' => '2-parcali-dikey', 'size_indices' => []],
        ];

        foreach ($formats as $index => $formatData) {
            $format = \App\Models\CanvasFormat::updateOrCreate(
                ['slug' => $formatData['slug']],
                [
                    'name' => $formatData['name'],
                    'sort_order' => $index,
                ]
            );

            $syncData = [];
            foreach ($formatData['size_indices'] as $sIndex) {
                if (isset($sizeModels[$sIndex])) {
                    $syncData[$sizeModels[$sIndex]->id] = ['sort_order' => $sIndex];
                }
            }
            $format->sizes()->sync($syncData);
        }
    }
}
