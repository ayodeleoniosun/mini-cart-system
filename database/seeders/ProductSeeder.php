<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = resource_path() . '/products.json';

        if (File::exists($file)) {
            $products = json_decode(file_get_contents($file), true);

            foreach ($products as $product) {
                Product::create([
                    'name'  => $product['name'],
                    'price' => $product['price']
                ]);
            }
        }
    }
}
