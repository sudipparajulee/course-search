@extends('layouts.app')

@section('page-title', 'Applications')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between no-print">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#1f5d92]">Print Preview</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Application #{{ $application->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">Print-friendly preview using the same PDF-style layout as the apply page.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Print
            </button>
            <a href="{{ route('admin.applications.view', $application) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Back to review
            </a>
        </div>
    </div>

    @include('application.partials.pdf-form', [
        'formSchema' => $formSchema,
        'formData' => $formData,
        'isReadOnly' => true,
    ])
</div>
@endsection
