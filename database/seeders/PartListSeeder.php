<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PartList;

class PartListSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            // 🧱 Raw Material
            ['asset_code_id' => 0, 'part_name' => 'Polypropylene (PP) Resin', 'category' => 'Raw Material', 'UoM' => 'kg', 'type' => 'container dan cup', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Polyethylene (PE) Resin', 'category' => 'Raw Material', 'UoM' => 'kg', 'type' => 'film dan lembaran packaging', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'PET Granule', 'category' => 'Raw Material', 'UoM' => 'kg', 'type' => 'botol dan packaging transparan', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'HIPS Sheet (High Impact Polystyrene)', 'category' => 'Raw Material', 'UoM' => 'sheet', 'type' => 'lid dan tray', 'requires_stock_reduction' => 1],

            // 🎨 Additive
            ['asset_code_id' => 0, 'part_name' => 'Color Masterbatch – White', 'category' => 'Additive', 'UoM' => 'kg', 'type' => 'Pewarna putih untuk molded part', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Color Masterbatch – Blue', 'category' => 'Additive', 'UoM' => 'kg', 'type' => 'Pigmen biru untuk sheet dan product film', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Antistatic Additive', 'category' => 'Additive', 'UoM' => 'kg', 'type' => '-', 'requires_stock_reduction' => 1],

            // ⚙️ Spare Part
            ['asset_code_id' => 0, 'part_name' => 'Heater Band 240V 1000W', 'category' => 'Spare Part', 'UoM' => 'pcs', 'type' => 'heater untuk barrel extruder', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Thermocouple Sensor', 'category' => 'Spare Part', 'UoM' => 'pcs', 'type' => 'Sensor molding', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Hydraulic Seal Kit', 'category' => 'Spare Part', 'UoM' => 'set', 'type' => 'untuk silinder hidrolik', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Vacuum Pump Filter', 'category' => 'Spare Part', 'UoM' => 'pcs', 'type' => 'Filter pompa vacuum', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Air Compressor Filter', 'category' => 'Spare Part', 'UoM' => 'pcs', 'type' => 'untuk kompresor', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Solenoid Valve 24V', 'category' => 'Spare Part', 'UoM' => 'pcs', 'type' => 'pneumatic valve', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Mould Clamp Set', 'category' => 'Spare Part', 'UoM' => 'set', 'type' => 'kunci mold injection', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Rubber Gasket (Seal Mold)', 'category' => 'Rubber Part', 'UoM' => 'pcs', 'type' => 'seal mold', 'requires_stock_reduction' => 1],

            // 🧰 Machine Maintenance
            ['asset_code_id' => 0, 'part_name' => 'Lubricating Oil – Shell Tellus 68', 'category' => 'Machine Maintenance', 'UoM' => 'L', 'type' => 'oli mesin prod', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Grease EP2', 'category' => 'Machine Maintenance', 'UoM' => 'kg', 'type' => 'pelumas', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Mold Release Spray', 'category' => 'Machine Maintenance', 'UoM' => 'can', 'type' => 'mold remover', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Cleaning Solvent', 'category' => 'Machine Maintenance', 'UoM' => 'L', 'type' => 'mold remover', 'requires_stock_reduction' => 1],

            // 📦 Packaging Material
            ['asset_code_id' => 0, 'part_name' => 'Stretch Film', 'category' => 'Packaging Material', 'UoM' => 'roll', 'type' => 'wrapping finished goods', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Polybag – Large (60x90cm)', 'category' => 'Packaging Material', 'UoM' => 'pack', 'type' => 'polybag besar', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Carton Box – Medium', 'category' => 'Packaging Material', 'UoM' => 'pcs', 'type' => 'Box karton', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Packing Tape Brown', 'category' => 'Packaging Material', 'UoM' => 'roll', 'type' => 'sealing carton', 'requires_stock_reduction' => 1],

            // 🦺 Safety Equipment
            ['asset_code_id' => 0, 'part_name' => 'Safety Gloves', 'category' => 'Safety Equipment', 'UoM' => 'pair', 'type' => 'Sarung tangan safety untuk operator', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Face Mask', 'category' => 'Safety Equipment', 'UoM' => 'box', 'type' => 'Masker area produksi', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Ear Plug', 'category' => 'Safety Equipment', 'UoM' => 'pack', 'type' => 'Pelindung telinga', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Safety Shoes', 'category' => 'Safety Equipment', 'UoM' => 'pair', 'type' => 'Sepatu safety teknisi/produksi', 'requires_stock_reduction' => 1],

            // 🗂️ Office Supply
            ['asset_code_id' => 0, 'part_name' => 'A4 Paper 80gsm', 'category' => 'Office Supply', 'UoM' => 'ream', 'type' => 'Kertas print', 'requires_stock_reduction' => 0],
            ['asset_code_id' => 0, 'part_name' => 'Printer Ink (Black)', 'category' => 'Office Supply', 'UoM' => 'pcs', 'type' => 'Tinta hitam ', 'requires_stock_reduction' => 0],
            // ['asset_code_id' => 0, 'part_name' => 'Stationery Set', 'category' => 'Office Supply', 'UoM' => 'set', 'type' => 'Isi: pulpen, spidol, marker, correction pen', 'requires_stock_reduction' => 0],
            ['asset_code_id' => 0, 'part_name' => 'Copy Paper (Legal)', 'category' => 'Office Supply', 'UoM' => 'ream', 'type' => 'Kertas legal untuk dokumentasi', 'requires_stock_reduction' => 0],

            // 🔌 Utility
            ['asset_code_id' => 0, 'part_name' => 'Cooling Water Hose 1/2”', 'category' => 'Utility', 'UoM' => 'm', 'type' => 'Selang air pendingin untuk mesin', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Pressure Gauge 0–10 bar', 'category' => 'Utility', 'UoM' => 'pcs', 'type' => 'Untuk monitoring tekanan udara di line produksi', 'requires_stock_reduction' => 1],
            ['asset_code_id' => 0, 'part_name' => 'Plastic Pallet', 'category' => 'Utility', 'UoM' => 'pcs', 'type' => 'Digunakan untuk material handling di warehouse', 'requires_stock_reduction' => 1],
        ];

        foreach ($parts as $part) {
            // $part['user_id'] = 3;  
            PartList::create($part);
        }
    }
}
