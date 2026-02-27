<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LearningContentController extends Controller
{
    public function indexView()
    {
        return view('dashboards.admin.settings.learningContentManager');
    }

    public function index()
    {
        $blocks = ContentBlock::query()
            ->with(['videos' => function ($query) {
                $query->orderBy('id');
            }])
            ->orderByDesc('default_block')
            ->orderBy('id')
            ->get();

        return response()->json([
            'blocks' => $blocks,
        ]);
    }

    public function storeBlock(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'default_block' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($validated['default_block'] ?? false);

        $block = DB::transaction(function () use ($validated, $isDefault) {
            if ($isDefault) {
                ContentBlock::query()->update(['default_block' => false]);
            }

            return ContentBlock::create([
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'default_block' => $isDefault,
            ]);
        });

        return response()->json([
            'message' => 'Content block created successfully.',
            'block' => $block,
        ], 201);
    }

    public function updateBlock(Request $request, ContentBlock $contentBlock)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'default_block' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($validated['default_block'] ?? false);

        $block = DB::transaction(function () use ($contentBlock, $validated, $isDefault) {
            if ($isDefault) {
                ContentBlock::query()
                    ->whereKeyNot($contentBlock->id)
                    ->update(['default_block' => false]);
            }

            $contentBlock->update([
                'title' => trim($validated['title']),
                'description' => $validated['description'] ?? null,
                'default_block' => $isDefault,
            ]);

            return $contentBlock->fresh();
        });

        return response()->json([
            'message' => 'Content block updated successfully.',
            'block' => $block,
        ]);
    }

    public function destroyBlock(ContentBlock $contentBlock)
    {
        $contentBlock->delete();

        return response()->json([
            'message' => 'Content block deleted successfully.',
        ]);
    }

    public function storeVideo(Request $request, ContentBlock $contentBlock)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'embedded_link' => ['required', 'url', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $video = $contentBlock->videos()->create([
            'title' => trim($validated['title']),
            'embedded_link' => trim($validated['embedded_link']),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Video created successfully.',
            'video' => $video,
        ], 201);
    }

    public function updateVideo(Request $request, ContentBlock $contentBlock, Video $video)
    {
        if ((int) $video->content_block_id !== (int) $contentBlock->id) {
            return response()->json([
                'message' => 'Video does not belong to this content block.',
            ], 422);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'embedded_link' => ['required', 'url', 'max:1000'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $video->update([
            'title' => trim($validated['title']),
            'embedded_link' => trim($validated['embedded_link']),
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'Video updated successfully.',
            'video' => $video->fresh(),
        ]);
    }

    public function destroyVideo(ContentBlock $contentBlock, Video $video)
    {
        if ((int) $video->content_block_id !== (int) $contentBlock->id) {
            return response()->json([
                'message' => 'Video does not belong to this content block.',
            ], 422);
        }

        $video->delete();

        return response()->json([
            'message' => 'Video deleted successfully.',
        ]);
    }
}
