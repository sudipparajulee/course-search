@extends('layouts.app')

@section('page-title', 'Applications')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-[#1f5d92]">Admin Review</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">Application #{{ $application->id }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                {{ $application->user?->name ?? 'Unknown student' }} • {{ $application->user?->email ?? 'No email' }}
            </p>
        </div>

        <div class="flex flex-wrap gap-3 no-print">
            <a href="{{ route('admin.applications.list') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Back to list
            </a>
            <a href="{{ route('admin.applications.pdf', $application) }}" class="inline-flex items-center justify-center rounded-2xl bg-[#1f5d92] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#184a74]">
                Print view
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <p class="font-semibold">Please review the highlighted admin fields.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div>
            @include('application.partials.pdf-form', [
                'formSchema' => $formSchema,
                'formData' => $formData,
                'isReadOnly' => true,
            ])
        </div>

        <aside class="space-y-6 no-print">
            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Update application</h2>
                <p class="mt-2 text-sm text-slate-600">Status, notes, and office-use fields are stored with this application record.</p>

                <form method="POST" action="{{ route('admin.applications.update-status', $application) }}" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="status" class="block text-sm font-medium text-slate-900">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm">
                            <option value="draft" @selected($application->status === 'draft')>Draft</option>
                            <option value="submitted" @selected($application->status === 'submitted')>Submitted</option>
                            <option value="approved" @selected($application->status === 'approved')>Approved</option>
                            <option value="rejected" @selected($application->status === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-slate-900">Admin notes</label>
                        <textarea name="notes" id="notes" rows="5" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm" placeholder="Add internal notes...">{{ old('notes', $application->notes) }}</textarea>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label for="office_received_date" class="block text-sm font-medium text-slate-900">Received date</label>
                            <input type="text" name="office_received_date" id="office_received_date" value="{{ old('office_received_date', $formData['office_received_date'] ?? '') }}" placeholder="dd/mm/yyyy" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm">
                        </div>
                        <div>
                            <label for="office_approved_date" class="block text-sm font-medium text-slate-900">Approved date</label>
                            <input type="text" name="office_approved_date" id="office_approved_date" value="{{ old('office_approved_date', $formData['office_approved_date'] ?? '') }}" placeholder="dd/mm/yyyy" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm">
                        </div>
                        <div>
                            <label for="approved_by" class="block text-sm font-medium text-slate-900">Approved by</label>
                            <input type="text" name="approved_by" id="approved_by" value="{{ old('approved_by', $formData['approved_by'] ?? '') }}" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm">
                        </div>
                        <div>
                            <label for="office_signature" class="block text-sm font-medium text-slate-900">Office signature</label>
                            <input type="text" name="office_signature" id="office_signature" value="{{ old('office_signature', $formData['office_signature'] ?? '') }}" class="mt-1 block w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm shadow-sm">
                        </div>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#1f5d92] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#184a74]">
                        Save changes
                    </button>
                </form>
            </div>

            <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                @php
                    $statusClasses = [
                        'draft' => 'bg-slate-100 text-slate-800',
                        'submitted' => 'bg-amber-100 text-amber-800',
                        'approved' => 'bg-emerald-100 text-emerald-800',
                        'rejected' => 'bg-rose-100 text-rose-800',
                    ];
                @endphp

                <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Snapshot</h3>
                <span class="mt-4 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$application->status] ?? 'bg-slate-100 text-slate-800' }}">
                    {{ ucfirst($application->status) }}
                </span>

                <dl class="mt-4 space-y-3 text-sm text-slate-700">
                    <div><span class="font-medium text-slate-900">Course:</span> {{ $application->course_name ?: 'Not set' }}</div>
                    <div><span class="font-medium text-slate-900">Submitted:</span> {{ $application->submitted_at?->format('M d, Y H:i') ?? $application->created_at?->format('M d, Y H:i') }}</div>
                    <div><span class="font-medium text-slate-900">Reviewed:</span> {{ $application->reviewed_at?->format('M d, Y H:i') ?? 'Not reviewed yet' }}</div>
                </dl>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $originalPdfUrl }}" target="_blank" rel="noreferrer" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Open original PDF
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
