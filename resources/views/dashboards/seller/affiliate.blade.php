@extends('layouts.seller.app')

@section('content')
@php
  $referralCode = $seller?->referral_code;
  $referralLink = $seller?->referral_link;
  $hasReferralCredentials = filled($referralCode) && filled($referralLink);
  $stats = $affiliateStats ?? [
      'total_referrals' => 0,
      'active_referrals' => 0,
      'orders_from_referrals' => 0,
      'available_commission_lkr' => 0,
      'paid_commission_lkr' => 0,
      'total_commission_lkr' => 0,
  ];
  $referredList = $referredSellers ?? collect();
@endphp

<div class="space-y-6">
  @if($errors->has('affiliate'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      {{ $errors->first('affiliate') }}
    </div>
  @endif

  <div class="overflow-hidden rounded-3xl border border-emerald-200/70 bg-gradient-to-br from-emerald-100 via-white to-cyan-100 p-7 shadow-sm dark:border-emerald-500/30 dark:from-emerald-600/20 dark:via-slate-900 dark:to-cyan-600/20">
    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-emerald-700 dark:text-emerald-300">Seller Affiliate</p>
    <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Grow by Referring Other Sellers</h1>
    <p class="mt-3 max-w-3xl text-sm text-slate-700 dark:text-slate-200">
      Suggest this platform to other sellers and earn commissions whenever their orders are placed. Share your referral code, track onboarding progress, and monitor expected payouts.
    </p>

    <div class="mt-5 grid gap-3 lg:grid-cols-[1fr_auto]">
      <div class="rounded-2xl border border-emerald-200/70 bg-white/90 p-4 dark:border-emerald-500/30 dark:bg-slate-900/70">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Your Referral Code</p>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ $referralCode ?: 'Not generated yet' }}</p>
        @if($referralLink)
          <div class="mt-2 flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/80">
            <p id="sellerReferralLinkText" class="min-w-0 flex-1 truncate text-xs text-slate-700 dark:text-slate-200" title="{{ $referralLink }}">
              {{ $referralLink }}
            </p>
            <button
              id="sellerReferralLinkCopyBtn"
              type="button"
              class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-700 transition hover:bg-slate-100 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-700"
              title="Copy referral link"
              data-link="{{ $referralLink }}"
            >
              <i class="fas fa-copy text-xs"></i>
            </button>
          </div>
          <p id="sellerReferralCopyFeedback" class="mt-1 hidden text-xs font-semibold text-emerald-700 dark:text-emerald-300">Copied link</p>
        @else
          <p class="mt-1 break-all text-xs text-slate-600 dark:text-slate-300">
            Generate referral credentials to create your seller invite link.
          </p>
        @endif
      </div>

      <form method="POST" action="{{ route('sellerAffiliateGenerate') }}">
        @csrf
        <button
          type="submit"
          class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-400 bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 dark:border-emerald-400/60 dark:bg-emerald-500 dark:hover:bg-emerald-400"
        >
          <i class="fas {{ $hasReferralCredentials ? 'fa-arrows-rotate' : 'fa-link' }}"></i>
          {{ $hasReferralCredentials ? 'Regenerate Referral Link & Code' : 'Generate Referral Link & Code' }}
        </button>
      </form>
    </div>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
      <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Referrals</p>
      <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ $stats['total_referrals'] }}</p>
    </div>

    <div class="rounded-2xl border border-blue-200/80 bg-blue-50/80 p-4 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
      <p class="text-[11px] font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">Active Referrals</p>
      <p class="mt-2 text-3xl font-extrabold text-blue-900 dark:text-blue-100">{{ $stats['active_referrals'] }}</p>
    </div>

    <div class="rounded-2xl border border-violet-200/80 bg-violet-50/80 p-4 shadow-sm dark:border-violet-500/30 dark:bg-violet-500/10">
      <p class="text-[11px] font-semibold uppercase tracking-wider text-violet-700 dark:text-violet-300">Available Affiliate Commission</p>
      <p class="mt-2 text-3xl font-extrabold text-violet-900 dark:text-violet-100">LKR {{ number_format((float) ($stats['available_commission_lkr'] ?? 0), 2) }}</p>
    </div>

    <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
      <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Paid Affiliate Commission</p>
      <p class="mt-2 text-3xl font-extrabold text-amber-900 dark:text-amber-100">LKR {{ number_format((float) ($stats['paid_commission_lkr'] ?? 0), 2) }}</p>
      <p class="mt-1 text-[11px] text-amber-700/80 dark:text-amber-300/80">Total: LKR {{ number_format((float) ($stats['total_commission_lkr'] ?? 0), 2) }}</p>
    </div>
  </div>

  <div class="grid gap-4">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900/70">
      <div class="flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Referred Sellers</h2>
        <span class="rounded-full border border-slate-200 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-600 dark:border-slate-700 dark:text-slate-300">{{ number_format($referredList->count()) }} Linked</span>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
          <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
              <th class="px-3 py-2">Seller</th>
              <th class="px-3 py-2">Email</th>
              <th class="px-3 py-2">Phone</th>
              <th class="px-3 py-2">Joined</th>
              <th class="px-3 py-2">Status</th>
              <th class="px-3 py-2">Orders</th>
              <th class="px-3 py-2 text-right">Available</th>
              <th class="px-3 py-2 text-right">Paid</th>
              <th class="px-3 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse($referredList as $item)
              <tr class="text-slate-700 dark:text-slate-200">
                <td class="px-3 py-2.5 font-semibold">{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) ?: 'Seller #' . $item->id }}</td>
                <td class="px-3 py-2.5">{{ $item->email ?: '-' }}</td>
                <td class="px-3 py-2.5">{{ $item->phone ?: '-' }}</td>
                <td class="px-3 py-2.5">{{ optional($item->created_at)->format('Y-m-d') ?: '-' }}</td>
                <td class="px-3 py-2.5">
                  <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $item->status === 'approved' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200' : ($item->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200' : ($item->status === 'blocked' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200')) }}">
                    {{ ucfirst((string) $item->status) }}
                  </span>
                </td>
                <td class="px-3 py-2.5">{{ number_format((int) ($item->orders_count ?? 0)) }}</td>
                <td class="px-3 py-2.5 text-right font-semibold text-violet-700 dark:text-violet-300">LKR {{ number_format((float) ($item->affiliate_available_amount ?? 0), 2) }}</td>
                <td class="px-3 py-2.5 text-right font-semibold text-blue-700 dark:text-blue-300">LKR {{ number_format((float) ($item->affiliate_paid_amount ?? 0), 2) }}</td>
                <td class="px-3 py-2.5 text-right font-semibold">LKR {{ number_format((float) ($item->affiliate_total_amount ?? 0), 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="px-3 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                  No sellers are linked to your affiliate account yet.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    const copyBtn = document.getElementById('sellerReferralLinkCopyBtn');
    const feedback = document.getElementById('sellerReferralCopyFeedback');
    if (!copyBtn) return;

    const showFeedback = () => {
      if (!feedback) return;
      feedback.classList.remove('hidden');
      window.setTimeout(() => feedback.classList.add('hidden'), 1500);
    };

    copyBtn.addEventListener('click', async () => {
      const link = copyBtn.getAttribute('data-link') || '';
      if (!link) return;

      try {
        await navigator.clipboard.writeText(link);
        showFeedback();
      } catch (error) {
        const temp = document.createElement('textarea');
        temp.value = link;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        showFeedback();
      }
    });
  })();
</script>
@endsection
