<x-public-layout :title="$post['title']">
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <a href="{{ route('blog.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; {{ __('Back to blog') }}</a>

    <article class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $post['title'] }}</h1>
            <div class="mt-2 text-sm text-gray-500">
                {{ __('Published') }} · {{ $post['published_at']->format('F j, Y') }}
            </div>

            <p class="mt-4 text-gray-700">{{ $post['excerpt'] }}</p>

            <div class="mt-6 text-gray-700">
                {{ $post['content'] }}
            </div>
        </div>
    </article>
</div>
</x-public-layout>
