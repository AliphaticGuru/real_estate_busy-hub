<x-public-layout :title="$post['title']">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <a href="{{ route('blog.index') }}" class="text-sm text-text-muted hover:text-text">&larr; {{ __('Back to blog') }}</a>

    <article class="mt-6 bg-surface overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-text">{{ $post['title'] }}</h1>
            <div class="mt-2 text-sm text-text-muted">
                {{ __('Published') }} · {{ $post['published_at']->format('F j, Y') }}
            </div>

            <p class="mt-4 text-text">{{ $post['excerpt'] }}</p>

            <div class="mt-6 text-text">
                {{ $post['content'] }}
            </div>
        </div>
    </article>
</div>
</x-public-layout>
