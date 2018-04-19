<?php

use Illuminate\Database\Seeder;
use Vanilo\Product\Models\Product;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name'             => 'Producto 1',
            'sku'              => '123',
            'slug'             => 'producto-1',
            'description'      => 'Este es el Producto 1',
            'state'            => 'active',
            'price'            => 10.00
        ]);

        Product::create([
            'name'             => 'Producto 2',
            'sku'              => '123',
            'slug'             => 'producto-2',
            'description'      => 'Este es el Producto 2',
            'state'            => 'active',
            'price'            => 20.00
        ]);

        Product::create([
            'name'             => 'Producto 3',
            'sku'              => '123',
            'slug'             => 'producto-3',
            'description'      => 'Este es el Producto 3',
            'state'            => 'active',
            'price'            => 30.00
        ]);
    }
}
