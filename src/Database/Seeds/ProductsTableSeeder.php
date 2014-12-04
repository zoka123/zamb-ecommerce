<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class ProductsTableSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 50) as $index) {
            Product::create([
                'title' => $faker->realText(25),
                'description' => $faker->realText(300),
                'price' => rand(0, 140)
            ]);
        }
    }

}