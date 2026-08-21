<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\TeacherBalance;
use App\Support\TeacherEarnings;
use Illuminate\Console\Command;

class UpdateTeacherBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:update-balances {--force : Force update existing balances}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update teacher balances from successful purchases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting teacher balance update...');

        // Get all successful purchases with course and teacher relationship
        $purchases = Purchase::where('status', 'success')
            ->with(['course' => function ($query) {
                $query->select('id', 'teacher_id', 'name');
            }])
            ->get();

        $this->info("Found {$purchases->count()} successful purchases");

        $updated = 0;
        $errors = 0;

        foreach ($purchases as $purchase) {
            try {
                if (!$purchase->course) {
                    $this->warn("Purchase {$purchase->id} has no course");
                    $errors++;
                    continue;
                }

                $teacher = $purchase->course->teacher;
                if (!$teacher) {
                    $this->warn("Course {$purchase->course->id} has no teacher");
                    $errors++;
                    continue;
                }

                // Get or create teacher balance
                $balance = TeacherBalance::firstOrCreate(
                    ['teacher_id' => $teacher->id],
                    [
                        'balance' => 0,
                        'total_earnings' => 0,
                        'total_withdrawn' => 0,
                        'pending_earnings' => 0,
                    ]
                );

                // Pendapatan yang diharapkan adalah nilai BERSIH setelah komisi.
                //
                // Sebelumnya baris ini memakai:
                //     ->sum(\DB::raw('COALESCE(teacher_earning, amount)'))
                // Kolom teacher_earning tidak pernah diisi, jadi COALESCE selalu
                // jatuh ke `amount` - harga bruto - dan perintah ini justru ikut
                // menulis angka sebelum potongan komisi ke tabel saldo.
                $summary = TeacherEarnings::summaryFor($teacher->id);
                $expectedEarnings = $summary['net'];

                // Dibandingkan dengan selisih, bukan "kurang dari", supaya saldo
                // yang terlanjur kelebihan (bruto) ikut dikoreksi ke bawah.
                if (abs((float) $balance->total_earnings - $expectedEarnings) >= 0.01) {
                    $sebelum = (float) $balance->total_earnings;

                    $balance->total_earnings = $expectedEarnings;

                    // total_withdrawn tidak disentuh: histori penarikan yang sudah
                    // diajukan/disetujui adalah catatan keuangan, bukan turunan.
                    $balance->balance = max(0, $expectedEarnings - (float) $balance->total_withdrawn);
                    $balance->last_updated = now();
                    $balance->save();
                    $updated++;

                    $this->line(sprintf(
                        'Updated balance for teacher %d (%s): Rp%s -> Rp%s (komisi guru %s%%)',
                        $teacher->id,
                        $teacher->name,
                        number_format($sebelum, 0, ',', '.'),
                        number_format($expectedEarnings, 0, ',', '.'),
                        rtrim(rtrim(number_format($summary['teacher_percentage'], 2, ',', '.'), '0'), ',')
                    ));
                }
            } catch (\Exception $e) {
                $this->error("Error processing purchase {$purchase->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Update completed!");
        $this->line("✓ Updated: $updated");
        $this->line("✗ Errors: $errors");
    }
}
