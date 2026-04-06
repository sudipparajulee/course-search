<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'StudyAide') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary:   #1a3a5c;
            --color-secondary: #2ca5b8;
            --color-tertiary:  #1e6fa0;
        }
        body { font-family: 'Poppins', sans-serif; }
    </style>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
</head>
<body class="flex min-h-screen flex-col bg-[#f8fafb] text-slate-900 antialiased">
    @include('layouts.toaster')

    {{-- ===== HEADER ===== --}}
    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/95 backdrop-blur-sm shadow-sm">
        <div class="mx-auto flex w-full max-w-[1600px] flex-wrap items-center justify-between gap-4 px-4 py-3 lg:px-8">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="flex h-20 shrink-0 items-center overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="StudyAide logo" class="h-full w-auto object-contain" />
                </a>
            </div>

            {{-- Desktop Nav --}}
            <nav class="hidden xl:flex flex-1 justify-center">
                <div class="flex flex-wrap items-center justify-center gap-1 text-[0.88rem] font-medium text-slate-600">
                    @foreach([
                        ['Applying','#'],['After you apply','#'],
                        ['For Institutions','#'],['Media','#'],
                    ] as [$label, $href])
                    <a href="{{ $href }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-2 transition hover:bg-slate-100 hover:text-[#1a3a5c] whitespace-nowrap">
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </div>
            </nav>

            {{-- Desktop Actions --}}
            <div class="hidden xl:flex items-center gap-3">
                <a href="{{ url('/course-search/search/find-a-course-international') }}"
                   class="inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-[0.88rem] font-semibold text-[#1a3a5c] transition hover:bg-slate-100 whitespace-nowrap">
                    <svg class="h-4 w-4 text-[#2ca5b8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="6.75"/><path d="m16 16 5 5"/>
                    </svg>
                    All courses
                </a>

                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : url('/') }}" class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white p-1 pr-4 shadow-sm transition hover:shadow-md">
                        @if(auth()->user()->photo)
                            <img src="{{ Storage::url(auth()->user()->photo) }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1a3a5c] text-xs font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-[13px] font-bold text-[#1a3a5c]">Hi, {{ explode(' ', auth()->user()->name ?? 'User')[0] }}</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-500" title="Log out">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 whitespace-nowrap rounded-full bg-[#1a3a5c] px-5 py-2.5 text-[0.88rem] font-semibold text-white shadow-sm transition hover:bg-[#16304d]">
                        <span>Apply / log in</span>
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <div class="flex items-center xl:hidden xl:flex-none">
                <button type="button" id="mobile-menu-btn" class="-m-2.5 p-2.5 text-slate-700 transition hover:text-[#1a3a5c]">
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile Sidebar Overlay (Moved outside header for stacking context) --}}
    <div id="mobile-sidebar" class="fixed inset-0 z-[100] hidden" aria-modal="true" role="dialog">
        {{-- Backdrop --}}
        <div id="mobile-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-300 ease-in-out"></div>

        {{-- Sidebar Panel --}}
        <div id="mobile-panel" class="fixed inset-y-0 right-0 z-[110] w-full max-w-sm transform translate-x-full overflow-y-auto bg-white px-6 py-6 transition-transform duration-300 ease-in-out shadow-2xl">
            <div class="flex items-center justify-between">
                <a href="/" class="-m-1.5 p-1.5 flex h-9 shrink-0 items-center overflow-hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="StudyAide logo" class="h-full w-auto object-contain" />
                </a>
                <button type="button" id="close-menu-btn" class="-m-2.5 p-2.5 text-slate-700 transition hover:text-red-500">
                    <span class="sr-only">Close menu</span>
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-8 flow-root">
                <div class="-my-6 divide-y divide-slate-100">
                    <div class="space-y-2 py-6">
                        @foreach([
                            ['Planning','#'],['Applying','#'],['After you apply','#'],
                            ['For Institutions','#'],['Enterprise','#'],['Media','#'],
                        ] as [$label, $href])
                        <a href="{{ $href }}" class="-mx-3 block rounded-xl px-3 py-3 text-base font-semibold leading-7 text-slate-800 transition hover:bg-slate-50 hover:text-[#2ca5b8]">
                            {{ $label }}
                        </a>
                        @endforeach
                    </div>
                    <div class="py-6 space-y-4">
                        <a href="{{ url('/course-search/search/find-a-course-international') }}"
                           class="-mx-3 flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-base font-semibold border-2 border-[#1a3a5c] text-[#1a3a5c] transition hover:bg-[#1a3a5c] hover:text-white group">
                            <svg class="h-5 w-5 text-[#2ca5b8] group-hover:text-white transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="6.75"/><path d="m16 16 5 5"/>
                            </svg>
                            All courses
                        </a>

                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : url('/') }}" class="-mx-3 flex items-center gap-3 rounded-xl px-3 py-3 text-base font-semibold leading-7 text-slate-800 transition hover:bg-slate-50">
                                @if(auth()->user()->photo)
                                    <img src="{{ Storage::url(auth()->user()->photo) }}" alt="Avatar" class="h-10 w-10 rounded-full object-cover shadow-sm">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#1a3a5c] text-sm font-bold text-white shadow-sm">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <span>Hi, {{ explode(' ', auth()->user()->name ?? 'User')[0] }}</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="-mx-3">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-base font-semibold leading-7 text-red-600 transition hover:bg-red-50">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log out
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                               class="-mx-3 flex items-center justify-center gap-2 rounded-xl bg-[#1a3a5c] px-3 py-3 text-base font-semibold text-white shadow-md transition hover:bg-[#2ca5b8] active:scale-[0.98]">
                                Apply / log in
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-menu-btn');
            const sidebar = document.getElementById('mobile-sidebar');
            const backdrop = document.getElementById('mobile-backdrop');
            const panel = document.getElementById('mobile-panel');

            function openMenu() {
                sidebar.classList.remove('hidden');
                // Trigger reflow
                void sidebar.offsetWidth;
                backdrop.classList.remove('opacity-0');
                backdrop.classList.add('opacity-100');
                panel.classList.remove('translate-x-full');
                panel.classList.add('translate-x-0');
                document.body.style.overflow = 'hidden';
            }

            function closeMenu() {
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                panel.classList.remove('translate-x-0');
                panel.classList.add('translate-x-full');

                setTimeout(() => {
                    sidebar.classList.add('hidden');
                    document.body.style.overflow = '';
                }, 300);
            }

            if(openBtn) openBtn.addEventListener('click', openMenu);
            if(closeBtn) closeBtn.addEventListener('click', closeMenu);
            if(backdrop) backdrop.addEventListener('click', closeMenu);
        });
    </script>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-slate-200 bg-white py-10">
        <div class="mx-auto max-w-7xl px-4 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-6">
                    <a href="/" class="h-8 shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="StudyAide" class="h-full w-auto opacity-80 transition hover:opacity-100" />
                    </a>
                    <nav class="hidden gap-5 text-sm font-medium text-slate-500 md:flex">
                        <a href="#" class="transition hover:text-[#2ca5b8]">About</a>
                        <a href="#" class="transition hover:text-[#2ca5b8]">Privacy</a>
                        <a href="#" class="transition hover:text-[#2ca5b8]">Terms</a>
                        <a href="#" class="transition hover:text-[#2ca5b8]">Contact</a>
                    </nav>
                </div>
                <div class="flex items-center gap-5">
                    <a href="#" aria-label="Twitter/X" class="text-slate-400 transition hover:text-[#1a3a5c]">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622Z"/></svg>
                    </a>
                    <a href="#" aria-label="LinkedIn" class="text-slate-400 transition hover:text-[#1a3a5c]">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.761 0 5-2.239 5-5v-14c0-2.761-2.239-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                </div>
            </div>
            <div class="mt-8 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
                &copy; {{ now()->year }} StudyAide. Empowering student futures.
            </div>
        </div>
    </footer>

</body>
</html>

