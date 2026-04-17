@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-[#f8fafb] py-12">
    <div class="mx-auto max-w-2xl px-4 lg:px-8">
        <div class="rounded-[28px] border border-slate-200 bg-white shadow-sm p-8">
            <!-- Success Icon -->
            <div class="mb-6 flex justify-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Success Message -->
            <div class="text-center">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Application Submitted</h1>
                <p class="mt-4 text-base text-slate-600">
                    Your application has been successfully submitted. We'll review your information and contact you within 5-7 business days.
                </p>
            </div>

            <!-- Application Details -->
            <div class="mt-8 rounded-lg bg-slate-50 p-6">
                <h2 class="text-sm font-semibold text-slate-900">Application Details</h2>
                <dl class="mt-4 space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-600">Application ID</dt>
                        <dd class="text-sm font-medium text-slate-900">#{{ $application->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-600">Course</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ $application->course_name ?? $application->course_id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-600">Submitted</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ $application->created_at?->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-slate-600">Status</dt>
                        <dd class="text-sm font-medium">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800">
                                {{ ucfirst($application->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:gap-4">
                <a href="{{ request()->getBaseUrl().route('application.pdf', $application, false) }}" class="inline-flex items-center justify-center rounded-lg bg-[#2ca5b8] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#238a9b] focus:outline-none focus:ring-2 focus:ring-[#2ca5b8] focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    View Application
                </a>
                <a href="{{ request()->getBaseUrl().route('search', [], false) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Back to Search
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 border-t border-slate-200 pt-6">
                <h3 class="text-sm font-semibold text-slate-900">What happens next?</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li class="flex items-start">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-[#2ca5b8]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>We'll review your application within 5-7 business days</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-[#2ca5b8]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>You'll receive an email with our decision and next steps</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0 text-[#2ca5b8]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Keep your Application ID (#{{ $application->id }}) for future reference</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
