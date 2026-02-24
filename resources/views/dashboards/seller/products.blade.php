@extends('layouts.seller.app')

@section('content')
@php
  $sellerLevelId = auth()->user()?->seller?->seller_level_id;
@endphp

<div class="space-y-6">
  <div class="rounded-3xl border border-slate-200/70 bg-white/80 p-7 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Seller Products</p>
    <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white">Products</h1>
    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
      Explore active products available in the catalog.
    </p>
  </div>

  @if ($products->count() === 0)
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
      No active products available right now.
    </div>
  @else
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
      @foreach ($products as $product)
        @php
          $primaryImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
          $prices = $product->varients->pluck('price')->filter(fn ($price) => !is_null($price));
          $minPrice = $prices->count() ? $prices->min() : null;
          $maxPrice = $prices->count() ? $prices->max() : null;
          $currentLevelCommission = $product->productLevels
            ->first(fn ($row) => (int) $row->level_id === (int) $sellerLevelId);

          $commissionLkr = null;
          if ($currentLevelCommission && !is_null($minPrice)) {
            if ($currentLevelCommission->type === 'percentage') {
              $commissionLkr = ((float) $minPrice * (float) $currentLevelCommission->value) / 100;
            } else {
              $commissionLkr = (float) $currentLevelCommission->value;
            }
          }
        @endphp

        <article
          class="group cursor-pointer overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
          onclick="window.location.href='{{ route('sellerProducts.show', $product) }}'"
        >
          <div class="relative h-52 w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
            @if ($primaryImage)
              <img src="{{ asset('storage/' . $primaryImage->path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
            @else
              <div class="flex h-full w-full items-center justify-center text-xs text-slate-400 dark:text-slate-500">No image</div>
            @endif

            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-900/90 dark:text-slate-200">
              {{ $product->category?->name ?? 'Uncategorized' }}
            </span>
          </div>

          <div class="space-y-3 p-4">
            <div>
              <h3 class="line-clamp-1 text-lg font-bold text-slate-900 dark:text-white">{{ $product->title }}</h3>
            </div>

            <p class="line-clamp-2 min-h-[2.8rem] text-sm text-slate-600 dark:text-slate-300">
              {{ \Illuminate\Support\Str::limit($product->small_description, 90) }}
            </p>

            <div class="rounded-xl bg-slate-50 px-3 py-2 text-sm dark:bg-slate-800/70">
              @if (is_null($minPrice))
                <span class="text-slate-500 dark:text-slate-400">Price unavailable</span>
              @elseif ($minPrice == $maxPrice)
                <span class="font-bold text-slate-900 dark:text-white">Selling Price: LKR {{ number_format((float) $minPrice, 2) }}</span>
              @else
                <span class="font-bold text-slate-900 dark:text-white">Selling Price: LKR {{ number_format((float) $minPrice, 2) }} - {{ number_format((float) $maxPrice, 2) }}</span>
              @endif
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs dark:border-blue-500/30 dark:bg-blue-500/10">
              @if (is_null($commissionLkr))
                <span class="font-semibold text-blue-700 dark:text-blue-300">Your current commission: Not configured</span>
              @else
                <span class="font-semibold text-blue-700 dark:text-blue-300">
                  Your current commission: LKR {{ number_format((float) $commissionLkr, 2) }}
                </span>
              @endif
            </div>
          </div>
        </article>
      @endforeach
    </div>

    <div class="pt-2">
      {{ $products->links() }}
    </div>
  @endif
</div>
@endsection
