<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GenaralController extends Controller
{
    public function index(){
        return view('site.home', $this->homeLearningData());
    }

    public function about(){


        return view('site.about');
    }

    public function contact(){


        return view('site.contact');
    }

    public function sellerRegistration(){
        return view('site.seller-registration');
    }

    public function learnMore(){
        return view('site.learn-more');
    }

    public function home(){

        if (auth()->check()) {
                $user = auth()->user();

                if ($user->hasRole('Marketer')) {

                    if ($user && $user->marketer->is_blocked) {
                        Auth::guard('web')->logout();
                            return redirect()->route('blocked');
                    }
                    return redirect()->route('marketerDashboard');
                } elseif ($user->hasRole('Admin')) {
                    return redirect()->route('adminDashboard');
                } elseif ($user->hasRole('Seller')) {
                    return redirect()->route('sellerDashboard');
                } else {
                    return redirect()->route('setDashboard');
                }
        } else {
            return view('site.home', $this->homeLearningData());
        }
    }

    public function learningMaterials()
    {
        $blocks = ContentBlock::query()
            ->with(['videos' => function ($query) {
                $query->orderBy('id');
            }])
            ->orderByDesc('default_block')
            ->orderBy('id')
            ->get()
            ->map(function (ContentBlock $block) {
                $block->videos->transform(function ($video) {
                    $video->embed_url = $this->toEmbedUrl((string) ($video->embedded_link ?? ''));
                    return $video;
                });
                return $block;
            });

        return view('site.learning-materials', [
            'learningBlocks' => $blocks,
        ]);
    }


    public function setDashboard(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('adminDashboard');
        }

        if ($user->hasRole('Seller')) {
            return redirect()->route('sellerDashboard');
        }

        return redirect('/');
    }

    private function homeLearningData(): array
    {
        $defaultBlock = ContentBlock::query()
            ->with(['videos' => function ($query) {
                $query->orderBy('id');
            }])
            ->where('default_block', true)
            ->first();

        if (!$defaultBlock) {
            $defaultBlock = ContentBlock::query()
                ->with(['videos' => function ($query) {
                    $query->orderBy('id');
                }])
                ->orderBy('id')
                ->first();
        }

        if ($defaultBlock) {
            $defaultBlock->videos->transform(function ($video) {
                $video->embed_url = $this->toEmbedUrl((string) ($video->embedded_link ?? ''));
                return $video;
            });
        }

        return [
            'defaultLearningBlock' => $defaultBlock,
        ];
    }

    private function toEmbedUrl(string $url): string
    {
        $raw = trim($url);
        if ($raw === '') {
            return '';
        }

        try {
            $parts = parse_url($raw);
            $host = isset($parts['host']) ? preg_replace('/^www\./', '', strtolower((string) $parts['host'])) : '';
            $path = (string) ($parts['path'] ?? '');
            $query = (string) ($parts['query'] ?? '');

            if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be'], true)) {
                if ($host === 'youtu.be') {
                    $id = trim($path, '/');
                    return $id !== '' ? "https://www.youtube.com/embed/{$id}" : $raw;
                }

                if ($path === '/watch') {
                    parse_str($query, $queryParams);
                    $id = $queryParams['v'] ?? null;
                    return $id ? "https://www.youtube.com/embed/{$id}" : $raw;
                }

                if (str_starts_with($path, '/embed/')) {
                    return $raw;
                }
            }

            if ($host === 'vimeo.com') {
                $segments = array_values(array_filter(explode('/', $path)));
                $id = $segments[0] ?? null;
                return $id ? "https://player.vimeo.com/video/{$id}" : $raw;
            }

            return $raw;
        } catch (\Throwable $e) {
            return $raw;
        }
    }
}
