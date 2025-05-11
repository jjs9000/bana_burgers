<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\Variation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Main Menu Categories
        $mainMenu = Category::create(['name' => 'Main Menu']);

        // Create Products
        $burgerDaging = Product::create([
            'category_id' => $mainMenu->id,
            'name' => 'Burger Daging',
            'price' => 9.00,
            'description' => 'Delicious beef burger',
            'has_variations' => true
        ]);

        $burgerAyam = Product::create([
            'category_id' => $mainMenu->id,
            'name' => 'Burger Ayam',
            'price' => 8.00,
            'description' => 'Tasty chicken burger',
            'has_variations' => true
        ]);

        $benjo = Product::create([
            'category_id' => $mainMenu->id,
            'name' => 'Benjo',
            'price' => 6.00,
            'description' => 'Special Benjo',
            'has_variations' => false
        ]);

        // Create Variations (not applicable to Benjo)
        $variations = [
            ['name' => 'Biasa', 'additional_price' => 0],
            ['name' => 'Special', 'additional_price' => 2.00],
            ['name' => 'Special Cheese', 'additional_price' => 3.00],
            ['name' => 'Double Patty', 'additional_price' => 4.00],
            ['name' => 'Cheese', 'additional_price' => 1.00],
        ];

        foreach ($variations as $variation) {
            Variation::create([
                'product_id' => $burgerDaging->id,
                'name' => $variation['name'],
                'additional_price' => $variation['additional_price']
            ]);

            Variation::create([
                'product_id' => $burgerAyam->id,
                'name' => $variation['name'],
                'additional_price' => $variation['additional_price']
            ]);
        }

        // Create Options
        $options = [
            ['name' => 'Onion', 'type' => 'extra', 'additional_price' => 0.50],
            ['name' => 'Lettuce', 'type' => 'extra', 'additional_price' => 0.50],
            ['name' => 'Onion', 'type' => 'less', 'additional_price' => 0],
            ['name' => 'Lettuce', 'type' => 'less', 'additional_price' => 0],
            ['name' => 'Onion', 'type' => 'no', 'additional_price' => 0],
            ['name' => 'Lettuce', 'type' => 'no', 'additional_price' => 0],
        ];

        foreach ($options as $option) {
            Option::create($option);
        }
    }
}
