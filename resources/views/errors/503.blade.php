<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ __('Service unavailable') }} · {{ config('app.name') }}</title>
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f9fafb;
                color: #111827;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            }

            .card {
                max-width: 32rem;
                margin: 0 1rem;
                padding: 2rem;
                background-color: #ffffff;
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                text-align: center;
            }

            h1 {
                font-size: 1.875rem;
                font-weight: 700;
                color: #111827;
            }

            p {
                margin-top: 1rem;
                color: #4b5563;
            }

            a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                margin-top: 1.5rem;
                padding: 0.5rem 1.25rem;
                border: 1px solid transparent;
                border-radius: 0.375rem;
                background-color: #4f46e5;
                color: #ffffff;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
            }

            a:hover {
                background-color: #4338ca;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>{{ __('Service unavailable') }}</h1>
            <p>{{ __('The service is temporarily unavailable. Please try again later.') }}</p>
            <a href="{{ url('/') }}">{{ __('Go home') }}</a>
        </div>
    </body>
</html>
