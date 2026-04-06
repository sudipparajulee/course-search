<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password — StudyAide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
</head>
<body class="min-h-screen bg-[#f0f6fb] flex items-center justify-center p-4">

<div class="w-full max-w-md">

    {{-- Logo --}}
    <a href="/" class="flex items-center justify-center mb-8">
        <img src="{{ asset('images/logo.png') }}" class="h-16 w-auto" alt="StudyAide logo" />
    </a>

    <div class="overflow-hidden rounded-2xl bg-white shadow-[0_8px_40px_rgba(26,58,92,0.12)] ring-1 ring-[#1a3a5c]/8">
        {{-- Top accent --}}
        <div class="h-1.5 w-full bg-gradient-to-r from-[#1a3a5c] via-[#2ca5b8] to-[#1a3a5c]"></div>

        <div class="px-8 py-10">
            <h1 class="text-2xl font-bold text-[#1a3a5c]">Create new password</h1>
            <p class="mt-1 text-sm text-slate-500">Choose a new, secure password for your account.</p>

            {{-- Validation errors --}}
            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-100 bg-red-50 p-4">
                    <ul class="space-y-1 text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Email address</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('email') border-red-400 @enderror"
                            placeholder="you@example.com" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">New Password</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </span>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('password') border-red-400 @enderror"
                            placeholder="••••••••" />
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm Password</label>
                    <div class="relative mt-2">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                        </span>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('password_confirmation') border-red-400 @enderror"
                            placeholder="••••••••" />
                    </div>
                </div>

                <button type="submit"
                    class="mt-2 w-full rounded-xl bg-[#1a3a5c] py-3.5 text-sm font-bold text-white shadow-md shadow-[#1a3a5c]/20 transition hover:bg-[#2ca5b8] active:scale-[0.98]">
                    Reset Password
                </button>
            </form>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} StudyAide. All rights reserved.</p>
</div>

</body>
</html>
