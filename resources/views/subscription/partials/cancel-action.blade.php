{{--
    Tombol "Batalkan Langganan" beserta indikator aturan minimal 1 bulan.

    Data $cancellation dikirim dari SubscriptionController@show lewat
    SubscriptionPlanService, jadi tampilan dan validasi backend memakai sumber
    aturan yang sama. Semua kelas tombol memakai style .btn-* yang sudah ada di
    subs.css agar tipografi dan palet warna Mersif Lab tidak berubah.
--}}
@php
    $cancelInfo = $cancellation ?? null;
    $canCancel = (bool) ($cancelInfo['allowed'] ?? false);
    $daysActive = (int) ($cancelInfo['days_active'] ?? 0);
    $daysRemaining = (int) ($cancelInfo['days_remaining'] ?? 0);
    $minimumDays = (int) ($cancelInfo['minimum_days'] ?? 30);
    $eligibleAt = $cancelInfo['eligible_at'] ?? null;
@endphp

<div class="cancel-subscription-action">
    @if($canCancel)
        <button type="button"
                class="btn-cancel-subscription"
                onclick="openCancelSubscriptionModal()">
            <i class="fas fa-times-circle"></i> Batalkan Langganan
        </button>
    @else
        {{-- Belum 1 bulan: tombol tetap terlihat tapi terkunci, dan alasannya
             dijelaskan lewat tooltip + teks di bawahnya agar transparan. --}}
        <button type="button"
                class="btn-cancel-subscription btn-cancel-locked"
                onclick="openCancelRuleModal()"
                title="{{ __('Langganan tidak dapat dibatalkan sebelum masa aktif mencapai 1 bulan.') }}">
            <i class="fas fa-lock"></i> Batalkan Langganan
        </button>
        <p class="cancel-rule-note">
            <i class="fas fa-info-circle"></i>
            Masa aktif baru berjalan {{ $daysActive }} dari {{ $minimumDays }} hari.
            @if($eligibleAt)
                Bisa dibatalkan mulai {{ $eligibleAt->format('d M Y') }}.
            @else
                Sisa {{ $daysRemaining }} hari lagi.
            @endif
        </p>
    @endif
</div>
