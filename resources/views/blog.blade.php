<x-public-layout :title="__('Blog')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-surface shadow rounded-lg p-8 max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold text-text mb-6">Blog</h2>
        <p class="text-text-muted leading-relaxed">
            {{ __('Guides and notes from the verified real estate & construction world in Abuja.') }}
        </p>

        <div class="mt-6 grid gap-6">
            @foreach ($posts as $post)
                <article class="bg-surface overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-text">
                            <a href="{{ route('blog.show', $post['slug']) }}" class="hover:text-brand">{{ $post['title'] }}</a>
                        </h2>
                        <p class="mt-2 text-text">{{ $post['excerpt'] }}</p>
                        <div class="mt-4 text-sm text-text-muted">
                            {{ __('Published') }} · {{ $post['published_at']->format('F j, Y') }}
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('blog.show', $post['slug']) }}" class="inline-flex items-center px-4 py-2 bg-brand-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-800 focus:bg-brand-800 active:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Read more') }}
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</div>
</x-public-layout>