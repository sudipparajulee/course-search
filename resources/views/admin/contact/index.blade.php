@extends('layouts.app')
@section('title', 'Contact')
@section('page-title', 'Contact Enquiries')

@section('content')
<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
        <div>
            <h3 class="font-semibold text-[#1a3a5c]">Recent enquiries</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $contacts->count() }} messages</p>
        </div>
    </div>

    <div class="overflow-x-auto px-6 pb-6">
        <table id="contact-table" class="table is-fullwidth is-hoverable" style="width:100%">
            <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Visa status</th>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($contacts as $contact)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-slate-900">{{ $contact->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $contact->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $contact->visa_status }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $contact->created_at->format('j M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">
                            <div class="flex flex-wrap gap-2">
                                <button type="button"
                                    class="view-contact-button inline-flex items-center rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200"
                                    data-contact-name="{{ $contact->name }}"
                                    data-contact-email="{{ $contact->email }}"
                                    data-contact-visa="{{ $contact->visa_status }}"
                                    data-contact-date="{{ $contact->created_at->format('j M Y') }}"
                                    data-contact-message="{{ htmlentities($contact->message, ENT_QUOTES, 'UTF-8') }}">
                                    View
                                </button>
                                <button type="button"
                                    class="delete-contact-button inline-flex items-center rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-rose-700"
                                    data-action="{{ route('admin.contact.destroy', $contact) }}"
                                    data-contact-name="{{ $contact->name }}">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-10 text-center text-sm text-slate-500" colspan="5">No enquiries have been submitted yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="view-contact-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4 py-6">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl shadow-slate-900/10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-600">Contact details</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-900" id="view-contact-name">Contact details</h3>
                <p id="view-contact-email" class="mt-2 text-sm text-slate-500"></p>
            </div>
            <button type="button" id="view-modal-close" class="rounded-full border border-slate-200 bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                <span class="sr-only">Close modal</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>

        <div class="mt-6 space-y-4">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Visa Status</div>
                <div id="view-contact-visa" class="mt-2 text-sm font-semibold text-slate-900"></div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4">
                <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Submitted</div>
                <div id="view-contact-date" class="mt-2 text-sm font-semibold text-slate-900"></div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Message</div>
                <div id="view-contact-message" class="mt-2 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-700"></div>
            </div>
        </div>
    </div>
</div>

<div id="delete-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 px-4 py-6">
    <div class="w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl shadow-slate-900/10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-rose-600">Confirm deletion</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-900">Delete enquiry</h3>
                <p id="delete-confirm-message" class="mt-3 text-sm leading-6 text-slate-600">
                    Are you sure you want to delete this enquiry? This action cannot be undone.
                </p>
            </div>
            <button type="button" id="delete-modal-close" class="rounded-full border border-slate-200 bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                <span class="sr-only">Close modal</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                    Delete enquiry
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteModal = document.getElementById('delete-confirm-modal');
        const deleteClose = document.getElementById('delete-modal-close');
        const deleteCancel = document.getElementById('delete-cancel-btn');
        const confirmForm = document.getElementById('delete-confirm-form');
        const deleteMessage = document.getElementById('delete-confirm-message');

        const viewModal = document.getElementById('view-contact-modal');
        const viewClose = document.getElementById('view-modal-close');
        const viewName = document.getElementById('view-contact-name');
        const viewEmail = document.getElementById('view-contact-email');
        const viewVisa = document.getElementById('view-contact-visa');
        const viewDate = document.getElementById('view-contact-date');
        const viewMessage = document.getElementById('view-contact-message');

        function hideModal(modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showDeleteModal(action, name) {
            confirmForm.action = action;
            deleteMessage.textContent = `Are you sure you want to delete the enquiry from "${name}"? This action cannot be undone.`;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        }

        function showViewModal(name, email, visa, date, messageText) {
            viewName.textContent = name;
            viewEmail.textContent = email;
            viewVisa.textContent = visa;
            viewDate.textContent = date;
            viewMessage.textContent = messageText;
            viewModal.classList.remove('hidden');
            viewModal.classList.add('flex');
        }

        document.querySelectorAll('.delete-contact-button').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.dataset.action;
                const contactName = this.dataset.contactName;
                showDeleteModal(action, contactName);
            });
        });

        document.querySelectorAll('.view-contact-button').forEach(button => {
            button.addEventListener('click', function () {
                showViewModal(
                    this.dataset.contactName,
                    this.dataset.contactEmail,
                    this.dataset.contactVisa,
                    this.dataset.contactDate,
                    this.dataset.contactMessage
                );
            });
        });

        deleteClose.addEventListener('click', () => hideModal(deleteModal));
        deleteCancel.addEventListener('click', () => hideModal(deleteModal));
        deleteModal.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                hideModal(deleteModal);
            }
        });

        viewClose.addEventListener('click', () => hideModal(viewModal));
        viewModal.addEventListener('click', function (event) {
            if (event.target === viewModal) {
                hideModal(viewModal);
            }
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#contact-table').DataTable({
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: {
                search: 'Filter enquiries:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ messages',
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    });
</script>
@endsection
