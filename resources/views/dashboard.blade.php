@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
$stats = [
['label' => 'Total Users', 'value' => \App\Models\User::count(), 'icon' => '
<path
    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
', 'color' => 'bg-[#e0f7fa] text-[#1a3a5c]'],
['label' => 'Courses Listed', 'value' => '240+', 'icon' => '
<path
    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
', 'color' => 'bg-[#e8f3f8] text-[#1e6fa0]'],
['label' => 'Students Helped','value' => '15k+', 'icon' => '
<path
    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
', 'color' => 'bg-[#dbeeff] text-[#1a3a5c]'],
['label' => 'Satisfaction', 'value' => '98%', 'icon' => '
<path
    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
', 'color' => 'bg-amber-50 text-amber-700'],
];
@endphp

{{-- Stats --}}
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $stat)
    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat['color'] }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">{!!
                    $stat['icon'] !!}</svg>
            </div>
            <svg class="h-4 w-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </div>
        <div class="mt-4 text-2xl font-bold text-[#1a3a5c]">{{ $stat['value'] }}</div>
        <div class="mt-1 text-sm text-slate-500">{{ $stat['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Recent users + quick links --}}
<div class="mt-8 grid gap-6 lg:grid-cols-[1fr_320px]">

    {{-- Recent Users --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="font-semibold text-[#1a3a5c]">Recent registrations</h3>
            <a href="{{ route('admin.users') }}" class="text-xs font-medium text-[#2ca5b8] hover:underline">View all</a>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach (\App\Models\User::where('role', '!=', 'admin')->latest()->take(5)->get() as $user)
            <div class="flex items-center gap-4 px-6 py-3.5">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e0f7fa] text-xs font-bold text-[#1a3a5c]">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-slate-800">{{ $user->name }}</div>
                    <div class="truncate text-xs text-slate-400">{{ $user->email }}</div>
                </div>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide
                        {{ $user->role === 'admin' ? 'bg-[#1a3a5c] text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $user->role }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Quick links --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60 p-6">
        <h3 class="font-semibold text-[#1a3a5c] mb-4">Quick actions</h3>
        <div class="space-y-3">
            <a href="{{ route('admin.users') }}"
                class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-[#e0f7fa] hover:text-[#1a3a5c] hover:border-[#2ca5b8]/30">
                <svg class="h-4 w-4 text-[#2ca5b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Manage users
            </a>
            <a href="{{ route('admin.contact') }}"
                class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-[#e0f7fa] hover:text-[#1a3a5c] hover:border-[#2ca5b8]/30">
                <svg class="h-4 w-4 text-[#2ca5b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                View contacts
            </a>
        </div>
    </div>
</div>
@endsection