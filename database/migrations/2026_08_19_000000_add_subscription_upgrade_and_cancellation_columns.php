<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dukungan untuk upgrade paket (Standard -> Premium) dan aturan pembatalan
 * minimal 1 bulan.
 *
 * Perubahan penting:
 *  - Kolom subscription_purchases.status semula ENUM('pending','success',
 *    'expired','cancelled'). Nilai baru 'upgraded' (untuk menandai paket lama
 *    yang digantikan) DITOLAK oleh constraint ENUM tersebut. Kolomnya diubah
 *    menjadi VARCHAR agar status baru bisa dipakai tanpa harus mengubah skema
 *    lagi setiap kali ada status tambahan. Nilai lama tetap valid apa adanya.
 *  - started_at / cancelled_at / replaced_by_id untuk melacak siklus hidup
 *    langganan (kapan mulai, kapan dibatalkan, digantikan oleh purchase mana).
 *  - users.subscription_started_at dipakai sebagai acuan resmi umur langganan
 *    saat memeriksa aturan "tidak bisa dibatalkan sebelum 1 bulan".
 *
 * Ditulis idempoten supaya aman dijalankan ulang pada database yang sudah
 * sebagian ter-update.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_purchases')) {
            // ENUM -> VARCHAR. Memakai change() bawaan Laravel agar portabel
            // antar driver (MySQL, SQLite, PostgreSQL), bukan raw SQL MySQL.
            try {
                Schema::table('subscription_purchases', function (Blueprint $table) {
                    $table->string('status', 20)->default('pending')->change();
                });
            } catch (\Throwable $e) {
                // Kalau driver tidak mendukung perubahan tipe, biarkan kolom
                // apa adanya - kode aplikasi tetap berjalan untuk status lama.
            }

            Schema::table('subscription_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('subscription_purchases', 'started_at')) {
                    $table->timestamp('started_at')->nullable()->after('paid_at')
                        ->comment('Waktu langganan mulai aktif, acuan aturan pembatalan 1 bulan');
                }

                if (!Schema::hasColumn('subscription_purchases', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('expires_at')
                        ->comment('Waktu langganan dibatalkan pengguna');
                }

                if (!Schema::hasColumn('subscription_purchases', 'replaced_by_id')) {
                    $table->unsignedBigInteger('replaced_by_id')->nullable()->after('cancelled_at')
                        ->comment('ID purchase pengganti saat paket ini di-upgrade');
                }

                if (!Schema::hasColumn('subscription_purchases', 'is_upgrade')) {
                    $table->boolean('is_upgrade')->default(false)->after('replaced_by_id')
                        ->comment('Menandai purchase ini dibuat lewat alur upgrade paket');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'subscription_started_at')) {
                    $table->timestamp('subscription_started_at')->nullable()->after('subscription_plan')
                        ->comment('Tanggal mulai langganan aktif saat ini');
                }

                if (!Schema::hasColumn('users', 'subscription_cancelled_at')) {
                    $table->timestamp('subscription_cancelled_at')->nullable()->after('subscription_started_at')
                        ->comment('Tanggal pembatalan langganan terakhir');
                }
            });
        }

        $this->backfillStartDates();
    }

    public function down(): void
    {
        if (Schema::hasTable('subscription_purchases')) {
            Schema::table('subscription_purchases', function (Blueprint $table) {
                foreach (['started_at', 'cancelled_at', 'replaced_by_id', 'is_upgrade'] as $column) {
                    if (Schema::hasColumn('subscription_purchases', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['subscription_started_at', 'subscription_cancelled_at'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Kolom status sengaja dibiarkan VARCHAR: mengembalikannya ke ENUM
        // akan menggagalkan baris yang sudah memakai status 'upgraded'.
    }

    /**
     * Isi tanggal mulai untuk langganan yang sudah terlanjur aktif sebelum
     * migrasi ini, supaya aturan 1 bulan punya acuan yang benar dan tidak
     * memblokir pembatalan selamanya karena tanggalnya null.
     */
    private function backfillStartDates(): void
    {
        try {
            if (Schema::hasColumn('subscription_purchases', 'started_at')) {
                \Illuminate\Support\Facades\DB::table('subscription_purchases')
                    ->whereNull('started_at')
                    ->where('status', 'success')
                    ->update([
                        'started_at' => \Illuminate\Support\Facades\DB::raw('COALESCE(paid_at, created_at)'),
                    ]);
            }

            if (Schema::hasColumn('users', 'subscription_started_at')) {
                // Ambil tanggal bayar purchase sukses terakhir tiap user.
                $latest = \Illuminate\Support\Facades\DB::table('subscription_purchases')
                    ->select('user_id', \Illuminate\Support\Facades\DB::raw('MAX(COALESCE(paid_at, created_at)) as started'))
                    ->where('status', 'success')
                    ->groupBy('user_id')
                    ->get();

                foreach ($latest as $row) {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $row->user_id)
                        ->whereNull('subscription_started_at')
                        ->update(['subscription_started_at' => $row->started]);
                }
            }
        } catch (\Throwable $e) {
            // Backfill bersifat pelengkap. Kalau gagal, SubscriptionPlanService
            // masih punya fallback ke paid_at / created_at purchase.
        }
    }
};
