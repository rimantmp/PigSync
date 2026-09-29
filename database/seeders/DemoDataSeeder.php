<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Disease;
use App\Models\Equipment;
use App\Models\FeedType;
use App\Models\Medicine;
use App\Models\Payment;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigBreed;
use App\Models\PurchaseInvoice;
use App\Models\Revenue;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\BirthService;
use App\Services\DeathService;
use App\Services\HealthService;
use App\Services\PigService;
use App\Services\PurchaseService;
use App\Services\SaleService;
use App\Services\StockService;
use App\Services\WeighingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Data demo untuk pengembangan & preview UI.
 *
 * Semua transaksi dibuat lewat service, bukan Model::create langsung,
 * supaya business rule (BR-01 s.d. BR-16), audit log, dan validasi
 * konsisten dengan alur produksi. Kalau diisi langsung ke model,
 * data demo akan terlihat benar di layar tapi melanggar aturan yang
 * sebenarnya ditegakkan aplikasi.
 *
 * Jalankan: php artisan migrate:fresh --seed
 */
class DemoDataSeeder extends Seeder
{
    private User $admin;

    private Pen $penFattening;

    private Pen $penFarrowing;

    private Pen $penQuarantine;

    private array $feeds = [];

    private array $medicines = [];

    private array $diseases = [];

    public function run(): void
    {
        // Seeder ini bergantung pada output DatabaseSeeder (role + user admin).
        // Kalau dipanggil sendiri tanpa itu, berhenti dengan pesan jelas
        // alih-alih ModelNotFoundException yang tidak informatif.
        $this->admin = User::where('email', 'admin@sistemkandang.test')->first();

        if ($this->admin === null) {
            $this->command?->error(
                'User admin@sistemkandang.test tidak ditemukan. '
                .'Jalankan DatabaseSeeder dulu: php artisan db:seed'
            );

            throw new \RuntimeException('Data demo butuh user admin — jalankan DatabaseSeeder lebih dulu.');
        }

        Auth::login($this->admin);

        $master = $this->seedMaster();
        $pens = $this->seedPens($master);

        $this->penFattening = $pens['fattening'];
        $this->penFarrowing = $pens['farrowing'];
        $this->penQuarantine = $pens['quarantine'];

        $pigs = $this->seedPigs($master, $pens);
        $this->seedWeights($pigs);
        $this->seedHealth($pigs, $master);
        $this->seedReproduction($pigs);
        $this->seedDeaths($pigs, $master);
        $this->seedSales($pigs, $master);
        $this->seedStock($pens, $master);
        $this->seedPurchasing($master);

        $this->command?->newLine();
        $this->command?->info('Data demo selesai. Login: admin@sistemkandang.test / password');
    }

    /**
     * Master data tambahan di luar DatabaseSeeder (supplier, pelanggan,
     * penyakit, conductors, Instrumentation).
     *
     * @return array<string, mixed>
     */
    private function seedMaster(): array
    {
        $feeds = [
            ['code' => 'FD-ST', 'name' => 'Pakan Starter', 'category' => 'starter', 'default_price' => 18500],
            ['code' => 'FD-GR', 'name' => 'Pakan Grower', 'category' => 'grower', 'default_price' => 16000],
            ['code' => 'FD-FN', 'name' => 'Pakan Finisher', 'category' => 'finisher', 'default_price' => 14500],
            ['code' => 'FD-SW', 'name' => 'Pakan Sow', 'category' => 'sow', 'default_price' => 15000],
            ['code' => 'FD-SW-G', 'name' => 'Pakan Sow Gestasi', 'category' => 'sow', 'default_price' => 15200],
        ];

        foreach ($feeds as $f) {
            $this->feeds[$f['code']] = FeedType::firstOrCreate(
                ['code' => $f['code']],
                $f + ['unit_id' => Unit::where('code', 'kg')->value('id')]
            );
        }

        $medicines = [
            ['code' => 'MDC-ABX', 'name' => 'Antibiotik Broad Spectrum', 'kind' => 'medicine', 'withdrawal_days' => 14],
            ['code' => 'MDC-VTM', 'name' => 'Vitamin Multikomponen', 'kind' => 'medicine', 'withdrawal_days' => 0],
            ['code' => 'VCN-PCV2', 'name' => 'Vaksin PCV2', 'kind' => 'vaccine', 'withdrawal_days' => 0],
            ['code' => 'VCN-HS', 'name' => 'Vaksin Hog Cholera', 'kind' => 'vaccine', 'withdrawal_days' => 0],
            ['code' => 'MDC-DEP', 'name' => 'Dewormer', 'kind' => 'medicine', 'withdrawal_days' => 7],
        ];

        foreach ($medicines as $m) {
            $this->medicines[$m['code']] = Medicine::firstOrCreate(
                ['code' => $m['code']],
                $m + ['unit_id' => null, 'default_dose' => null, 'auto_deduct' => true]
            );
        }

        $diseases = [
            ['code' => 'DZ-PRS', 'name' => 'PRRS', 'category' => 'virus', 'is_zoonosis' => false],
            ['code' => 'DZ-ASC', 'name' => 'ASF (African Swine Fever)', 'category' => 'virus', 'is_zoonosis' => true],
            ['code' => 'DZ-DIA', 'name' => 'Diare', 'category' => 'digestif', 'is_zoonosis' => false],
            ['code' => 'DZ-PNE', 'name' => 'Pneumonia', 'category' => 'respirasi', 'is_zoonosis' => false],
            ['code' => 'DZ-MST', 'name' => 'Mastitis', 'category' => 'reproduksi', 'is_zoonosis' => false],
        ];

        foreach ($diseases as $d) {
            $this->diseases[$d['code']] = Disease::firstOrCreate(
                ['code' => $d['code']],
                $d + ['protocol' => null]
            );
        }

        $suppliers = [
            ['code' => 'SUP-01', 'name' => 'CV Sumber Pakan Sejahtera', 'type' => 'pakan', 'contact' => 'Bpk. Rahmat', 'phone' => '0812-3456-7890', 'bank' => 'BCA 1234567890'],
            ['code' => 'SUP-02', 'name' => 'PT Satwa Medika', 'type' => 'obat', 'contact' => 'Ibu Sari', 'phone' => '0813-9876-5432', 'bank' => 'Mandiri 0987654321'],
            ['code' => 'SUP-03', 'name' => 'CV Perlengkapan Kandang', 'type' => 'lain', 'contact' => 'Bpk. Joko', 'phone' => '0857-1122-3344', 'bank' => 'BNI 5566778899'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['code' => $s['code']], $s);
        }

        $customers = [
            ['code' => 'CUS-01', 'name' => 'Bapak Budi — Warung Sate Pak Budi', 'type' => 'konsumsi', 'contact' => 'Bpk. Budi', 'phone' => '0812-7777-8888', 'bank' => null],
            ['code' => 'CUS-02', 'name' => 'CV Resto Mie Ayam Jaya', 'type' => 'konsumsi', 'contact' => 'Ibu Tini', 'phone' => '0819-2222-3333', 'bank' => null],
            ['code' => 'CUS-03', 'name' => 'Peternakan Makmur Mandiri', 'type' => 'pejantan', 'contact' => 'Bpk. Anton', 'phone' => '0821-5555-6666', 'bank' => 'BRI 3344556677'],
        ];

        foreach ($customers as $c) {
            Customer::firstOrCreate(['code' => $c['code']], $c);
        }

        $equipment = [
            ['code' => 'EQ-TP01', 'name' => 'Tempat Pakan Feeder', 'category' => 'kandang'],
            ['code' => 'EQ-DR01', 'name' => 'Drinker Nipple', 'category' => 'kandang'],
            ['code' => 'EQ-SC01', 'name' => 'Timbangan Digital 200kg', 'category' => 'peralatan'],
            ['code' => 'EQ-SR01', 'name' => 'Spuit 5ml', 'category' => 'peralatan'],
            ['code' => 'EQ-BT01', 'name' => 'Botol Susu 250ml', 'category' => 'perlengkapan'],
        ];

        foreach ($equipment as $e) {
            Equipment::firstOrCreate(
                ['code' => $e['code']],
                $e + ['unit_id' => Unit::where('code', 'pcs')->value('id') ?? null, 'default_price' => null]
            );
        }

        return [
            'breeds' => PigBreed::orderBy('id')->get()->all(),
            'suppliers' => Supplier::orderBy('id')->get()->all(),
            'customers' => Customer::orderBy('id')->get()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $master
     * @return array{fattening: Pen, farrowing: Pen, quarantine: Pen}
     */
    private function seedPens(array $master): array
    {
        $branch = Branch::where('code', 'SKM')->firstOrFail();

        // Tabel areas tidak punya kolom code/status — hanya nama & urutan.
        $area = Area::firstOrCreate(
            ['branch_id' => $branch->id, 'name' => 'Area Utama'],
            ['sort_order' => 0]
        );

        $pens = [
            'fattening' => ['code' => 'SKM-A1', 'name' => 'Kandang A1 — Finisher', 'type' => 'fattening', 'capacity' => 60],
            'fattening2' => ['code' => 'SKM-A2', 'name' => 'Kandang A2 — Finisher', 'type' => 'fattening', 'capacity' => 60],
            'farrowing' => ['code' => 'SKM-F1', 'name' => 'Kandang F1 — Kelahiran', 'type' => 'farrowing', 'capacity' => 24],
            'quarantine' => ['code' => 'SKM-Q1', 'name' => 'Kandang Q1 — Karantina', 'type' => 'quarantine', 'capacity' => 20],
        ];

        $result = [];

        foreach ($pens as $key => $pen) {
            $result[$key] = Pen::firstOrCreate(
                ['code' => $pen['code']],
                [
                    'branch_id' => $branch->id,
                    'area_id' => $area->id,
                    'name' => $pen['name'],
                    'type' => $pen['type'],
                    'capacity' => $pen['capacity'],
                    'status' => 'aktif',
                ]
            );
        }

        Warehouse::firstOrCreate(
            ['code' => 'WH-SKM'],
            ['name' => 'Gudang Cabang Sukamaju', 'branch_id' => $branch->id]
        );

        return [
            'fattening' => $result['fattening'],
            'fattening2' => $result['fattening2'],
            'farrowing' => $result['farrowing'],
            'quarantine' => $result['quarantine'],
        ];
    }

    /**
     * @param  array<string, mixed>  $master
     * @param  array<string, Pen>  $pens
     * @return array<string, array<int, Pig>> kelompok ras
     */
    private function seedPigs(array $master, array $pens): array
    {
        $pigs = app(PigService::class);

        // Tiga indukan betina/jantan untuk data reproduksi, sisanya grower dan finisher.
        // tanpa lambat karena tiap registrasi lewat service + audit.
        $plan = [
            // Dua indukan betina + satu pejantan untuk data reproduksi.
            'induk' => [
                ['sex' => 'betina', 'age' => 420, 'pen' => 'farrowing', 'weight' => 165],
                ['sex' => 'betina', 'age' => 380, 'pen' => 'farrowing', 'weight' => 158],
                ['sex' => 'jantan', 'age' => 400, 'pen' => 'fattening', 'weight' => 172],
            ],
            // Grower & finisher.
            'grower' => [
                ['sex' => 'jantan', 'age' => 75, 'pen' => 'fattening', 'weight' => 42],
                ['sex' => 'betina', 'age' => 72, 'pen' => 'fattening', 'weight' => 40],
                ['sex' => 'jantan', 'age' => 80, 'pen' => 'fattening', 'weight' => 46],
                ['sex' => 'betina', 'age' => 68, 'pen' => 'fattening2', 'weight' => 38],
                ['sex' => 'jantan', 'age' => 60, 'pen' => 'fattening2', 'weight' => 34],
                ['sex' => 'betina', 'age' => 82, 'pen' => 'fattening', 'weight' => 48],
                ['sex' => 'jantan', 'age' => 66, 'pen' => 'fattening2', 'weight' => 36],
                ['sex' => 'betina', 'age' => 78, 'pen' => 'fattening', 'weight' => 44],
            ],
            'finisher' => [
                ['sex' => 'jantan', 'age' => 140, 'pen' => 'fattening', 'weight' => 96],
                ['sex' => 'betina', 'age' => 135, 'pen' => 'fattening', 'weight' => 92],
                ['sex' => 'jantan', 'age' => 128, 'pen' => 'fattening2', 'weight' => 88],
                ['sex' => 'betina', 'age' => 145, 'pen' => 'fattening2', 'weight' => 99],
                ['sex' => 'jantan', 'age' => 150, 'pen' => 'fattening', 'weight' => 102],
                ['sex' => 'betina', 'age' => 138, 'pen' => 'fattening2', 'weight' => 94],
                ['sex' => 'jantan', 'age' => 132, 'pen' => 'fattening', 'weight' => 90],
                ['sex' => 'betina', 'age' => 155, 'pen' => 'fattening2', 'weight' => 105],
            ],
        ];

        $result = [];

        foreach ($plan as $group => $rows) {
            foreach ($rows as $i => $row) {
                $breed = $master['breeds'][$i % count($master['breeds'])];

                $result[$group][] = $pigs->register([
                    'sex' => $row['sex'],
                    'breed_id' => $breed->id,
                    'birth_date' => now()->subDays($row['age'])->toDateString(),
                    'origin_type' => 'internal',
                    'pen_id' => $pens[$row['pen']]->id,
                    'initial_weight' => $row['weight'],
                    'tag_id' => 'ID'.str_pad((string) (($i + 1) * 7), 6, '0', STR_PAD_LEFT),
                ]);
            }
        }

        return $result;
    }

    /**
     * Riwayat timbang 3x per ternak supaya ADG terhitung.
     *
     * @param  array<string, array<int, Pig>>  $groups
     */
    private function seedWeights(array $groups): void
    {
        $weighing = app(WeighingService::class);

        foreach ($groups as $pigs) {
            foreach ($pigs as $pig) {
                $current = (float) ($pig->initial_weight ?? 40);

                // Mundur 60 dan 30 hari, bobot naik ~0,7 kg/hari.
                $weighing->record($pig, [
                    'weighed_at' => now()->subDays(60)->toDateString(),
                    'weight' => round($current - 60 * 0.65, 1),
                    'method' => 'individu',
                ]);

                $weighing->record($pig, [
                    'weighed_at' => now()->subDays(30)->toDateString(),
                    'weight' => round($current - 30 * 0.7, 1),
                    'method' => 'individu',
                ]);

                $weighing->record($pig, [
                    'weighed_at' => now()->subDays(3)->toDateString(),
                    'weight' => $current,
                    'method' => 'individu',
                ]);
            }
        }
    }

    /**
     * @param  array<string, array<int, Pig>>  $groups
     * @param  array<string, mixed>  $master
     */
    private function seedHealth(array $groups, array $master): void
    {
        $health = app(HealthService::class);

        // Ternak yang dibuat sakit hanya dari kelompok grower, supaya
        // kelompok ini masih punya ekor sehat untuk target kematian.
        $sickPool = $groups['grower'];

        // Sebagian ternary dibuat sakit dengan kondisi berbeda, supaya laporan
        // kesehatan tidak seragam dan ada data untuk tes tampilan.
        $cases = [
            ['disease' => 'DZ-PRS', 'medicine' => 'MDC-ABX', 'diagnosis' => 'Batuk kering, nafsu makan turun'],
            ['disease' => 'DZ-DIA', 'medicine' => 'MDC-VTM', 'diagnosis' => 'Diare ringan, feses encer'],
            ['disease' => 'DZ-PNE', 'medicine' => 'MDC-ABX', 'diagnosis' => 'Batuk basah, suhu tinggi'],
            ['disease' => 'DZ-MST', 'medicine' => 'MDC-ABX', 'diagnosis' => 'Mamitis, putting abnormal'],
            ['disease' => 'DZ-PRS', 'medicine' => 'MDC-ABX', 'diagnosis' => 'Nafsu makan menurun, sesak nafas'],
        ];

        // Tiga ekor grower dibuat sakit; sisa grower tetap sehat supaya
        // masih bisa dipakai sebagai target kematian.
        $sickTargets = array_slice($sickPool, 0, 3);

        foreach ($sickTargets as $i => $pig) {
            $case = $cases[$i % count($cases)];

            $health->record($pig, [
                'checked_at' => now()->subDays(random_int(1, 20))->toDateString(),
                'symptoms' => $case['diagnosis'],
                'disease_id' => $this->diseases[$case['disease']]?->id,
                'diagnosis' => $case['diagnosis'],
                'medicine_id' => $this->medicines[$case['medicine']]?->id,
                'dose' => '2 ml',
                'route' => 'injeksi',
                'vet' => 'Dr. Andi, S.H.',
                'notes' => 'Perlu monitoring 3 hari',
            ]);
        }

        // Satu induk ringan agar ada catatan kesehatan pada kelompok menyusui.
        $this->recordCheckup($health, $groups['induk'][1] ?? null, $cases[0]);
    }

    /**
     * @param  array<string, mixed>  $case
     */
    private function recordCheckup(HealthService $health, ?Pig $pig, array $case): void
    {
        if ($pig === null) {
            return;
        }

        $health->record($pig, [
            'checked_at' => now()->subDays(12)->toDateString(),
            'symptoms' => $case['diagnosis'],
            'disease_id' => $this->diseases[$case['disease']]?->id,
            'diagnosis' => $case['diagnosis'],
            'medicine_id' => $this->medicines[$case['medicine']]?->id,
            'dose' => '5 ml',
            'route' => 'injeksi',
            'vet' => 'Dr. Andi, S.H.',
            'notes' => 'Sembuh, lanjut observasi',
        ]);
    }

    /**
     * Kelahiran — memperbesar populasi dan memberi data reproduksi.
     *
     * @param  array<string, array<int, Pig>>  $groups
     */
    private function seedReproduction(array $groups): void
    {
        $births = app(BirthService::class);

        $sows = $groups['induk'];
        $sow = $sows[0] ?? null;
        $sow2 = $sows[1] ?? null;

        if ($sow) {
            $births->farrow($sow, [
                'pen_id' => $this->penFarrowing->id,
                'farrowed_at' => now()->subDays(21)->toDateString(),
                'total_born' => 12,
                'born_alive' => 10,
                'born_dead' => 1,
                'mummified' => 1,
                'avg_weight' => 1.4,
                'assistant' => 'Bpk. Joko',
                'notes' => 'Kelahiran normal, induksi baik',
            ]);
        }

        if ($sow2) {
            $births->farrow($sow2, [
                'pen_id' => $this->penFarrowing->id,
                'farrowed_at' => now()->subDays(7)->toDateString(),
                'total_born' => 9,
                'born_alive' => 8,
                'born_dead' => 1,
                'mummified' => 0,
                'avg_weight' => 1.5,
                'assistant' => 'Bpk. Joko',
            ]);
        }
    }

    /**
     * @param  array<string, array<int, Pig>>  $groups
     * @param  array<string, mixed>  $master
     */
    private function seedDeaths(array $groups, array $master): void
    {
        $deaths = app(DeathService::class);

        $causes = [
            ['cause' => 'Pneumonia', 'disease' => 'DZ-PNE'],
            ['cause' => 'Diare akut', 'disease' => 'DZ-DIA'],
            ['cause' => 'Kolera', 'disease' => null],
            ['cause' => 'Infeksi, cedera', 'disease' => null],
        ];

        // Kematian memakai 4 ekor grower mulai indeks ke-4; 3 ekor pertama
        // sudah dipakai sebagai simulate quest sehingga tidak bentrok.
        $targets = array_slice($groups['grower'], 3, 4);

        $recorded = 0;

        foreach ($targets as $i => $pig) {
            // Status dibaca ulang dari DB: instance in-memory masih menyimpan
            // nilai dari sebelum langkah sebelumnya.
            if ($pig->refresh()->status !== 'aktif') {
                continue;
            }

            $case = $causes[$i % count($causes)];

            $deaths->record($pig, [
                'died_at' => now()->subDays(random_int(2, 25))->toDateString(),
                'cause' => $case['cause'],
                'suspected_disease_id' => $case['disease'] ? $this->diseases[$case['disease']]?->id : null,
                'disposal' => 'Dikubur',
                'notes' => 'Ternak ditemukan saat patrol pagi',
            ]);

            $recorded++;
        }

        $this->command?->line("  Kematian tercatat: {$recorded} ekor");
    }

    /**
     * @param  array<string, array<int, Pig>>  $groups
     * @param  array<string, mixed>  $master
     */
    private function seedSales(array $groups, array $master): void
    {
        $sales = app(SaleService::class);
        $branchId = $this->penFattening->branch_id;

        // 2 ekor dari depan — dijamin terpisah dari ekor yang dicatat mati.
        $targets = array_slice($groups['finisher'], 0, 2);
        $sold = 0;

        foreach ($targets as $i => $pig) {
            if ($pig->refresh()->status !== 'aktif') {
                continue;
            }

            // Ternak yang sedang sakit/baru diobati berada dalam masa
            // withdrawal dan tidak boleh dijual.
            if ($pig->healthRecords()->whereDate('withdrawal_until', '>=', now())->exists()) {
                $this->command?->warn("  Leuati penjualan {$pig->code}: masih masa withdrawal.");

                continue;
            }

            $customer = $master['customers'][$i % count($master['customers'])];

            $sales->sell($branchId, $customer->id, [
                [
                    'pig_id' => $pig->id,
                    'weight' => (float) ($pig->initial_weight ?? 90),
                    'price_per_kg' => 42000,
                ],
            ], [
                'sale_date' => now()->subDays(random_int(3, 15))->toDateString(),
                'payment_status' => $i === 0 ? 'lunas' : 'belum_bayar',
            ]);

            $sold++;
        }

        if ($sold === 0) {
            $this->command?->warn('  Peringatan: tidak ada Animalia yang terjual (semua mati/withdrawal).');
        }
    }

    /**
     * @param  array<string, Pen>  $pens
     * @param  array<string, mixed>  $master
     */
    private function seedStock(array $pens, array $master): void
    {
        $stock = app(StockService::class);
        $warehouse = Warehouse::where('code', 'WH-SKM')->firstOrFail();

        foreach ($this->feeds as $code => $feed) {
            $stock->receive($warehouse, 'feed', $feed->id, random_int(400, 1500), [
                'unit_id' => $feed->unit_id,
                'min_stock' => 200,
                'notes' => 'Stok awal period',
            ]);
        }

        foreach ($this->medicines as $code => $medicine) {
            $stock->receive($warehouse, 'medicine', $medicine->id, random_int(20, 80), [
                'unit_id' => null,
                'min_stock' => 10,
                'notes' => 'Stok awal period',
            ]);
        }
    }

    /**
     * Alur pembelian: PR → PO → penerimaan → invoice → pembayaran.
     *
     * @param  array<string, mixed>  $master
     */
    private function seedPurchasing(array $master): void
    {
        $purchases = app(PurchaseService::class);
        $branchId = $this->penFattening->branch_id;
        $supplier = $master['suppliers'][0];

        // 1. Purchase Request
        $pr = $purchases->createRequest($branchId, [
            ['item_type' => 'feed', 'item_id' => $this->feeds['FD-GR']->id, 'qty' => 500, 'unit_id' => $this->feeds['FD-GR']->unit_id, 'est_price' => 16000],
            ['item_type' => 'feed', 'item_id' => $this->feeds['FD-ST']->id, 'qty' => 250, 'unit_id' => $this->feeds['FD-ST']->unit_id, 'est_price' => 18500],
        ], ['request_date' => now()->subDays(12)->toDateString(), 'notes' => 'Kebutuhan pakan 2 minggu']);

        $pr->update(['status' => 'approved']);

        // 2. PO dari PR yang disetujui
        $po = $purchases->createOrderFromRequest($pr, $supplier->id, [
            'po_date' => now()->subDays(11)->toDateString(),
            'payment_method' => 'transfer',
            'due_date' => now()->addDays(19)->toDateString(),
            'notes' => 'Pengiriman bertahap',
        ]);

        // 3. Penerimaan sebagian — supaya struk menunjukkan qty nyata
        $receiptQty = [];

        foreach ($po->items as $item) {
            $receiptQty[$item->id] = (float) $item->qty * 0.6;
        }

        $purchases->receive($po, [
            'received_at' => now()->subDays(8)->toDateString(),
            'received_qty' => $receiptQty,
            'notes' => 'Pengiriman pertama, sisanya menyusul',
        ]);

        // 4. Invoice
        $invoice = $purchases->invoice($po, [
            'invoice_number' => 'INV-'.now()->format('Ymd').'-001',
            'invoice_date' => now()->subDays(7)->toDateString(),
            'total' => $po->total,
        ]);

        // 5. Pembayaran (lunas → revenue tercatat)
        $invoice->update(['status' => 'lunas']);
        $po->update(['status' => 'lunas']);

        Payment::create([
            'payable_type' => PurchaseInvoice::class,
            'payable_id' => $invoice->id,
            'amount' => $invoice->total,
            'method' => 'transfer',
            'paid_at' => now()->subDays(5)->toDateString(),
            'status' => 'lunas',
            'user_id' => $this->admin->id,
        ]);

        Revenue::create([
            'branch_id' => $branchId,
            'source' => 'penjualan',
            'amount' => $invoice->total,
            'received_at' => now()->subDays(5)->toDateString(),
            'reference_type' => PurchaseInvoice::class,
            'reference_id' => $invoice->id,
            'notes' => 'Pembayaran invoice pembelian',
            'user_id' => $this->admin->id,
        ]);
    }
}
