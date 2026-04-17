@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-[#f5f7fb] py-8">
    <div class="mx-auto max-w-[1120px] px-4 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-8 no-print">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#1f5d92]">Application Preview</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Application #{{ $application->id }}</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Submitted {{ $application->submitted_at?->format('M d, Y H:i') ?? $application->created_at?->format('M d, Y H:i') }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Print
                </button>
                <a
                    href="{{ $originalPdfUrl }}"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Open original PDF
                </a>
                <a
                    href="{{ route('application.success', $application) }}"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Back
                </a>
            </div>
        </div>

        @include('application.partials.pdf-form', [
            'formSchema' => $formSchema,
            'formData' => $formData,
            'isReadOnly' => true,
        ])
    </div>
</div>
@endsection
