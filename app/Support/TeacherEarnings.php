<?php

namespace App\Support;

use App\Models\CommissionSetting;
use App\Models\Purchase;
use App\Models\TeacherWithdrawal;

/**
 * Sumber tunggal perhitungan pendapatan bersih guru.
 *
 * Sebelumnya guru dikreditkan nominal BRUTO transaksi. Penyebabnya ada di
 * App\Models\Purchase::updateTeacherBalance():
 *
 *     $earnings = $purchase->teacher_earning ?? $purchase->amount;
 *
 * Kolom teacher_earning tidak pernah diisi oleh alur pembelian mana pun, jadi
 * nilainya selalu NULL dan perhitungan selalu jatuh ke $purchase->amount -
 * harga penuh. Model CommissionSetting beserta calculateCommission() sudah ada
 * dan berfungsi, tetapi tidak pernah dipanggil.
 *
 * Kelas ini memusatkan rumusnya supaya kartu ringkasan, validasi penarikan,
 * dan perintah penyelarasan saldo memakai angka yang sama persis.
 *
 * Catatan tentang komisi kustom: tabel commission_settings hanya punya kolom
 * teacher_id (boleh NULL untuk pengaturan global) - tidak ada kolom per course.
 * Jadi "komisi kustom" di aplikasi ini berarti per guru, dan urutan prioritas
 * yang dipakai CommissionSetting::getForTeacher() adalah:
 *
 *     pengaturan khusus guru -> pengaturan global -> bawaan 80% guru / 20% platform
 */
class TeacherEarnings
{
    /**
     * Status penarikan yang benar-benar mengurangi saldo guru.
     *
     * 'pending' TIDAK termasuk: pengajuan yang belum diproses belum memotong
     * apa pun, sama seperti perilaku yang sudah berjalan selama ini.
     */
    public const SETTLED_WITHDRAWAL_STATUSES = ['approved', 'processed'];

    /**
     * Bagian bersih guru dari satu transaksi.
     *
     * Nilai teacher_earning yang sudah tersimpan diutamakan supaya transaksi
     * lama tetap memakai tarif yang berlaku saat itu, dan penyesuaian manual
     * oleh admin tidak tertimpa perhitungan ulang.
     */
    public static function netForPurchase(Purchase $purchase, ?CommissionSetting $setting = null): float
    {
        if ($purchase->teacher_earning !== null) {
            return (float) $purchase->teacher_earning;
        }

        $setting ??= CommissionSetting::getForTeacher($purchase->course->teacher_id ?? null);

        return (float) $purchase->amount * ((float) $setting->teacher_percentage / 100);
    }

    /**
     * Ringkasan keuangan seorang guru, dihitung dari transaksi yang lunas.
     *
     * @return array{
     *     gross: float,
     *     net: float,
     *     platform_commission: float,
     *     withdrawn: float,
     *     available: float,
     *     teacher_percentage: float,
     *     platform_percentage: float
     * }
     */
    public static function summaryFor(int $teacherId): array
    {
        $setting = CommissionSetting::getForTeacher($teacherId);

        // Hanya status 'success' yang berarti lunas - status itu pula yang
        // memicu penambahan saldo di App\Models\Purchase.
        $purchases = Purchase::whereHas('course', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId);
        })
        ->where('status', 'success')
        ->with('course')
        ->get();

        $gross = (float) $purchases->sum('amount');

        $net = $purchases->reduce(
            fn (float $total, Purchase $purchase) => $total + self::netForPurchase($purchase, $setting),
            0.0
        );

        $withdrawn = (float) TeacherWithdrawal::where('teacher_id', $teacherId)
            ->whereIn('status', self::SETTLED_WITHDRAWAL_STATUSES)
            ->sum('amount');

        return [
            'gross' => $gross,
            'net' => $net,
            'platform_commission' => $gross - $net,
            'withdrawn' => $withdrawn,

            // Tidak boleh negatif: penarikan yang terlanjur disetujui melebihi
            // pendapatan bersih akan menampilkan saldo minus yang membingungkan
            // dan membuat validasi penarikan berperilaku aneh.
            'available' => max(0, $net - $withdrawn),

            'teacher_percentage' => (float) $setting->teacher_percentage,
            'platform_percentage' => (float) $setting->platform_percentage,
        ];
    }
}
