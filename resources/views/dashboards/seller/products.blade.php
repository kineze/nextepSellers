@extends('layouts.seller.app')

@section('content')
@php
  $sellerLevelId = auth()->user()?->seller?->seller_level_id;
@endphp

<div class="space-y-6">
  @if(($categories ?? collect())->count() > 0)
    <div class="rounded-2xl border border-zinc-200/70 bg-white/90 p-3 shadow-sm dark:border-zinc-800/70 dark:bg-slate-900/80">
      <div class="flex flex-wrap items-center gap-2">
        <a
          href="{{ route('sellerProducts') }}"
          class="rounded-xl border px-3 py-2 text-xs font-semibold uppercase tracking-wide transition {{ empty($selectedCategoryId) ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-zinc-300 text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800' }}"
        >
          All
        </a>

        @foreach(($categories ?? collect()) as $category)
          <a
            href="{{ route('sellerProducts', ['category_id' => $category->id]) }}"
            class="rounded-xl border px-3 py-2 text-xs font-semibold uppercase tracking-wide transition {{ (int) ($selectedCategoryId ?? 0) === (int) $category->id ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-zinc-300 text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800' }}"
          >
            {{ $category->name }}
          </a>
        @endforeach
      </div>
    </div>
  @endif

  @if ($products->count() === 0)
    <div class="rounded-2xl border border-dashed border-slate-300 bg-white/70 p-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-400">
      {{ !empty($selectedCategoryId) ? 'No active products found in this category.' : 'No active products available right now.' }}
    </div>
  @else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
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
          class="group flex h-full flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-md dark:border-zinc-800 dark:bg-slate-900 dark:hover:border-zinc-700"
        >
          <div class="relative aspect-square w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
            @if ($primaryImage)
              <img src="{{ asset('storage/' . $primaryImage->path) }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" />
            @else
              <div class="flex h-full w-full items-center justify-center text-xs text-slate-400 dark:text-slate-500">No image</div>
            @endif

            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-900/90 dark:text-slate-200">
              {{ $product->category?->name ?? 'Uncategorized' }}
            </span>
          </div>

          <div class="flex flex-1 flex-col space-y-3 p-4">
            <div>
              <h3 class="line-clamp-2 min-h-10 text-sm font-bold leading-5 text-slate-900 dark:text-white">{{ $product->title }}</h3>
            </div>

            <p class="line-clamp-2 min-h-9 text-xs leading-4 text-slate-600 dark:text-slate-300">
              {{ \Illuminate\Support\Str::limit($product->small_description, 70) }}
            </p>

            <div class="rounded-xl bg-zinc-50 px-3 py-2 text-xs dark:bg-zinc-800/70">
              @if (is_null($minPrice))
                <span class="text-slate-500 dark:text-slate-400">Price unavailable</span>
              @elseif ($minPrice == $maxPrice)
                <span class="font-bold text-slate-900 dark:text-white">Selling Price: LKR {{ number_format((float) $minPrice, 2) }}</span>
              @else
                <span class="font-bold text-slate-900 dark:text-white">Selling Price: LKR {{ number_format((float) $minPrice, 2) }} - {{ number_format((float) $maxPrice, 2) }}</span>
              @endif
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs dark:border-zinc-700 dark:bg-slate-950/60">
              @if (is_null($commissionLkr))
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">Commission: Not configured</span>
              @else
                <span class="font-semibold text-zinc-700 dark:text-zinc-200">
                  Commission: LKR {{ number_format((float) $commissionLkr, 2) }}
                </span>
              @endif
            </div>

            <div class="mt-auto pt-1">
              <a
                href="{{ route('sellerProducts.show', $product) }}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-black px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200"
              >
                View
                <i class="fas fa-arrow-right text-[0.68rem]"></i>
              </a>
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
