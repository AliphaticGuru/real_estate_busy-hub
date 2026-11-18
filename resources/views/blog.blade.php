<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('Blog') }} · {{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400;600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-gray-50 text-gray-900">
        <div class="min-h-screen">
            <header class="bg-white border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <a href="{{ route('listings.index') }}" class="text-lg font-semibold text-gray-900">
                        {{ config('app.name') }}
                    </a>
                    <nav class="flex items-center gap-4 text-sm">
                        <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Listings') }}</a>
                        <a href="{{ route('fees') }}" class="text-gray-600 hover:text-gray-900">{{ __('Fees') }}</a>
                        <a href="{{ route('verification') }}" class="text-gray-600 hover:text-gray-900">{{ __('Verification') }}</a>
                        <a href="{{ route('blog.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Blog') }}</a>
                    </nav>
                </div>
            </header>

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('Blog') }}</h1>
                    <p class="mt-2 text-gray-600">{{ __('Guides and notes from the verified real estate & construction world in Abuja.') }}</p>

                    <div class="mt-6 grid gap-6">
                        @foreach ($posts as $post)
                            <article class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('blog.show', $post['slug']) }}" class="hover:text-indigo-600">{{ $post['title'] }}</a>
                                    </h2>
                                    <p class="mt-2 text-gray-700">{{ $post['excerpt'] }}</p>
                                    <div class="mt-4 text-sm text-gray-500">
                                        {{ __('Published') }} · {{ $post['published_at']->format('F j, Y') }}
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('blog.show', $post['slug']) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Read more') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </main>

            <footer class="py-16 text-center text-sm text-gray-500">
                {{ config('app.name') }} · {{ __('Find verified real estate & construction partners in Abuja') }}
            </footer>
        </div>
    </body>
</html>
