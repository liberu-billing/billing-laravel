<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BillingOS') }} — Professional Billing &amp; Invoicing</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    {{-- Navigation --}}
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">{{ config('app.name', 'BillingOS') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/app') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Dashboard
                        </a>
                        <a href="{{ url('/admin') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Admin Panel
                        </a>
                    @else
                        <a href="{{ url('/app/login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Sign In
                        </a>
                        <a href="{{ url('/app/login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Get Started
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-white pt-20 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold uppercase tracking-wide mb-6">
                Professional Billing Platform
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight">
                Invoicing &amp; Billing<br>
                <span class="text-indigo-600">Made Simple</span>
            </h1>
            <p class="mt-6 text-lg text-gray-500 max-w-2xl mx-auto">
                Manage clients, automate invoices, track payments, and grow your business — all from one powerful platform.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/app/login') }}" class="inline-flex items-center justify-center px-8 py-3 bg-indigo-600 text-white text-base font-semibold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/25 transition-all">
                    Start Billing Free
                </a>
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-8 py-3 bg-white border-2 border-gray-200 text-gray-700 text-base font-semibold rounded-xl hover:border-indigo-300 hover:text-indigo-600 transition-all">
                    Admin Dashboard
                </a>
            </div>
        </div>
    </section>

    {{-- Features Grid --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900">Everything you need to get paid</h2>
                <p class="mt-4 text-gray-500">A complete toolkit for freelancers, agencies, and SaaS businesses.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">

                @php
                $features = [
                    ['icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z', 'title' => 'Smart Invoicing', 'desc' => 'Create professional invoices in seconds with automatic calculations, taxes, and PDF export.'],
                    ['icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z', 'title' => 'Payment Gateways', 'desc' => 'Accept payments from anywhere with support for multiple gateways and automatic reconciliation.'],
                    ['icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'title' => 'Client Portal', 'desc' => 'Give clients a branded self-service portal to view invoices, make payments, and manage services.'],
                    ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'title' => 'Revenue Analytics', 'desc' => 'Deep insights into revenue trends, outstanding invoices, and client lifetime value.'],
                    ['icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99', 'title' => 'Recurring Billing', 'desc' => 'Set up subscriptions and recurring invoices with automatic retries and dunning management.'],
                    ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'title' => 'Role-Based Access', 'desc' => 'Granular permissions for your team — admins, billing staff, and read-only users.'],
                ];
                @endphp

                @foreach($features as $feature)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-indigo-50 rounded-xl mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-indigo-600">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-white">Ready to streamline your billing?</h2>
            <p class="mt-4 text-indigo-200 text-lg">Access the admin panel to manage clients, invoices, and settings.</p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center px-8 py-3 bg-white text-indigo-600 text-base font-semibold rounded-xl hover:bg-indigo-50 transition-colors shadow-lg">
                    Open Admin Panel
                </a>
                <a href="{{ url('/app/login') }}" class="inline-flex items-center justify-center px-8 py-3 border-2 border-indigo-400 text-white text-base font-semibold rounded-xl hover:bg-indigo-500 transition-colors">
                    User Dashboard
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-gray-500 text-sm">
                <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                </svg>
                {{ config('app.name', 'BillingOS') }} &mdash; Built on Laravel {{ app()->version() }}
            </div>
            <div class="text-gray-400 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name', 'BillingOS') }}. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
