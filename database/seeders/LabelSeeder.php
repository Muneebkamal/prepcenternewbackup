<?php

namespace Database\Seeders;

use App\Models\SystemOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert default labels into the 'labels' table
        SystemOption::create([
            'label' => 'Poly Bag Size',
            'type' => 'poly_bag_size', // Label type
        ]);

        SystemOption::create([
            'label' => 'Carton Size',
            'type' => 'carton_size', // Label type
        ]);
        SystemOption::create([
            'label' => 'Label 1',
            'type' => 'label_1', // Label type
        ]);
        SystemOption::create([
            'label' => 'Label 2',
            'type' => 'label_2', // Label type
        ]);
        SystemOption::create([
            'label' => 'Label 3',
            'type' => 'label_3', // Label type
        ]);
        SystemOption::create([
            'label' => 'Shrink Wrap Size',
            'type' => 'shrink_wrap_size', // Label type
        ]);
    }
}
