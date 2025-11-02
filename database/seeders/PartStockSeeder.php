<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartStock;

class PartStockSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            ['part_list_id' => 1, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 1500, 'before_quantity' => 0, 'after_quantity' => 1500],
            ['part_list_id' => 2, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 1200, 'before_quantity' => 0, 'after_quantity' => 1200],
            ['part_list_id' => 3, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 800, 'before_quantity' => 0, 'after_quantity' => 800],
            ['part_list_id' => 4, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 400, 'before_quantity' => 0, 'after_quantity' => 400],
            ['part_list_id' => 5, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 100, 'before_quantity' => 0, 'after_quantity' => 100],
            ['part_list_id' => 6, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 80, 'before_quantity' => 0, 'after_quantity' => 80],
            ['part_list_id' => 7, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 60, 'before_quantity' => 0, 'after_quantity' => 60],
            ['part_list_id' => 8, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 25, 'before_quantity' => 0, 'after_quantity' => 25],
            ['part_list_id' => 9, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 15, 'before_quantity' => 0, 'after_quantity' => 15],
            ['part_list_id' => 10, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 50, 'before_quantity' => 0, 'after_quantity' => 50],
            ['part_list_id' => 11, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 12, 'before_quantity' => 0, 'after_quantity' => 12],
            ['part_list_id' => 12, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 30, 'before_quantity' => 0, 'after_quantity' => 30],
            ['part_list_id' => 13, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 40, 'before_quantity' => 0, 'after_quantity' => 40],
            ['part_list_id' => 14, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 18, 'before_quantity' => 0, 'after_quantity' => 18],
            ['part_list_id' => 15, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 20, 'before_quantity' => 0, 'after_quantity' => 20],
            ['part_list_id' => 16, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 25, 'before_quantity' => 0, 'after_quantity' => 25],
            ['part_list_id' => 17, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 50, 'before_quantity' => 0, 'after_quantity' => 50],
            ['part_list_id' => 18, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 40, 'before_quantity' => 0, 'after_quantity' => 40],
            ['part_list_id' => 19, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 25, 'before_quantity' => 0, 'after_quantity' => 25],
            ['part_list_id' => 20, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 10, 'before_quantity' => 0, 'after_quantity' => 10],
            ['part_list_id' => 21, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 100, 'before_quantity' => 0, 'after_quantity' => 100],
            ['part_list_id' => 22, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 150, 'before_quantity' => 0, 'after_quantity' => 150],
            ['part_list_id' => 23, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 75, 'before_quantity' => 0, 'after_quantity' => 75],
            ['part_list_id' => 24, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 25, 'before_quantity' => 0, 'after_quantity' => 25],
            ['part_list_id' => 25, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 35, 'before_quantity' => 0, 'after_quantity' => 35],
            ['part_list_id' => 26, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 20, 'before_quantity' => 0, 'after_quantity' => 20],
            ['part_list_id' => 27, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 200, 'before_quantity' => 0, 'after_quantity' => 200],
            ['part_list_id' => 28, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 10, 'before_quantity' => 0, 'after_quantity' => 10],
            ['part_list_id' => 29, 'operations' => 'plus', 'source' => 'Purchase GR', 'quantity' => 60, 'before_quantity' => 0, 'after_quantity' => 60],
            ['part_list_id' => 30, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 15, 'before_quantity' => 0, 'after_quantity' => 15],
            ['part_list_id' => 31, 'operations' => 'plus', 'source' => 'Opening Balance', 'quantity' => 10, 'before_quantity' => 0, 'after_quantity' => 10],
        ];

        foreach ($stocks as $stock) {
            PartStock::create($stock);
        }
    }
}
