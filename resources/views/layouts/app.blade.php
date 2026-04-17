<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin') — StudyAide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bulma.min.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
   <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bulma.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Override DataTables Bulma negative margin overflow */
        .dataTables_wrapper .columns { margin-left: 0 !important; margin-right: 0 !important; }
        .dataTables_wrapper .column { padding-left: 0; padding-right: 0; }
        @media (min-width: 769px) { .dataTables_wrapper .column { padding-left: 0.75rem; padding-right: 0.75rem; } }
    </style>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
</head>

<body class="min-h-screen bg-[#f0f6fb] text-slate-800">

    <div class="flex min-h-screen">

        {{-- Mobile Overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 backdrop-blur-sm transition-opacity lg:hidden"></div>

        {{-- Sidebar --}}
        <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-[#0d2640] shadow-2xl transition-transform duration-300 ease-in-out lg:translate-x-0">
            {{-- Logo --}}
            <div class="flex items-center gap-3 border-b border-white/10 bg-white/5 px-6 py-4">
                <div class="rounded bg-white p-1.5 shadow-sm">
                    <img src="{{ asset('images/logo.png') }}" alt="StudyAide logo" class="h-6 w-auto" />
                </div>
                <div>
                    <div class="text-[10px] font-bold tracking-widest text-[#2ca5b8] uppercase">Admin Panel</div>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
                @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => '
                    <path
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    '],
                    ['route' => 'admin.applications.list', 'label' => 'Applications', 'icon' => '
                    <path
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    '],
                    ['route' => 'admin.users', 'label' => 'Users', 'icon' => '
                    <path
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    '],
                    ['route' => 'admin.contact', 'label' => 'Contact', 'icon' => '
                    <path
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    '],
                ];
                @endphp

                @foreach ($navItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition
                        {{ $isActive ? 'bg-[#2ca5b8]/20 text-[#2ca5b8] hover:text-[#2ca5b8]' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="1.8">{!! $item['icon'] !!}</svg>
                    {{ $item['label'] }}
                    @if ($isActive)
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-[#2ca5b8]"></span>
                    @endif
                </a>
                @endforeach
            </nav>

            {{-- User & logout at bottom --}}
            <div class="border-t border-white/10 px-4 py-4">
                <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#2ca5b8] text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</div>
                        <div class="truncate text-[10px] text-white/40">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-white/60 transition hover:bg-red-500/10 hover:text-red-400">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="1.8">
                            <path
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Log out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex min-h-screen flex-1 flex-col min-w-0 transition-all duration-300 lg:ml-64">
            {{-- Top bar --}}
            <header
                class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-4 backdrop-blur-md sm:px-8">
                <div class="flex items-center gap-4">
                    <button id="mobile-menu-btn" type="button" class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#2ca5b8] lg:hidden">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-base font-semibold text-[#1a3a5c]">@yield('page-title', 'Admin')</h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        View site
                    </a>
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1a3a5c] text-xs font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-8">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const menuBtn = document.getElementById('mobile-menu-btn');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            menuBtn.addEventListener('click', openSidebar);
            overlay.addEventListener('click', closeSidebar);
        });
    </script>
</body>

</html>
