@extends('layouts.app')

@section('page-title', 'Applications')

@section('content')
<div class="min-h-screen bg-[#f8fafb] py-8">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Applications</h1>
                <p class="mt-2 text-sm text-slate-600">Manage all student applications</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                Back to Admin
            </a>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4">
            <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-slate-900">Status</label>
                    <select name="status" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-900">Search</label>
                    <input type="text" name="search" placeholder="Name or email..." value="{{ request('search') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 rounded-lg bg-[#2ca5b8] px-4 py-2 text-sm font-semibold text-white hover:bg-[#238a9b]">
                        Filter
                    </button>
                    <a href="{{ route('admin.applications.list') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Applications Table -->
        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
            @if($applications->count())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-900">S.N.</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-900">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-900">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-900">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-900">Submitted</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($applications as $app)
                            @php
                                $statusClasses = [
                                    'draft' => 'bg-slate-100 text-slate-800',
                                    'submitted' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ ($applications->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-900">{{ $app->user?->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-600">{{ $app->user?->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $app->course_name ?: $app->course_id }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses[$app->status] ?? 'bg-slate-100 text-slate-800' }}">
                                        {{ ucfirst($app->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $app->created_at?->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.applications.view', $app) }}" title="View application" aria-label="View application" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.applications.pdf', $app) }}" title="Open PDF preview" aria-label="Open PDF preview" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6M9 17h6M9 9h1" />
                                            </svg>
                                        </a>
                                        <button
                                            type="button"
                                            title="Delete application"
                                            aria-label="Delete application"
                                            data-delete-url="{{ route('admin.applications.destroy', $app) }}"
                                            data-delete-title="Application #{{ $app->id }}"
                                            class="open-delete-modal inline-flex h-8 w-8 items-center justify-center rounded-md border border-red-200 text-red-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a1 1 0 011-1h4a1 1 0 011 1v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">No applications found</h3>
                    <p class="mt-2 text-sm text-slate-600">Try adjusting your search or filter criteria</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl shadow-slate-900/10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-600">Confirm deletion</p>
                <h3 id="delete-modal-title" class="mt-3 text-2xl font-semibold text-slate-900">Delete application</h3>
                <p id="delete-confirm-message" class="mt-3 text-sm leading-6 text-slate-600">
                    Are you sure you want to delete this application? This action cannot be undone.
                </p>
            </div>
            <button type="button" id="delete-modal-close" class="rounded-full border border-slate-200 bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                <span class="sr-only">Close modal</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button type="button" id="delete-cancel-btn" class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Cancel
            </button>
            <form id="delete-confirm-form" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex w-full justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                    Delete application
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('delete-confirm-modal');
        const modalText = document.getElementById('delete-confirm-message');
        const modalForm = document.getElementById('delete-confirm-form');
        const cancelBtn = document.getElementById('delete-cancel-btn');
        const closeBtn = document.getElementById('delete-modal-close');
        const openButtons = document.querySelectorAll('.open-delete-modal');

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const openModal = (action, label) => {
            modalForm.action = action;
            modalText.textContent = `Are you sure you want to delete ${label}? This action cannot be undone.`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.getAttribute('data-delete-url');
                const label = button.getAttribute('data-delete-title') || 'this application';
                openModal(action, label);
            });
        });

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    });
</script>
@endsection
