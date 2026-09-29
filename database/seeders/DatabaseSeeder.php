<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Disease;
use App\Models\FeedType;
use App\Models\Medicine;
use App\Models\Pen;
use App\Models\PigBreed;
use App\Models\PigPhase;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $this->seedSettings();
        $this->seedUsers();
        $this->seedMasterData();
    }

    private function seedSettings(): void
    {
        $defaults = [
            'feed_cost_method' => 'average',      // Q1
            'farrowing_days' => '114',            // Q2
            'death_alert_threshold' => '2',       // Q3
            'medicine_auto_deduct' => 'optional', // Q4
            'piglet_id_mode' => 'individual',     // Q6
            'harga_pasar_kg' => '40000',
            'kapasitas_alert_pct' => '90',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    private function seedUsers(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistemkandang.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'branch_scope' => null,
                'is_active' => true,
            ]
        );
        $admin->assignRole('Super Admin');
    }

    private function seedMasterData(): void
    {
        // Satuan
        foreach (['kg' => 'Kilogram', 'karung' => 'Karung 50kg', 'botol' => 'Botol', 'dosis' => 'Dosis'] as $code => $name) {
            Unit::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        // Fase ternak
        $phases = [
            ['code' => 'WNG', 'name' => 'Weaning', 'age_min' => 0, 'age_max' => 40],
            ['code' => 'GR', 'name' => 'Grower', 'age_min' => 41, 'age_max' => 100],
            ['code' => 'FN', 'name' => 'Finisher', 'age_min' => 101, 'age_max' => 180],
            ['code' => 'SW', 'name' => 'Sow (Induk)', 'age_min' => null, 'age_max' => null],
        ];
        foreach ($phases as $i => $p) {
            PigPhase::firstOrCreate(['code' => $p['code']], $p + ['sort_order' => $i]);
        }

        // Ras
        foreach ([
            ['code' => 'YSH', 'name' => 'Yorkshire'],
            ['code' => 'LDR', 'name' => 'Landrace'],
            ['code' => 'DRC', 'name' => 'Duroc'],
        ] as $b) {
            PigBreed::firstOrCreate(['code' => $b['code']], $b);
        }

        // Jenis pakan
        $kg = Unit::where('code', 'kg')->first();
        foreach ([
            ['code' => 'FD-ST', 'name' => 'Pakan Starter', 'category' => 'starter'],
            ['code' => 'FD-GR', 'name' => 'Pakan Grower', 'category' => 'grower'],
            ['code' => 'FD-FN', 'name' => 'Pakan Finisher', 'category' => 'finisher'],
            ['code' => 'FD-SW', 'name' => 'Pakan Sow', 'category' => 'sow'],
        ] as $f) {
            FeedType::firstOrCreate(['code' => $f['code']], $f + ['unit_id' => $kg?->id, 'default_price' => 12000]);
        }

        // Obat & vaksin
        foreach ([
            ['code' => 'MDC-ABX', 'name' => 'Antibiotik', 'kind' => 'medicine', 'withdrawal_days' => 14],
            ['code' => 'VCN-PCV', 'name' => 'Vaksin PCV2', 'kind' => 'vaccine', 'withdrawal_days' => 0],
        ] as $m) {
            Medicine::firstOrCreate(['code' => $m['code']], $m + ['unit_id' => null, 'default_dose' => null, 'auto_deduct' => true]);
        }

        // Penyakit
        foreach ([
            ['code' => 'DZ-PRS', 'name' => 'PRRS', 'category' => 'virus', 'is_zoonosis' => false],
            ['code' => 'DZ-ASC', 'name' => 'ASF (African Swine Fever)', 'category' => 'virus', 'is_zoonosis' => false],
        ] as $d) {
            Disease::firstOrCreate(['code' => $d['code']], $d + ['protocol' => null]);
        }

        // Cabang + kandang + gudang contoh
        $branch = Branch::firstOrCreate(
            ['code' => 'SKM'],
            ['name' => 'Cabang Sukamaju', 'address' => '-', 'status' => 'aktif']
        );

        foreach ([
            ['code' => 'SKM-A1', 'name' => 'Kandang A1', 'type' => 'fattening'],
            ['code' => 'SKM-A2', 'name' => 'Kandang A2', 'type' => 'farrowing'],
            ['code' => 'SKM-Q1', 'name' => 'Karantina', 'type' => 'quarantine'],
        ] as $pen) {
            Pen::firstOrCreate(['code' => $pen['code']], $pen + [
                'branch_id' => $branch->id,
                'area_id' => null,
                'capacity' => 100,
                'status' => 'aktif',
            ]);
        }

        Warehouse::firstOrCreate(
            ['code' => 'WH-SKM'],
            ['name' => 'Gudang Cabang Sukamaju', 'branch_id' => $branch->id]
        );
    }
}
