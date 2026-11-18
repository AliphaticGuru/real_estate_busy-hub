<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    /**
     * Public blog index with sample posts.
     *
     * @return array<int, array{slug: string, title: string, excerpt: string, content: string, published_at: Carbon}>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'grand-villa-gwarinpa-buying-guide',
                'title' => 'Buying a Grand Villa in Gwarinpa: What to Check',
                'excerpt' => 'A practical checklist for buyers looking at grand villas in Gwarinpa.',
                'content' => 'When buying a grand villa in Gwarinpa, verify land documentation, title documents, and site inspection before closing.',
                'published_at' => Carbon::parse('2026-11-10 09:00:00'),
            ],
            [
                'slug' => 'abuja-construction-costs-2026',
                'title' => 'Abuja Construction Costs in 2026',
                'excerpt' => 'How to budget construction costs for apartments and townhouses in Abuja.',
                'content' => 'Construction budgeting for apartments and townhouses in Abuja depends on scope, materials, and site conditions.',
                'published_at' => Carbon::parse('2026-11-02 09:00:00'),
            ],
        ];
    }

    public function index(): View
    {
        return view('blog', ['posts' => $this->posts()]);
    }

    public function show(string $slug): View
    {
        $post = collect($this->posts())->first(fn (array $post) => $post['slug'] === $slug);

        if ($post === null) {
            abort(404);
        }

        return view('blog.show', ['post' => $post]);
    }
}
