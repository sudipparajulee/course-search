@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-[#f5f7fb] py-8">
    <div class="mx-auto max-w-[1120px] px-4 lg:px-8">
        <div class="mb-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#1f5d92]">Apply</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $selectedProviderName !== '' ? $selectedProviderName.' Application Form' : 'Course Application Form' }}
                </h1>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    Complete the application below and review your details carefully before submitting.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    @if($selectedProviderName !== '')
                        <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700">
                            College: {{ $selectedProviderName }}
                        </div>
                    @endif
                    @if($selectedCourseTitle !== '')
                        <div class="inline-flex items-center rounded-full border border-[#cfe2f3] bg-[#eef6fb] px-4 py-2 text-sm font-medium text-[#1f5d92]">
                            Selected course: {{ $selectedCourseTitle }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <p class="font-semibold">Some fields still need attention.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('application.store') }}" class="space-y-8" data-college-form-editable="1">
            @csrf

            @include('application.partials.pdf-form', [
                'formSchema' => $formSchema,
                'formData' => $formData,
                'isReadOnly' => false,
            ])

            <div class="mx-auto max-w-[980px] rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm no-print">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Submit application</h2>
                        <p class="mt-1 text-sm text-slate-600">Review the form carefully and submit once all required details are complete.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a
                            href="{{ route('search') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Back to search
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-2xl bg-[#1f5d92] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#184a74]"
                        >
                            Submit application
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
