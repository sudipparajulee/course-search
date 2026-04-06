@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-semibold text-[#1a3a5c]">All users</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $users->count() }} user accounts</p>
        </div>
    </div>

    <div class="overflow-x-auto px-6 pb-6 mt-4">
        <table id="users-table" class="table is-fullwidth is-hoverable" style="width:100%">
            <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3 text-left">User</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Address</th>
                    <th class="px-6 py-3 text-left">Joined</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if ($user->photo)
                                    <img src="{{ asset('storage/' . $user->photo) }}" class="h-9 w-9 rounded-full object-cover shrink-0" alt="" />
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#e0f7fa] text-xs font-bold text-[#1a3a5c]">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-slate-800">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $user->phone ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 max-w-[200px] truncate">{{ $user->address ?? '—' }}</td>
                        <td class="px-6 py-4 text-xs text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">Edit</a>
                                <button type="button" class="delete-user-button inline-flex items-center rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700"
                                    data-action="{{ route('admin.users.destroy', $user) }}"
                                    data-user-name="{{ $user->name }}">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-10 text-center text-sm text-slate-500" colspan="5">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4 py-6">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl shadow-slate-900/10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-600">Confirm deletion</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-900">Delete user</h3>
                <p id="delete-confirm-message" class="mt-3 text-sm leading-6 text-slate-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
            </div>
            <button type="button" id="delete-modal-close" class="rounded-full border border-slate-200 bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                <span class="sr-only">Close modal</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button type="button" id="delete-cancel-btn" class="inline-flex justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
            <form id="delete-confirm-form" method="POST" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex w-full justify-center rounded-2xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">Delete user</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#users-table').DataTable({
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: {
                search: 'Filter users:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ users',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });

        const modal = document.getElementById('delete-confirm-modal');
        const confirmForm = document.getElementById('delete-confirm-form');
        const message = document.getElementById('delete-confirm-message');
        const closeModal = document.getElementById('delete-modal-close');
        const cancelBtn = document.getElementById('delete-cancel-btn');

        function hideModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showModal(action, name) {
            confirmForm.action = action;
            message.textContent = `Are you sure you want to delete the user "${name}"? This action cannot be undone.`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.querySelector('#users-table tbody').addEventListener('click', function (event) {
            const target = event.target.closest('.delete-user-button');
            if (!target) {
                return;
            }

            const action = target.dataset.action;
            const name = target.dataset.userName;
            showModal(action, name);
        });

        closeModal.addEventListener('click', hideModal);
        cancelBtn.addEventListener('click', hideModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                hideModal();
            }
        });
    });
</script>
@endsection
