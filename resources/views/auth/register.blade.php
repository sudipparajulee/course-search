<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create account — StudyAide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
</head>
<body class="min-h-screen bg-[#f0f6fb] flex items-center justify-center p-4 py-12">

<div class="w-full max-w-xl">

    {{-- Logo --}}
    <a href="/" class="flex items-center justify-center mb-8">
        <img src="{{ asset('images/logo.png') }}" class="h-16 w-auto" alt="StudyAide logo" />
    </a>

    <div class="overflow-hidden rounded-2xl bg-white shadow-[0_8px_40px_rgba(26,58,92,0.12)] ring-1 ring-[#1a3a5c]/8">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#1a3a5c] via-[#2ca5b8] to-[#1a3a5c]"></div>

        <div class="px-8 py-10">
            <h1 class="text-2xl font-bold text-[#1a3a5c]">Create your account</h1>
            <p class="mt-1 text-sm text-slate-500">Join StudyAide — it's free</p>

            @if ($errors->any())
                <div class="mt-4 rounded-xl border border-red-100 bg-red-50 p-4">
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

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
                @csrf

                {{-- Photo upload --}}
                <div class="flex flex-col items-center gap-3">
                    <div id="photo-preview" class="h-20 w-20 rounded-full border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden relative cursor-pointer hover:border-[#2ca5b8] transition">
                        <svg id="photo-icon" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <img id="photo-img" class="hidden absolute inset-0 h-full w-full object-cover" alt="Preview" />
                        <input id="photo" name="photo" type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewPhoto(this)" />
                    </div>
                    <p class="text-xs text-slate-400">Click to upload profile photo <span class="text-slate-300">(optional)</span></p>
                </div>

                {{-- Name & Email --}}
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Full name <span class="text-red-400">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('name') border-red-400 @enderror"
                                placeholder="Full name e.g. John Doe" />
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Email address <span class="text-red-400">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('email') border-red-400 @enderror"
                                placeholder="you@example.com" />
                        </div>
                    </div>
                </div>

                {{-- Phone & Address --}}
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Phone number</label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400"
                                placeholder="+977 400 000 000" />
                        </div>
                    </div>
                    <div>
                        <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Address</label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <input id="address" name="address" type="text" value="{{ old('address') }}"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400"
                                placeholder="Enter your address" />
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Password <span class="text-red-400">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            </span>
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 @error('password') border-red-400 @enderror"
                                placeholder="••••••••" />
                        </div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm password <span class="text-red-400">*</span></label>
                        <div class="relative mt-2">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            </span>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400"
                                placeholder="••••••••" />
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-[#1a3a5c] py-3.5 text-sm font-bold text-white shadow-md shadow-[#1a3a5c]/20 transition hover:bg-[#2ca5b8] active:scale-[0.98]">
                    Create my account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-[#2ca5b8] hover:text-[#1a3a5c] transition">Sign in</a>
            </p>
        </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} StudyAide. All rights reserved.</p>
</div>

<script>
function previewPhoto(input) {
    const img = document.getElementById('photo-img');
    const icon = document.getElementById('photo-icon');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
