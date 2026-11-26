<x-public-layout :title="__('Blog')">
<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg p-8 max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Blog</h2>
        <p class="text-gray-600 leading-relaxed">
            {{ __('Guides and notes from the verified real estate & construction world in Abuja.') }}
        </p>

        <div class="mt-6 grid gap-6">
            @foreach ($posts as $post)
                <article class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900">
                            <a href="{{ route('blog.show', $post['slug']) }}" class="hover:text-brand-600">{{ $post['title'] }}</a>
                        </h2>
                        <p class="mt-2 text-gray-700">{{ $post['excerpt'] }}</p>
                        <div class="mt-4 text-sm text-gray-500">
                            {{ __('Published') }} · {{ $post['published_at']->format('F j, Y') }}
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('blog.show', $post['slug']) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
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