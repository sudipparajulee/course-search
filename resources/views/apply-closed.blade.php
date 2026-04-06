@extends('layouts.master')

@section('title', 'Applications Closed')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center p-6 bg-[#f8fafb]">
    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-200">
        <!-- Modal style top accent -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#1a3a5c] via-[#2ca5b8] to-[#1a3a5c]"></div>

        <div class="px-8 pb-10 pt-8">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Applications closed
                </span>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-[#1a3a5c]">Application Portal</h1>
            <p class="mt-3 text-[1rem] leading-relaxed text-slate-600">
                We are currently not accepting new applications. Please review the schedule below for upcoming admission rounds.
            </p>

            <div class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-5 space-y-3">
                <p class="text-[0.95rem] font-medium text-slate-800">
                    Applications for semester 1, 2026 have now closed.
                </p>
                <p class="text-[0.95rem] text-slate-600">
                    Applications for semester 2, 2026 and semester 1, 2027 admissions open <strong>22 April</strong>.
                </p>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ url('/search') }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#1a3a5c] px-6 py-3.5 text-[0.95rem] font-semibold text-white shadow-sm transition hover:bg-[#2ca5b8]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to course search
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
