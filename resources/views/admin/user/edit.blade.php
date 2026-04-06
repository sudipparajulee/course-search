@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="font-semibold text-[#1a3a5c]">Edit user account</h3>
        <p class="text-xs text-slate-400 mt-0.5">Update the selected user’s details below.</p>
    </div>

    <div class="p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Name</label>
                <input name="name" type="text" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[#2ca5b8] focus:outline-none" required />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[#2ca5b8] focus:outline-none" required />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</label>
                <input name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[#2ca5b8] focus:outline-none" />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
                <input name="address" type="text" value="{{ old('address', $user->address) }}" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 focus:border-[#2ca5b8] focus:outline-none" />
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#1a3a5c] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#2ca5b8]">Save changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
