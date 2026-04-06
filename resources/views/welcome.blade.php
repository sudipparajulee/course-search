@extends('layouts.master')
@section('content')
	<div class="bg-[#f8fafb]">
		<section class="relative w-full px-0 py-0 overflow-hidden">
			<div class="relative overflow-hidden bg-[#0d2640] min-h-[520px]">
				<div class="absolute inset-0">
					<div class="absolute inset-0 bg-gradient-to-r from-[#0d2640] via-[#0d2640]/90 to-[#1a3a5c]/70"></div>
				</div>

				{{-- Decorative teal accent --}}
				<div class="absolute -top-20 -right-20 h-96 w-96 rounded-full bg-[#2ca5b8]/10 blur-3xl"></div>
				<div class="absolute bottom-0 left-1/2 h-64 w-64 rounded-full bg-[#2ca5b8]/5 blur-2xl"></div>

				<div class="relative z-10 mx-auto grid max-w-7xl gap-10 px-4 py-20 lg:grid-cols-[1.1fr_0.9fr] lg:px-12 lg:py-28">
					<div class="flex flex-col justify-center text-white">
						<div class="mb-5 inline-flex self-start items-center gap-2 rounded-full border border-[#2ca5b8]/30 bg-[#2ca5b8]/10 px-4 py-1.5 text-[0.75rem] font-bold uppercase tracking-widest text-[#2ca5b8]">
							<span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#2ca5b8] opacity-75"></span><span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-[#2ca5b8]"></span></span>
							International Students
						</div>
						<h1 class="max-w-xl text-4xl font-extrabold leading-[1.1] sm:text-[3.2rem]">
							Start studying this year with confidence
						</h1>
						<p class="mt-6 max-w-lg text-base leading-7 text-white/70 sm:text-lg">
							Find the right course, understand entry requirements and apply with confidence.
						</p>

						<div class="mt-8 flex flex-wrap items-center gap-4">
							<a href="{{ route('login') }}"
								class="inline-flex items-center gap-2.5 rounded-full bg-[#1a3a5c] px-8 py-3.5 text-sm font-bold text-white shadow-lg shadow-[#1a3a5c]/30 transition hover:bg-[#2ca5b8] active:scale-[0.98]">
								<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
								Start your application
							</a>
						</div>
					</div>
					<div class="hidden lg:flex lg:items-center lg:justify-end">
						<div class="grid grid-cols-2 gap-4 text-white">
							<div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
								<div class="text-3xl font-extrabold text-[#2ca5b8]">15k+</div>
								<div class="mt-1 text-sm text-white/60">Students helped</div>
							</div>
							<div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
								<div class="text-3xl font-extrabold text-[#2ca5b8]">240+</div>
								<div class="mt-1 text-sm text-white/60">Courses available</div>
							</div>
							<div class="col-span-2 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
								<div class="text-3xl font-extrabold text-[#2ca5b8]">98%</div>
								<div class="mt-1 text-sm text-white/60">Student satisfaction rate</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section id="international-section" class="relative z-10 mx-auto max-w-7xl px-4 pb-16 lg:px-6">
			<div class="overflow-hidden rounded-2xl bg-white shadow-[0_8px_40px_rgba(26,58,92,0.10)] ring-1 ring-[#1a3a5c]/10 lg:-mt-[2rem] w-full">

				{{-- Tab nav --}}
				<div class="border-b border-slate-100 px-4 pt-3 sm:px-8">
					<div class="flex items-center gap-0 overflow-x-auto no-scrollbar" role="tablist">
						@php
							$tabs = [
							'international' => [
									'label'  => 'International',
									'desc'   => "You’re an International student, and completed Year 12 and degree and applying further.",
									'action' => '/course-search/search/find-a-course-international',
								],
							'undergraduate' => [
									'label'  => 'Undergraduate',
									'desc'   => "You have finished high school, never graduated from university, or are returning to study something different.",
									'action' => '/course-search/search/find-a-course-undergraduate',
								],
							'postgraduate' => [
									'label'  => 'Postgraduate',
									'desc'   => "You have completed an undergraduate degree and are looking to pursue a higher-level qualification.",
									'action' => '/course-search/search/find-a-course-postgraduate',
								],
                            'vet' => [
                                    'label'  => 'VET',
                                    'desc'   => "You are interested in Vocational Education and Training courses.",
                                    'action' => '/course-search/search/find-a-course-vet',
                                ],

						];
						@endphp

						@foreach ($tabs as $key => $tab)
							<button
								type="button"
								role="tab"
								id="tab-{{ $key }}"
								data-tab="{{ $key }}"
								aria-selected="{{ $loop->first ? 'true' : 'false' }}"
								aria-controls="panel-{{ $key }}"
								class="home-tab"
							>
								{{ $tab['label'] }}
							</button>
						@endforeach
					</div>
				</div>

				{{-- Tab panels --}}
				@foreach ($tabs as $key => $tab)
					<div
						id="panel-{{ $key }}"
						role="tabpanel"
						aria-labelledby="tab-{{ $key }}"
						class="home-panel flex-col gap-6 px-6 py-6 lg:flex-row lg:items-center lg:justify-between lg:px-8 lg:py-8"
					>
						<div class="max-w-[400px]">
							<p class="text-[13px] font-medium leading-[1.7] text-slate-600">
								{{ $tab['desc'] }}
							</p>
						</div>

						<form class="flex w-full lg:max-w-[580px]" action="{{ $tab['action'] }}" method="GET">
							<label class="flex w-full items-center overflow-hidden rounded-full border border-slate-200 bg-[#f8fafb] p-[4px] shadow-sm transition">
								<span class="sr-only">Search courses</span>
								<svg class="ml-3 h-4 w-4 shrink-0 text-[#2ca5b8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="6.75"/><path d="m16 16 5 5"/></svg>
								<input
									type="text"
									name="search"
									autocomplete="off"
									class="w-full border-0 bg-transparent px-4 py-2.5 text-[13px] text-slate-700 outline-none focus:outline-none focus:ring-0 focus:border-transparent placeholder:text-slate-400"
									placeholder="Enter course name or area of study…" />
								<button
									type="submit"
									class="ml-2 inline-flex shrink-0 items-center justify-center gap-1.5 rounded-full bg-[#1a3a5c] px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-[#2ca5b8]">
									Find courses
								</button>
							</label>
						</form>
					</div>
				@endforeach
			</div>

			<script>
				(function () {
					const tabs   = document.querySelectorAll('.home-tab');
					const panels = document.querySelectorAll('.home-panel');

					function activate(target) {
						tabs.forEach(function (t) {
							const active = t.dataset.tab === target;
							t.setAttribute('aria-selected', active ? 'true' : 'false');
							t.dataset.active = active ? '1' : '';
						});
						panels.forEach(function (p) {
							const show = p.id === 'panel-' + target;
							p.style.display = show ? 'flex' : 'none';
						});
					}

					tabs.forEach(function (tab) {
						tab.addEventListener('click', function () {
							activate(tab.dataset.tab);
						});
					});

					// Init: honour which tab was marked first
					const first = document.querySelector('.home-tab');
					if (first) activate(first.dataset.tab);
				})();
			</script>

			<style>
				.home-tab {
					border-bottom: 2.5px solid transparent;
					color: #64748b;
					padding: 0.625rem 1.25rem 0.75rem;
					font-size: 13px;
					font-weight: 600;
					white-space: nowrap;
					transition: color 0.15s, border-color 0.15s;
					background: none;
					cursor: pointer;
				}
				.home-tab:hover { color: #1a3a5c; }
				.home-tab[data-active="1"] {
					border-bottom-color: #2ca5b8;
					color: #1a3a5c;
				}
				.home-panel { display: none; }
			</style>
		</section>




		<section class="relative mx-auto max-w-7xl px-4 py-20 lg:px-6 lg:py-28">
			<div class="grid gap-16 lg:grid-cols-2 lg:items-center">
				<div>
					<div class="inline-flex items-center gap-2 rounded-full bg-[#e0f7fa] px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-[#1a3a5c]">
						<span class="relative flex h-2 w-2">
							<span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#2ca5b8] opacity-75"></span>
							<span class="relative inline-flex h-2 w-2 rounded-full bg-[#2ca5b8]"></span>
						</span>
						About Us
					</div>
					<h2 class="mt-6 text-4xl font-bold tracking-tight text-[#1a3a5c] sm:text-5xl lg:leading-[1.15]">
						Helping students find the <span class="bg-gradient-to-r from-[#1a3a5c] to-[#2ca5b8] bg-clip-text text-transparent">right course</span> and apply with confidence.
					</h2>
					<p class="mt-8 text-lg leading-relaxed text-slate-600">
						We support all International students through every step of the application Journey. Our goal is to make course search,
						admissions advice and student support easier, faster and more reliable.
					</p>
					<div class="mt-10 flex gap-10 border-t border-slate-100 pt-10">
						<div>
							<div class="text-3xl font-bold text-[#1a3a5c]">15k+</div>
							<div class="mt-1 text-sm text-slate-500 font-medium">Students Helped</div>
						</div>
						<div>
							<div class="text-3xl font-bold text-[#1a3a5c]">240+</div>
							<div class="mt-1 text-sm text-slate-500 font-medium">Courses Available</div>
						</div>
					</div>
				</div>

				<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:gap-6">
					<div class="space-y-4">
						<div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition hover:shadow-md hover:border-[#2ca5b8]/30">
							<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e0f7fa] text-[#1a3a5c]">
								<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
							</div>
							<h3 class="mt-4 font-bold text-[#1a3a5c]">Expert Guidance</h3>
							<p class="mt-2 text-sm text-slate-500 leading-relaxed">Support at every step of your journey.</p>
						</div>
						<div class="rounded-3xl border border-[#2ca5b8]/20 bg-[#e0f7fa]/30 p-6 shadow-sm transition hover:shadow-md">
							<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#2ca5b8] shadow-sm border border-slate-50">
								<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
							</div>
							<h3 class="mt-4 font-bold text-[#1a3a5c]">Fast Support</h3>
							<p class="mt-2 text-sm text-slate-500 leading-relaxed">Timely answers whenever you need them.</p>
						</div>
					</div>
					<div class="space-y-4 sm:pt-10">
						<div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition hover:shadow-md hover:border-[#2ca5b8]/30">
							<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e8f3f8] text-[#1e6fa0]">
								<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
							</div>
							<h3 class="mt-4 font-bold text-[#1a3a5c]">Trusted Advice</h3>
							<p class="mt-2 text-sm text-slate-500 leading-relaxed">Clear and reliable admissions info.</p>
						</div>
						<div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition hover:shadow-md hover:border-[#2ca5b8]/30">
							<div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e0f7fa] text-[#2ca5b8]">
								<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
							</div>
							<h3 class="mt-4 font-bold text-[#1a3a5c]">Smooth Process</h3>
							<p class="mt-2 text-sm text-slate-500 leading-relaxed">A simplified journey for your future.</p>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="relative bg-[#f0f8fc] py-24 sm:py-32 overflow-hidden">
			<div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-[#2ca5b8]/30 to-transparent"></div>
			<div class="mx-auto max-w-7xl px-4 lg:px-6">
				<div class="text-center max-w-3xl mx-auto mb-20">
					<div class="inline-flex items-center gap-2 rounded-full bg-[#e0f7fa] px-3 py-1.5 text-[11px] font-bold uppercase tracking-widest text-[#1a3a5c] mb-6">
						Why Choose Us
					</div>
					<h2 class="text-4xl font-bold tracking-tight text-[#1a3a5c] sm:text-5xl">
						Study with <span class="text-[#2ca5b8]">confidence,</span> support and clarity.
					</h2>
					<p class="mt-6 text-lg leading-relaxed text-slate-600">
						We deliver personalised course recommendations, clear admissions support, and fast responses so you can move forward with confidence.
					</p>
				</div>

				<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
					<div class="group relative rounded-3xl bg-white p-8 shadow-sm ring-1 ring-[#1a3a5c]/5 transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#1a3a5c]/10">
						<div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e0f7fa] text-[#1a3a5c] transition group-hover:bg-[#1a3a5c] group-hover:text-[#2ca5b8]">
							<span class="text-xl font-bold">1</span>
						</div>
						<h3 class="mt-6 text-xl font-bold text-[#1a3a5c]">Tailored course advice</h3>
						<p class="mt-3 text-sm text-slate-500 leading-relaxed">Find the course that fits your goals, profile and future plans perfectly.</p>
					</div>

					<div class="group relative rounded-3xl bg-white p-8 shadow-sm ring-1 ring-[#1a3a5c]/5 transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#1a3a5c]/10">
						<div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e8f3f8] text-[#1e6fa0] transition group-hover:bg-[#1a3a5c] group-hover:text-[#2ca5b8]">
							<span class="text-xl font-bold">2</span>
						</div>
						<h3 class="mt-6 text-xl font-bold text-[#1a3a5c]">Admissions clarity</h3>
						<p class="mt-3 text-sm text-slate-500 leading-relaxed">Understand entry requirements and next steps with complete confidence.</p>
					</div>

					<div class="group relative rounded-3xl bg-white p-8 shadow-sm ring-1 ring-[#1a3a5c]/5 transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#1a3a5c]/10">
						<div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#dbeeff] text-[#1a3a5c] transition group-hover:bg-[#1a3a5c] group-hover:text-[#2ca5b8]">
							<span class="text-xl font-bold">3</span>
						</div>
						<h3 class="mt-6 text-xl font-bold text-[#1a3a5c]">Quick support</h3>
						<p class="mt-3 text-sm text-slate-500 leading-relaxed">Get fast responses to questions about your study path and application.</p>
					</div>

					<div class="group relative rounded-3xl bg-white p-8 shadow-sm ring-1 ring-[#1a3a5c]/5 transition duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-[#1a3a5c]/10">
						<div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e0f7fa] text-[#2ca5b8] transition group-hover:bg-[#1a3a5c] group-hover:text-[#2ca5b8]">
							<span class="text-xl font-bold">4</span>
						</div>
						<h3 class="mt-6 text-xl font-bold text-[#1a3a5c]">Supportive experience</h3>
						<p class="mt-3 text-sm text-slate-500 leading-relaxed">We simplify the entire journey so you can focus on your ultimate goals.</p>
					</div>
				</div>
			</div>
		</section>

		<section id="contact" class="mx-auto max-w-5xl px-4 py-24 lg:px-6">
			<div class="relative overflow-hidden rounded-[2.5rem] bg-white shadow-[0_32px_120px_rgba(15,23,42,0.1)] ring-1 ring-slate-100">
				<div class="absolute top-0 right-0 -mr-20 -mt-20 h-64 w-64 rounded-full bg-blue-50/50"></div>
				<div class="absolute bottom-0 left-0 -ml-20 -mb-20 h-64 w-64 rounded-full bg-indigo-50/50"></div>

				<div class="relative grid lg:grid-cols-[0.4fr_0.6fr]">
					<div class="bg-[#0d2640] p-10 text-white lg:p-14">
						<p class="text-sm font-bold uppercase tracking-widest text-[#2ca5b8]">Get Started</p>
						<h2 class="mt-4 text-4xl font-bold leading-tight">Let us help you find your path</h2>
						<p class="mt-6 text-white/70 leading-relaxed">
							Book a free consultation with our expert admissions team today.
						</p>

						<div class="mt-12 space-y-6">
							<div class="flex items-center gap-4">
								<div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#2ca5b8]/20">
									<svg class="h-5 w-5 text-[#2ca5b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
								</div>
								<span class="text-white/80">+977 9855052132</span>
							</div>
							<div class="flex items-center gap-4">
								<div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#2ca5b8]/20">
									<svg class="h-5 w-5 text-[#2ca5b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
								</div>
								<span class="text-white/80">info@studyaide.com.np</span>
							</div>
							<div class="flex items-center gap-4">
								<div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#2ca5b8]/20">
									<svg class="h-5 w-5 text-[#2ca5b8]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414M16 6a6 6 0 11-12 0 6 6 0 0112 0zm-6 6c-5.333 0-8 4.667-8 8h16c0-3.333-2.667-8-8-8z"/></svg>
								</div>
								<span class="text-white/80">Bharatpur -10, Chitwan, Nepal</span>
							</div>
						</div>
					</div>

					<div class="p-10 lg:p-14">
						<form method="POST" action="{{ route('contact.submit') }}" class="grid gap-6">
								@csrf
								@if ($errors->any())
									<div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
										<ul class="list-disc pl-5">
											@foreach ($errors->all() as $error)
												<li>{{ $error }}</li>
											@endforeach
										</ul>
									</div>
								@endif
								<div class="grid gap-6 sm:grid-cols-2">
									<div>
										<label class="text-xs font-bold uppercase tracking-wider text-slate-500">First Name</label>
										<input name="first_name" type="text" value="{{ old('first_name') }}" class="mt-2 w-full rounded-2xl border-0 bg-slate-50 px-5 py-4 text-sm text-slate-900 transition" placeholder="John" />
									</div>
									<div>
										<label class="text-xs font-bold uppercase tracking-wider text-slate-500">Last Name</label>
										<input name="last_name" type="text" value="{{ old('last_name') }}" class="mt-2 w-full rounded-2xl border-0 bg-slate-50 px-5 py-4 text-sm text-slate-900 transition" placeholder="Doe" />
									</div>
								</div>
								<div>
									<label class="text-xs font-bold uppercase tracking-wider text-slate-500">Email Address</label>
									<input name="email" type="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border-0 bg-slate-50 px-5 py-4 text-sm text-slate-900 transition" placeholder="john@example.com" />
								</div>
								<div>
									<label class="text-xs font-bold uppercase tracking-wider text-slate-500">Visa Status</label>
									<select name="visa_status" class="mt-2 w-full appearance-none rounded-2xl border-0 bg-slate-50 px-5 py-4 text-sm text-slate-900 transition">
										<option value="International Student" {{ old('visa_status') === 'International Student' ? 'selected' : '' }}>International Student</option>
										<option value="Working Holiday" {{ old('visa_status') === 'Working Holiday' ? 'selected' : '' }}>Working Holiday</option>
										<option value="Visitor" {{ old('visa_status') === 'Visitor' ? 'selected' : '' }}>Visitor</option>
									</select>
								</div>
								<div>
									<label class="text-xs font-bold uppercase tracking-wider text-slate-500">How can we help?</label>
									<textarea name="message" rows="4" class="mt-2 w-full rounded-2xl border-0 bg-slate-50 px-5 py-4 text-sm text-slate-900 transition" placeholder="Tell us about your goals...">{{ old('message') }}</textarea>
								</div>
								<button type="submit" class="mt-4 rounded-2xl bg-[#1a3a5c] py-4 text-sm font-bold text-white shadow-lg shadow-[#1a3a5c]/20 transition hover:bg-[#2ca5b8] active:scale-[0.98]">
									Book Consultation
								</button>
							</form>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection
