@php
    $toastMessage = session('success') ?? session('error') ?? session('status');
    $toastType = session('error') ? 'rose' : 'emerald';
@endphp

@if ($toastMessage)
    <div id="layout-toaster" class="fixed right-4 top-4 z-[99999] max-w-sm rounded-3xl border border-slate-200/80 bg-white/95 px-5 py-4 shadow-2xl shadow-slate-900/10 backdrop-blur-xl text-slate-900 opacity-0 translate-x-6 transition duration-500 ease-out">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-2xl bg-{{ $toastType }}-100 text-{{ $toastType }}-600">
                @if ($toastType === 'emerald')
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @else
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12A9 9 0 113 12a9 9 0 0118 0z"/></svg>
                @endif
            </div>

            <div class="flex-1 text-sm leading-6">
                <p class="font-semibold text-slate-900">{{ $toastType === 'emerald' ? 'Success' : 'Error' }}</p>
                <p class="mt-1 text-slate-600">{{ $toastMessage }}</p>
            </div>

            <button type="button" id="layout-toaster-close" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <span class="sr-only">Close notification</span>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('layout-toaster');
            const close = document.getElementById('layout-toaster-close');
            if (!toast) return;

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-x-6');
                toast.classList.add('opacity-100');
            });

            const hide = () => {
                toast.classList.add('opacity-0', 'translate-x-6');
                toast.classList.remove('opacity-100');
                toast.addEventListener('transitionend', function () {
                    toast.remove();
                }, { once: true });
            };

            close?.addEventListener('click', hide);
            setTimeout(hide, 5000);
        });
    </script>
@endif
