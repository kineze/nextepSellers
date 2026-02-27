@extends('layouts.site.app')

@section('content')
<section class="py-12">
  <div class="mb-8">
    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.2em] text-fuchsia-600 dark:text-fuchsia-300">Learning</p>
    <h1 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">Learning Materials</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">Explore all content blocks and training videos.</p>
  </div>

  @if($learningBlocks->isEmpty())
    <div class="rounded-2xl border border-dashed border-slate-300 px-4 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
      No learning materials available yet.
    </div>
  @else
    <div class="space-y-6">
      @foreach($learningBlocks as $block)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $block->title }}</h2>
            @if($block->default_block)
              <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Default</span>
            @endif
          </div>
          <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $block->description ?: 'No description' }}</p>

          <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($block->videos as $video)
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-900">
                  <iframe
                    src="{{ $video->embed_url ?: $video->embedded_link }}"
                    class="h-44 w-full"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    allowfullscreen
                  ></iframe>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $video->title }}</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">{{ $video->description ?: 'No description' }}</p>
              </div>
            @empty
              <p class="text-sm text-slate-500 dark:text-slate-400">No videos in this block.</p>
            @endforelse
          </div>
        </article>
      @endforeach
    </div>
  @endif
</section>
@endsection
