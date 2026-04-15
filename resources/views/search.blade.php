@extends('layouts.master')

@section('content')
<div class="min-h-screen bg-[#f8fafb] pb-20">
    <div class="mx-auto max-w-[1600px] px-4 py-8 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)] lg:gap-10">
            <aside id="course-search-sidebar" class="self-start lg:sticky lg:top-6"></aside>

            <section class="min-w-0">
                <div class="relative mb-5">
                    <h1 class="text-[1.25rem] font-semibold leading-tight text-slate-800 sm:text-[1.4rem]">
                        Search for
                        <span class="relative inline-flex">
                            <button
                                id="searchTypeToggle"
                                type="button"
                                class="inline-flex items-center gap-1.5 border-b-[3px] border-[#2ca5b8] pb-1 font-bold text-[#1a3a5c]"
                                aria-haspopup="menu"
                                aria-expanded="false"
                            >
                                <span>{{ $searchTypeLabel }}</span>
                                <svg id="searchTypeChevron" class="h-5 w-5 transition-transform" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M7 10l5 5 5-5H7z"></path>
                                </svg>
                            </button>

                            <div id="searchTypeMenu" class="absolute left-0 top-full z-20 mt-4 hidden min-w-[270px] border border-slate-200 bg-white shadow-lg">
                                @foreach ($searchTypeMeta as $typeKey => $typeConfig)
                                    <button
                                        type="button"
                                        data-search-type="{{ $typeKey }}"
                                        class="block w-full px-6 py-3.5 text-left text-[0.85rem] font-medium transition hover:bg-slate-50 {{ $typeKey === $searchType ? 'bg-slate-50 text-slate-900' : 'text-slate-700' }}"
                                    >
                                        {{ $typeConfig['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </span>
                        {{ $searchHeadingSuffix }}
                    </h1>
                </div>

                <form id="courseSearchForm" action="{{ $searchTypeMeta[$searchType]['pagePath'] }}" class="flex overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-transparent focus-within:ring-[#2ca5b8]/30">
                    <label for="searchInput" class="sr-only">Search courses</label>
                    <input
                        id="searchInput"
                        type="text"
                        name="search"
                        autocomplete="off"
                        class="min-w-0 flex-1 border-0 px-4 py-2.5 text-[0.88rem] text-slate-700 placeholder:text-slate-400 outline-none focus:ring-0"
                        placeholder="Enter course name or area of study"
                    />
                    <button
                        type="submit"
                        class="inline-flex shrink-0 items-center justify-center rounded-r-xl bg-[#1a3a5c] px-7 text-white transition hover:bg-[#2ca5b8]"
                        aria-label="Find courses"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.75"></circle>
                            <path d="m16 16 5 5"></path>
                        </svg>
                    </button>
                </form>

                <div id="course-search-toolbar" class="mt-5"></div>
                <div id="course-search-content" class="mt-6"></div>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchTypeMeta = @json($searchTypeMeta);
    const endpoints = {
        search: @json(url('/api/course-search')),
        filters: @json(url('/api/course-search/filters')),
        campus: @json(url('/api/course-search/campus')),
    };

    const allowedSizes = [10, 30, 50, 100, 500];
    const numberFormatter = new Intl.NumberFormat('en-AU');
    const dateFormatter = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });

    const filterMeta = {
        inst: {
            label: 'Institutions / University',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M243.4 2.6l-224 96c-14 6-21.8 21-18.7 35.8S16.8 160 32 160v8c0 13.3 10.7 24 24 24h400c13.3 0 24-10.7 24-24v-8c15.2 0 28.3-10.7 31.3-25.6s-4.8-29.9-18.7-35.8l-224-96c-8-3.4-17.2-3.4-25.2 0zM128 224H64v192.3c-.6.3-1.2.7-1.8 1.1l-48 32c-11.7 7.8-17 22.4-12.9 35.9S17.9 512 32 512h448c14.1 0 26.5-9.2 30.6-22.7s-1.1-28.1-12.9-35.9l-48-32c-.6-.4-1.2-.7-1.8-1.1V224h-64v192h-40V224h-64v192h-48V224h-64v192h-40V224z"></path></svg>',
        },
        fos: {
            label: 'Areas of study',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M96 0C43 0 0 43 0 96v320c0 53 43 96 96 96h288 32c17.7 0 32-14.3 32-32s-14.3-32-32-32v-64c17.7 0 32-14.3 32-32V32c0-17.7-14.3-32-32-32H384 96zm0 384h256v64H96c-17.7 0-32-14.3-32-32s14.3-32 32-32zm48-240c0-8.8 7.2-16 16-16h176c8.8 0 16 7.2 16 16s-7.2 16-16 16H160c-8.8 0-16-7.2-16-16zm16 48h176c8.8 0 16 7.2 16 16s-7.2 16-16 16H160c-8.8 0-16-7.2-16-16s7.2-16 16-16z"></path></svg>',
        },
        level: {
            label: 'Level of Course',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M80 336a176 176 0 1 1 352 0 176 176 0 1 1-352 0zm167.6-94.9c3.5-7 13.4-7 16.9 0l22.3 45.4c1.4 2.7 4 4.7 7 5.1l50.1 7.3c7.7 1.1 10.8 10.5 5.2 16l-36.2 35.4c-2.2 2.2-3.3 5.2-2.8 8.3l8.6 49.9c1.3 7.6-6.7 13.5-13.6 9.9l-44.8-23.6c-2.7-1.4-6-1.4-8.7 0l-44.8 23.6c-6.9 3.6-14.9-2.3-13.6-9.9l8.6-49.9c.5-3.1-.5-6.1-2.8-8.3l-36.2-35.4c-5.6-5.5-2.5-14.9 5.2-16l50.1-7.3c3-.4 5.6-2.4 7-5.1l22.5-45.4zM24.6 0H133.9c11.2 0 21.7 5.9 27.4 15.5l68.5 114.1c-48.2 6.1-91.3 28.6-123.4 61.9L4.1 38.2C1.4 34.2 0 29.4 0 24.6 0 11 11 0 24.6 0zM487.4 0c13.6 0 24.6 11 24.6 24.6 0 4.8-1.4 9.6-4.1 13.6L405.6 191.5c-32.1-33.3-75.2-55.8-123.4-61.9L350.7 15.5C356.4 5.9 366.9 0 378.1 0h109.3z"></path></svg>',
        },
        fee: {
            label: 'Compare course fees',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 320 512" fill="currentColor" aria-hidden="true"><path d="M160 0c17.7 0 32 14.3 32 32v35.7c1.6.2 3.1.4 4.7.7.4.1.7.1 1.1.2l48 8.8c17.4 3.2 28.9 19.9 25.7 37.2s-19.9 28.9-37.2 25.7l-47.5-8.7c-31.3-4.6-58.9-1.5-78.3 6.2s-27.2 18.3-29 28.1c-2 10.7-.5 16.7 1.2 20.4 1.8 3.9 5.5 8.3 12.8 13.2 16.3 10.7 41.3 17.7 73.7 26.3l2.9.8c28.6 7.6 63.6 16.8 89.6 33.8 14.2 9.3 27.6 21.9 35.9 39.5 8.5 17.9 10.3 37.9 6.4 59.2-6.9 38-33.1 63.4-65.6 76.7-13.7 5.6-28.6 9.2-44.4 11V480c0 17.7-14.3 32-32 32s-32-14.3-32-32v-34.9l-1.5-.2c-24.4-3.8-64.5-14.3-91.5-26.3-16.1-7.2-23.4-26.1-16.2-42.2s26.1-23.4 42.2-16.2c20.9 9.3 55.3 18.5 75.2 21.6 31.9 4.7 58.2 2 76-5.3 16.9-6.9 24.6-16.9 26.8-28.9 1.9-10.6.4-16.7-1.3-20.4-1.9-4-5.6-8.4-13-13.3-16.4-10.7-41.5-17.7-74-26.3l-2.8-.7c-28.7-7.6-63.7-16.9-89.7-33.9-14.2-9.3-27.5-22-35.8-39.6-8.4-17.9-10.1-37.9-6.1-59.2C23.7 116 52.3 91.2 84.8 78.3c13.3-5.3 27.9-8.9 43.2-11V32c0-17.7 14.3-32 32-32z"></path></svg>',
        },
        start: {
            label: 'Course Intake',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M128 0c17.7 0 32 14.3 32 32v32h128V32c0-17.7 14.3-32 32-32s32 14.3 32 32v32h48c26.5 0 48 21.5 48 48v48H0v-48C0 85.5 21.5 64 48 64h48V32c0-17.7 14.3-32 32-32zM0 192h448v272c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm80 64c-8.8 0-16 7.2-16 16v64c0 8.8 7.2 16 16 16h288c8.8 0 16-7.2 16-16v-64c0-8.8-7.2-16-16-16H80z"></path></svg>',
        },
        attendance: {
            label: 'Attendance mode - Hide',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M256 0a256 256 0 1 1 0 512 256 256 0 1 1 0-512zm24 120c0-13.3-10.7-24-24-24s-24 10.7-24 24v136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120z"></path></svg>',
        },
        pathway: {
            label: 'Pathway options',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 640 640" fill="currentColor" aria-hidden="true"><path d="M576 112c0-11.1-5.7-21.4-15.2-27.2-9.5-5.8-21.2-6.4-31.1-1.4L413.5 141.5 234.1 81.6c-8.1-2.7-16.8-2.1-24.4 1.7l-128 64C70.8 152.8 64 163.9 64 176v352c0 11.1 5.7 21.4 15.2 27.2 9.5 5.8 21.2 6.4 31.1 1.4l116.1-58.1 173.3 57.8c-4.3-6.4-8.5-13.1-12.6-19.9-11-18.3-21.9-39.3-30-61.8l-101.2-33.7V156.4l128 42.7v99.3c31-35.8 77-58.4 128-58.4 22.6 0 44.2 4.4 64 12.5V112zM512 288c-66.3 0-120 52.8-120 117.9 0 68.9 64.1 150.4 98.6 189.3 11.6 13 31.3 13 42.9 0 34.5-38.9 98.6-120.4 98.6-189.3 0-65.1-53.7-117.9-120-117.9zm0 80c22.1 0 40 17.9 40 40s-17.9 40-40 40-40-17.9-40-40 17.9-40 40-40z"></path></svg>',
        },
        status: {
            label: 'Hide closed courses - Hide',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 576 512" fill="currentColor" aria-hidden="true"><path d="M88.7 223.8 0 375.8V96C0 60.7 28.7 32 64 32h117.5c17 0 33.3 6.7 45.3 18.7l26.5 26.5c12 12 28.3 18.7 45.3 18.7H416c35.3 0 64 28.7 64 64v32H144c-22.8 0-43.8 12.1-55.3 31.9zm27.6 16.1c5.8-10 16.3-15.9 27.7-15.9h400c11.5 0 22 6.1 27.7 16.1s5.7 22.2-.1 32.1l-112 192c-5.8 10-16.3 15.8-27.7 15.8H32c-11.5 0-22-6.1-27.7-16.1s-5.7-22.2.1-32.1l112-192z"></path></svg>',
        },
        content: {
            label: 'Search areas of study - Hide',
            icon: '<svg class="h-5 w-5 text-slate-900" viewBox="0 0 384 512" fill="currentColor" aria-hidden="true"><path d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160 128L256 0H64zm48 256H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16zm0 64H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"></path></svg>',
        },
    };

    const levelLabels = {
        TBH: 'Bachelor (Honours)',
        TBP: 'Bachelor',
        TAB: 'Associate Degree',
        TXD: 'Diploma',
        TOA: 'Undergraduate Certificate',
        TCM: 'Bachelor/Master',
        TT1: 'Certificate I',
        TT2: 'Certificate II',
        TT3: 'Certificate III',
        TT4: 'Certificate IV',
    };

    const feeLabels = {
        INT: 'International',
    };

    const attendanceLabels = {
        full_time: 'Full-time',
        part_time: 'Part-time',
        online: 'Online or Distance',
    };

    const monthLabels = {
        '01': 'January',
        '02': 'February',
        '03': 'March',
        '04': 'April',
        '05': 'May',
        '06': 'June',
        '07': 'July',
        '08': 'August',
        '09': 'September',
        '10': 'October',
        '11': 'November',
        '12': 'December',
    };

    const courseLevelOrder = ['TBH', 'TBP', 'TAB', 'TXD', 'TOA', 'TCM', 'TT1', 'TT2', 'TT3', 'TT4'];
    const feeOrder = ['INT'];
    const attendanceOrder = ['full_time', 'part_time', 'online'];

    const sidebar = document.getElementById('course-search-sidebar');
    const toolbar = document.getElementById('course-search-toolbar');
    const content = document.getElementById('course-search-content');
    const form = document.getElementById('courseSearchForm');
    const searchInput = document.getElementById('searchInput');
    const searchTypeToggle = document.getElementById('searchTypeToggle');
    const searchTypeMenu = document.getElementById('searchTypeMenu');
    const searchTypeChevron = document.getElementById('searchTypeChevron');

    const state = {
        searchType: @json($searchType),
        search: '',
        page: 1,
        size: 10,
        compact: false,
        filters: {
            inst: [],
            fos: [],
            level: [],
            fee: [],
            start: [],
            attendance: [],
            pathway: false,
            status: false,
            content: false,
        },
        filtersData: null,
        results: null,
        campusMap: {},
        loadingFilters: true,
        loadingResults: false,
        error: '',
        requestToken: 0,
        openAccordions: new Set(),
    };

    let campusPromise;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function sanitizeUrl(value) {
        const url = String(value ?? '').trim();
        if (url.includes('uac.edu.au')) {
            return '#';
        }
        return url;
    }

    function unique(values) {
        return [...new Set(values)];
    }

    function getArrayParam(params, key, fallbackKey = null) {
        const values = params.getAll(key);

        if (values.length > 0) {
            return unique(values.filter(Boolean));
        }

        if (fallbackKey) {
            return unique(params.getAll(fallbackKey).filter(Boolean));
        }

        return [];
    }

    function parseStateFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const size = Number.parseInt(params.get('size') || '', 10);
        const page = Number.parseInt(params.get('page') || '', 10);

        state.search = (params.get('search') || params.get('query') || '').trim();
        state.page = Number.isFinite(page) && page > 0 ? page : 1;
        state.size = allowedSizes.includes(size) ? size : 10;
        state.compact = params.get('compact') === '1';
        state.filters.inst = getArrayParam(params, 'inst', 'p');
        state.filters.fos = getArrayParam(params, 'fos');
        state.filters.level = getArrayParam(params, 'level', 'courseLevel');
        state.filters.fee = getArrayParam(params, 'fee');
        state.filters.start = getArrayParam(params, 'start', 'mm');
        state.filters.attendance = getArrayParam(params, 'attendance', 'moa');
        state.filters.pathway = getArrayParam(params, 'pathway', 'courseStageFlag').includes('P');
        state.filters.status = getArrayParam(params, 'status').includes('C');
        state.filters.content = params.get('content') === '1' || params.get('content') === 'true';
    }

    function buildBrowserParams() {
        const params = new URLSearchParams();

        if (state.search) {
            params.set('search', state.search);
        }

        state.filters.inst.forEach((value) => params.append('inst', value));
        state.filters.fos.forEach((value) => params.append('fos', value));
        state.filters.level.forEach((value) => params.append('level', value));
        state.filters.fee.forEach((value) => params.append('fee', value));
        state.filters.start.forEach((value) => params.append('start', value));
        state.filters.attendance.forEach((value) => params.append('attendance', value));

        if (state.filters.pathway) {
            params.append('pathway', 'P');
        }

        if (state.filters.status) {
            params.append('status', 'C');
        }

        if (state.filters.content) {
            params.set('content', '1');
        }

        if (state.page > 1) {
            params.set('page', String(state.page));
        }

        if (state.size !== 10) {
            params.set('size', String(state.size));
        }

        if (state.compact) {
            params.set('compact', '1');
        }

        return params;
    }

    function buildApiParams(includePagination = true) {
        const params = buildBrowserParams();

        if (!includePagination) {
            params.delete('page');
            params.delete('size');
            params.delete('compact');
        }

        return params;
    }

    function buildApiUrl(baseUrl, params) {
        const nextParams = new URLSearchParams(params);
        nextParams.set('type', state.searchType);

        return `${baseUrl}?${nextParams.toString()}`;
    }

    function buildSearchTypeUrl(type) {
        const config = searchTypeMeta[type] || searchTypeMeta.international;
        const params = new URLSearchParams();

        if (state.search) {
            params.set('search', state.search);
        }

        return params.toString() ? `${config.pagePath}?${params.toString()}` : config.pagePath;
    }

    function updateBrowserUrl() {
        const params = buildBrowserParams();
        const nextUrl = params.toString() ? `${window.location.pathname}?${params.toString()}` : window.location.pathname;
        window.history.pushState({}, '', nextUrl);
    }

    function hasActiveCriteria() {
        return Boolean(
            state.search ||
            state.filters.inst.length ||
            state.filters.fos.length ||
            state.filters.level.length ||
            state.filters.fee.length ||
            state.filters.start.length ||
            state.filters.attendance.length ||
            state.filters.pathway ||
            state.filters.status ||
            state.filters.content
        );
    }

    function hasActiveFilters() {
        return Boolean(
            state.filters.inst.length ||
            state.filters.fos.length ||
            state.filters.level.length ||
            state.filters.fee.length ||
            state.filters.start.length ||
            state.filters.attendance.length ||
            state.filters.pathway ||
            state.filters.status ||
            state.filters.content
        );
    }

    function getSelectedOptionLabel(group, value) {
        if (group === 'inst') {
            return (state.filtersData?.providers || []).find((item) => item.key === value)?.name || value;
        }

        if (group === 'fos') {
            return (state.filtersData?.fieldOfStudy || []).find((item) => item.key === value)?.name || value;
        }

        if (group === 'level') {
            return levelLabels[value] || value;
        }

        if (group === 'fee') {
            return (state.filtersData?.feeTypes || []).find((item) => item.key === value)?.name || feeLabels[value] || value;
        }

        if (group === 'start') {
            return monthLabels[value] || value;
        }

        if (group === 'attendance') {
            return attendanceLabels[value] || value;
        }

        return value;
    }

    function getActiveFilterChips() {
        const chips = [];

        ['inst', 'fos', 'level', 'fee', 'start', 'attendance'].forEach((group) => {
            state.filters[group].forEach((value) => {
                chips.push({
                    type: 'value',
                    group,
                    value,
                    label: getSelectedOptionLabel(group, value),
                });
            });
        });

        ['pathway', 'status', 'content'].forEach((group) => {
            if (state.filters[group]) {
                chips.push({
                    type: 'flag',
                    group,
                    label: filterMeta[group].label,
                });
            }
        });

        return chips;
    }

    function getTotalCount() {
        if (typeof state.results?.stats?.total === 'number') {
            return state.results.stats.total;
        }

        if (typeof state.filtersData?.target?.os === 'number') {
            return state.filtersData.target.os;
        }

        if (Array.isArray(state.filtersData?.courseStatus)) {
            return state.filtersData.courseStatus.reduce((sum, item) => sum + Number(item.count || 0), 0);
        }

        return 0;
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: {
                Accept: 'application/json',
            },
        }).then(async (response) => {
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed.');
            }

            return data;
        });
    }

    function ensureCampusData() {
        if (!campusPromise) {
            campusPromise = fetchJson(endpoints.campus)
                .then((items) => {
                    state.campusMap = (items || []).reduce((map, item) => {
                        map[`${item.providerId}-${item.campusCode}`] = item.nameShort || item.nameLong || item.campusCode;
                        return map;
                    }, {});
                })
                .catch(() => {
                    state.campusMap = {};
                });
        }

        return campusPromise;
    }

    function formatDate(value) {
        if (!value) {
            return 'TBC';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return escapeHtml(value);
        }

        return dateFormatter.format(date).replace(/ /g, '-');
    }

    function formatDuration(values) {
        if (!Array.isArray(values) || values.length === 0) {
            return 'TBC';
        }

        return unique(values).map((value) => {
            if (value === 'eqp') {
                return 'EqP';
            }

            const parts = String(value).split('_');

            if (parts.length < 3) {
                return escapeHtml(value);
            }

            return `${parts[0]}${parts[1] === 'y' ? '' : parts[1]}${String(parts[2]).toUpperCase()}`;
        }).join(' / ');
    }

    function buildLogoUrl(course) {
        if (course.isVet) {
            const primaryLogo = course.logoUrl || course.logoSourceUrl || '';
            const secondaryLogo = course.logoSourceUrl || course.logoFallbackUrl || primaryLogo;
            return {
                primary: primaryLogo,
                fallback: secondaryLogo,
            };
        }

        const svgLogo = `https://uac.edu.au/assets/images/Institution-logos/2025/${encodeURIComponent(course.providerId)}_h.svg`;
        const fallback = course.providerLogo ? `https://uac.edu.au${course.providerLogo}` : svgLogo;

        return {
            primary: svgLogo,
            fallback,
        };
    }

    function buildCourseLink(course) {
        return `${searchTypeMeta[state.searchType].detailPath}/${encodeURIComponent(course.courseUrl)}`;
    }

    function getCampusName(course) {
        const key = `${course.providerId}-${course.campusCode}`;
        return state.campusMap[key] || course.campusCode || 'Campus';
    }

    function spinnerMarkup(label = 'Loading courses...') {
        return `
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center text-slate-500 shadow-sm">
                <svg class="mx-auto mb-4 h-7 w-7 animate-spin text-[#2ca5b8]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4zm2 5.3A8 8 0 0 1 4 12H0c0 3 1.1 5.8 3 7.9l3-2.6z"></path>
                </svg>
                <p class="text-sm font-medium">${escapeHtml(label)}</p>
            </div>
        `;
    }

    function emptyNoticeMarkup(message) {
        return `
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <p class="text-lg font-semibold text-slate-700">${escapeHtml(message)}</p>
            </div>
        `;
    }

    function getOptionList(key) {
        const filtersData = state.filtersData || {};
        const selected = new Set(state.filters[key] || []);

        const vetLevelCatalog = [
            { key: 'certificate', name: 'Certificate' },
            { key: 'diploma', name: 'Diploma' },
            { key: 'advanced_diploma', name: 'Advanced Diploma' },
            { key: 'graduate_diploma', name: 'Graduate Diploma' },
            { key: 'english', name: 'English' },
            { key: 'other', name: 'Other' },
        ];

        if (key === 'inst') {
            const options = (filtersData.providers || []).map((item) => ({
                value: item.key,
                label: item.name,
                count: Number(item.count || 0),
            }));

            state.filters.inst.forEach((value) => {
                if (!options.some((option) => option.value === value)) {
                    options.push({ value, label: value, count: 0 });
                }
            });

            return options.sort((a, b) => a.label.localeCompare(b.label));
        }

        if (key === 'fos') {
            const options = (filtersData.fieldOfStudy || []).map((item) => ({
                value: item.key,
                label: item.name || item.key,
                count: Number(item.count || 0),
            }));

            state.filters.fos.forEach((value) => {
                if (!options.some((option) => option.value === value)) {
                    options.push({ value, label: value, count: 0 });
                }
            });

            return options.sort((a, b) => a.label.localeCompare(b.label));
        }

        if (key === 'level') {
            // Special handling for VET courses - use qualification categories
            if (state.searchType === 'vet') {
                const courseLevelData = filtersData.courseLevel || [];
                const counts = Object.fromEntries(courseLevelData.map((item) => [item.key, Number(item.count || 0)]));
                const allValues = unique([
                    ...vetLevelCatalog.map((item) => item.key),
                    ...courseLevelData.map((item) => item.key),
                    ...state.filters.level,
                ]);
                const values = selected.size > 0
                    ? allValues.filter((value) => selected.has(value))
                    : allValues;

                const options = values.map((value) => ({
                    value,
                    label: vetLevelCatalog.find((item) => item.key === value)?.name
                        || courseLevelData.find((item) => item.key === value)?.name
                        || value,
                    count: counts[value] || 0,
                }));

                return options;
            }

            const counts = Object.fromEntries((filtersData.courseLevel || []).map((item) => [item.key, Number(item.count || 0)]));
            const values = unique([...courseLevelOrder, ...state.filters.level]).filter((value) => counts[value] > 0 || selected.has(value));

            return values.map((value) => ({
                value,
                label: levelLabels[value] || value,
                count: counts[value] || 0,
            }));
        }

        if (key === 'fee') {
            if (state.searchType === 'vet') {
                const feeData = filtersData.feeTypes || [];
                const counts = Object.fromEntries(feeData.map((item) => [item.key, Number(item.count || 0)]));
                const values = unique([...feeData.map((item) => item.key), ...state.filters.fee]).filter((value) => counts[value] > 0 || selected.has(value));

                return values.map((value) => ({
                    value,
                    label: feeData.find((item) => item.key === value)?.name || value,
                    count: counts[value] || 0,
                }));
            }

            const counts = Object.fromEntries((filtersData.feeTypes || []).map((item) => [item.key, Number(item.count || 0)]));
            const ignoredFees = ['DFEE', 'CSP'];
            const values = unique([...feeOrder, ...state.filters.fee]).filter((value) => !ignoredFees.includes(value) && (counts[value] > 0 || selected.has(value)));

            return values.map((value) => ({
                value,
                label: feeLabels[value] || value,
                count: counts[value] || 0,
            }));
        }

        if (key === 'start') {
            const counts = Object.fromEntries((filtersData.startMonths || []).map((item) => [item.key, Number(item.count || 0)]));
            const values = unique([...Object.keys(monthLabels), ...state.filters.start]).filter((value) => counts[value] > 0 || selected.has(value));

            return values.map((value) => ({
                value,
                label: monthLabels[value] || value,
                count: counts[value] || 0,
            }));
        }

        if (key === 'attendance') {
            const counts = Object.fromEntries((filtersData.modeOfAttendance || []).map((item) => [item.key, Number(item.count || 0)]));
            const values = unique([...attendanceOrder, ...state.filters.attendance]).filter((value) => counts[value] > 0 || selected.has(value));

            return values.map((value) => ({
                value,
                label: attendanceLabels[value] || value,
                count: counts[value] || 0,
            }));
        }

        return [];
    }

    function renderAccordion(key) {
        const meta = filterMeta[key];
        const options = getOptionList(key);
        const isOpen = state.openAccordions.has(key);

        return `
            <div class="border-b border-slate-100 last:border-b-0">
                <button
                    type="button"
                    data-accordion="${key}"
                    class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition hover:bg-slate-50/50"
                >
                    <span class="flex min-w-0 items-center gap-3 text-[0.88rem] font-semibold text-slate-700">
                        <span class="shrink-0 transition-transform ${isOpen ? 'text-[#2ca5b8]' : 'text-slate-400'}">${meta.icon.replace('h-5 w-5', 'h-4 w-4')}</span>
                        <span class="truncate">${meta.label}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform ${isOpen ? 'rotate-180 text-[#2ca5b8]' : ''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                ${isOpen ? `
                    <div class="space-y-1 px-4 pb-4 pt-1">
                        ${options.length ? options.map((option) => `
                            <label class="group flex cursor-pointer items-center justify-between gap-3 rounded-lg px-2 py-1.5 transition hover:bg-slate-50">
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        class="h-3.5 w-3.5 rounded border-slate-300 text-[#2ca5b8] accent-[#2ca5b8] focus:ring-[#2ca5b8]"
                                        data-filter-group="${key}"
                                        data-filter-value="${escapeHtml(option.value)}"
                                        ${state.filters[key].includes(option.value) ? 'checked' : ''}
                                    />
                                    <span class="min-w-0 text-[0.82rem] text-slate-600 transition group-hover:text-slate-900">${escapeHtml(option.label)}</span>
                                </span>
                                <span class="shrink-0 text-[0.75rem] font-medium text-slate-400 group-hover:text-slate-500">${numberFormatter.format(option.count)}</span>
                            </label>
                        `).join('') : '<p class="px-2 text-xs text-slate-400">No options available.</p>'}
                    </div>
                ` : ''}
            </div>
        `;
    }

    function renderToggleRow(key, checked) {
        const meta = filterMeta[key];

        return `
            <label class="flex cursor-pointer items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 transition hover:bg-slate-50/50 last:border-b-0">
                <span class="flex min-w-0 items-center gap-3 text-[0.88rem] font-semibold text-slate-700">
                    <span class="shrink-0 text-slate-400">${meta.icon.replace('h-5 w-5', 'h-4 w-4')}</span>
                    <span class="truncate">${meta.label}</span>
                </span>
                <input
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-[#2ca5b8] accent-[#2ca5b8] focus:ring-[#2ca5b8]"
                    data-toggle-flag="${key}"
                    ${checked ? 'checked' : ''}
                />
            </label>
        `;
    }

    function renderSidebar() {
        const total = getTotalCount();
        const countMarkup = total > 0 ? `<span class="ml-1 text-[#2ca5b8] font-bold">${numberFormatter.format(total)}</span>` : '<span class="ml-1 text-slate-300">...</span>';
        const filtersActive = hasActiveFilters();

        sidebar.innerHTML = `
            <div class="space-y-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-400">
                        Filters ${countMarkup}
                    </h2>

                    ${filtersActive ? `
                        <button
                            type="button"
                            data-clear-filters="1"
                            class="text-[0.78rem] font-bold text-[#e74c3c] transition hover:text-[#c0392b] hover:underline"
                        >
                            Reset all
                        </button>
                    ` : ''}
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200/5">
                    ${renderAccordion('inst')}
                    ${renderAccordion('fos')}
                    ${renderAccordion('level')}
                    ${renderAccordion('fee')}
                    ${renderAccordion('start')}
                    ${renderToggleRow('pathway', state.filters.pathway)}
                </div>

                ${state.searchType !== 'vet' ? `
                    <div class="rounded-2xl border border-[#bfe2e5]/50 bg-[#f0f9fa] p-4">
                        <div class='flex items-start gap-3'>
                            <svg class='h-4 w-4 mt-0.5 shrink-0 text-[#1a3a5c]' viewBox='0 0 24 24' fill='none' stroke="currentColor" stroke-width="2.5"><circle cx='12' cy='12' r='9'/><path d='M12 8h.01M11 12h1v4h1'/></svg>
                            <p class="text-[0.8rem] leading-relaxed text-[#3b5b71]">
                                Course availability and details are subject to change throughout the admissions cycle.
                            </p>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    function getTileOptions() {
        return getOptionList('fos').sort((a, b) => a.label.localeCompare(b.label));
    }

    function getVetCategoryOptions() {
        const categories = [
            { key: 'certificate', name: 'Certificate' },
            { key: 'diploma', name: 'Diploma' },
            { key: 'advanced_diploma', name: 'Advanced Diploma' },
            { key: 'graduate_diploma', name: 'Graduate Diploma' },
            { key: 'english', name: 'English' },
            { key: 'other', name: 'Other' },
        ];

        const categoryCounts = {};
        if (state.filtersData?.courseLevel) {
            state.filtersData.courseLevel.forEach((cat) => {
                categoryCounts[cat.key] = Number(cat.count || 0);
            });
        }

        return categories.map((cat) => ({
            value: cat.key,
            label: cat.name,
            count: categoryCounts[cat.key] || 0,
        }));
    }

    function renderEmptyState() {
        let tiles = getTileOptions();
        const vetCategories = state.searchType === 'vet' ? getVetCategoryOptions() : [];

        return `
            <div class="space-y-8">
                <p class="text-[0.82rem] text-slate-500">
                    Use the search bar above or select a field of study filter below.
                </p>

                ${tiles.length || vetCategories.length ? `
                    <div class="grid gap-5 lg:grid-cols-3">
                        ${tiles.length ? tiles.map((tile) => `
                            <button
                                type="button"
                                data-fos-tile="${escapeHtml(tile.value)}"
                                class="flex items-center gap-3 rounded-xl border border-transparent bg-white px-3 py-3 text-left shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#2ca5b8]"
                            >
                                <span class="text-[0.82rem] font-semibold text-slate-700">${escapeHtml(tile.label)}</span>
                            </button>
                        `).join('') : ''}

                        ${!tiles.length && vetCategories.length ? vetCategories.map((category) => `
                            <button
                                type="button"
                                data-category-filter="${escapeHtml(category.value)}"
                                class="group flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-left shadow-sm transition hover:border-[#2ca5b8] hover:bg-[#e8f3f8]/30"
                            >
                                <span class="text-[0.88rem] font-semibold text-slate-700">${escapeHtml(category.label)}</span>
                                ${category.count > 0 ? `<span class="text-[0.75rem] font-medium text-slate-400">${numberFormatter.format(category.count)}</span>` : ''}
                            </button>
                        `).join('') : ''}
                    </div>
                ` : (state.loadingFilters ? spinnerMarkup('Loading fields of study...') : emptyNoticeMarkup('No fields of study available.'))}
            </div>
        `;
    }

    function getPageNumbers(totalPages) {
        if (totalPages <= 5) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        let start = Math.max(1, state.page - 2);
        let end = Math.min(totalPages, start + 4);
        start = Math.max(1, end - 4);

        return Array.from({ length: end - start + 1 }, (_, index) => start + index);
    }

    function getTotalPages() {
        return Math.max(1, Math.ceil((state.results?.stats?.total || 0) / state.size));
    }

    function renderPaginationControls(wrapperClass = '') {
        const totalPages = getTotalPages();
        const pages = getPageNumbers(totalPages);

        return `
            <div class="${wrapperClass}">
                <button
                    type="button"
                    class="border border-slate-300 px-3 py-1.5 font-semibold ${state.page === 1 ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white text-slate-800 hover:bg-slate-50'}"
                    data-page="${state.page - 1}"
                    ${state.page === 1 ? 'disabled' : ''}
                >
                    Prev.
                </button>

                ${pages.map((page) => `
                    <button
                        type="button"
                        data-page="${page}"
                        class="border px-3 py-1.5 font-semibold ${page === state.page ? 'border-slate-400 bg-slate-400 text-white' : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-50'}"
                    >
                        ${page}
                    </button>
                `).join('')}

                <button
                    type="button"
                    class="border border-slate-300 px-3 py-1.5 font-semibold ${state.page >= totalPages ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white text-slate-800 hover:bg-slate-50'}"
                    data-page="${state.page + 1}"
                    ${state.page >= totalPages ? 'disabled' : ''}
                >
                    Next
                </button>
            </div>
        `;
    }

    function renderToolbarMarkup(wrapperClass = '') {
        return `
            <div class="${wrapperClass} flex flex-col gap-4 text-[0.82rem] text-slate-700 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-center gap-2 text-[0.82rem]">
                    <span>Sort:</span>
                    <span class="font-bold text-slate-900">Relevance</span>
                    <svg class="h-3 w-3 text-slate-900" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 10l5 5 5-5H7z"></path>
                    </svg>
                </div>

                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:gap-8">
                    ${renderPaginationControls('flex flex-wrap items-center gap-2 text-[0.82rem]')}

                    <div class="flex flex-wrap items-center gap-2 text-[0.82rem]">
                        <span>Entries/Page:</span>
                        ${allowedSizes.map((size) => `
                            <button
                                type="button"
                                data-size="${size}"
                                class="border px-3 py-1.5 font-bold ${size === state.size ? 'border-slate-400 bg-slate-400 text-white' : 'border-slate-300 bg-white text-slate-800 hover:bg-slate-50'}"
                            >
                                ${size}
                            </button>
                        `).join('')}
                    </div>

                    <button
                        type="button"
                        data-toggle-compact="1"
                        class="inline-flex items-center gap-2 text-[0.82rem] font-medium text-slate-800 transition hover:text-slate-600"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M3 9h4V5H3v4zm0 10h4v-4H3v4zm14 0h4v-4h-4v4zm-7 0h4v-4h-4v4zm0-10h4V5h-4v4zm7 0h4V5h-4v4z"></path>
                        </svg>
                        <span>${state.compact ? 'Expanded view' : 'Compact view'}</span>
                    </button>
                </div>
            </div>
        `;
    }

    function renderActiveFiltersBar() {
        const chips = getActiveFilterChips();

        if (!chips.length) {
            return '';
        }

        return `
            <div class="rounded-sm border border-slate-200 bg-slate-100 px-4 py-3">
                <div class="flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="shrink-0 text-[0.85rem] font-semibold text-slate-800">Active filters:</div>

                    <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                        ${chips.map((chip) => `
                            <button
                                type="button"
                                class="inline-flex max-w-full items-center gap-2 bg-slate-200 px-3 py-1 text-[0.78rem] font-medium text-slate-600 transition hover:bg-slate-300 hover:text-slate-900"
                                data-remove-filter-group="${chip.group}"
                                ${chip.type === 'value' ? `data-remove-filter-value="${escapeHtml(chip.value)}"` : 'data-remove-filter-flag="1"'}
                            >
                                <span class="truncate">${escapeHtml(chip.label)}</span>
                                <span aria-hidden="true">&times;</span>
                            </button>
                        `).join('')}

                        <button
                            type="button"
                            data-clear-filters="1"
                            class="inline-flex items-center justify-center text-[0.82rem] font-bold text-[#e74c3c] transition hover:text-[#c0392b] hover:underline px-2"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function renderToolbar() {
        if (!hasActiveCriteria()) {
            toolbar.innerHTML = '';
            return;
        }

        toolbar.innerHTML = `
            <div class="space-y-4">
                ${renderActiveFiltersBar()}
                ${renderToolbarMarkup('')}
            </div>
        `;
    }

    function renderResultCard(course) {
        // Custom rendering for VET courses
        if (course.isVet) {
            return renderVetCourseCard(course);
        }

        const logo = buildLogoUrl(course);
        const campusName = getCampusName(course);
        const duration = formatDuration(course.duration);
        const offerings = Array.isArray(course.offerings) ? course.offerings : [];
        const startDates = unique(offerings.map((item) => item.startDate).filter(Boolean));
        const closingDates = unique(offerings.map((item) => item.finalClosing).filter(Boolean));
        const statusCode = String(course.status || course.courseStatus || '').toUpperCase();
        const isCancelled = statusCode === 'W';
        const compactClass = state.compact ? 'gap-4 px-4 py-5 sm:grid-cols-[120px_minmax(0,1fr)_120px_120px]' : 'gap-6 px-5 py-6 sm:grid-cols-[170px_minmax(0,1fr)_160px_160px]';
        const logoFrameClass = state.compact
            ? 'mt-2 flex h-[72px] w-[120px] items-center justify-center'
            : 'mt-2 flex h-[88px] w-[150px] items-center justify-center';
        const articleClass = isCancelled
            ? 'border-t-[2px] border-slate-200 bg-white/50 shadow-sm rounded-xl'
            : 'group border-t-[2px] border-[#2ca5b8]/40 bg-white shadow-sm rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#1a3a5c]/5 hover:ring-1 hover:ring-[#2ca5b8]/20';
        const titleClass = isCancelled
            ? 'min-w-0 text-[0.95rem] font-bold leading-snug text-slate-400 line-through decoration-1 sm:text-[1.05rem]'
            : 'min-w-0 text-[0.95rem] font-bold leading-snug text-[#1a3a5c] transition hover:text-[#2ca5b8] hover:underline group-hover:text-[#1e6fa0] group-hover:underline sm:text-[1.05rem]';
        const logoImageClass = isCancelled
            ? 'h-full w-full object-contain object-left grayscale opacity-50'
            : 'h-full w-full object-contain object-left';
        const detailsBorderClass = isCancelled ? 'border-slate-200' : 'border-[#2ca5b8]/30';
        const providerTextClass = isCancelled ? 'text-[0.85rem] text-slate-400' : 'text-[0.88rem] font-semibold text-slate-700';
        const campusRowClass = isCancelled
            ? 'mt-3 flex items-center gap-2 text-[0.82rem] font-semibold text-slate-300'
            : 'mt-3 flex items-center gap-2 text-[0.82rem] font-medium text-[#1e6fa0]';
        const campusIconClass = isCancelled ? 'h-3.5 w-3.5 text-slate-300' : 'h-3.5 w-3.5 text-[#e74c3c]';
        const metaRowClass = isCancelled
            ? 'mt-3 flex flex-wrap items-center gap-5 text-[0.82rem] text-slate-300'
            : 'mt-3 flex flex-wrap items-center gap-5 text-[0.82rem] text-slate-500';
        const durationClass = isCancelled
            ? 'inline-flex items-center gap-1 font-bold text-slate-400'
            : 'inline-flex items-center gap-1 font-bold text-slate-800';
        const statusMarkup = isCancelled ? `
            <div class="mb-2 flex flex-wrap items-center gap-2 text-[0.82rem] font-bold uppercase tracking-wide text-slate-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="8.5"></circle>
                    <path d="M8.5 8.5 15.5 15.5"></path>
                </svg>
                <span>This course has been cancelled</span>
            </div>
        ` : '';
        const rightColumnMarkup = isCancelled ? `
            <div class="mt-2 flex min-h-[118px] items-start justify-start sm:col-span-2 sm:justify-end">
                <span class="inline-flex items-center justify-center bg-slate-300 px-4 py-2 text-[0.95rem] font-bold uppercase tracking-wide text-white">
                    Cancelled
                </span>
            </div>
        ` : `
            <div class="mt-2 grid min-h-[100px] content-start rounded-lg bg-[#e8f3f8]/50 px-3 py-2.5 text-right sm:text-left">
                <div class="text-[0.65rem] font-bold uppercase tracking-widest text-[#1a3a5c]/50">Course starts</div>
                <div class="mt-2 space-y-1 text-[0.82rem] font-semibold text-[#1a3a5c]">
                    ${startDates.length ? startDates.map((date) => `<div>${formatDate(date)}</div>`).join('') : '<div>TBC</div>'}
                </div>
            </div>

            <div class="mt-2 grid min-h-[100px] content-start rounded-lg bg-slate-50 px-3 py-2.5 text-right sm:text-left">
                <div class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">Final closing</div>
                <div class="mt-2 space-y-1 text-[0.82rem] font-bold text-slate-600">
                    ${closingDates.length ? closingDates.map((date) => `<div>${formatDate(date)}</div>`).join('') : '<div>TBC</div>'}
                </div>
            </div>
        `;

        return `
            <article class="${articleClass}">
                <div class="${compactClass} grid items-start">
                    <div class="sm:col-span-full">
                        <div class="flex flex-wrap items-start gap-3 text-slate-400">
                                <div class="min-w-0 flex-1">
                                ${statusMarkup}
                                <a
                                    href="${buildCourseLink(course)}"
                                    class="${titleClass}"
                                >
                                    ${escapeHtml(course.title)}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="${logoFrameClass}">
                        <img
                            src="${escapeHtml(logo.primary)}"
                            alt="${escapeHtml(course.providerName)}"
                            class="${logoImageClass}"
                            onerror="this.onerror=null;this.src='${escapeHtml(logo.fallback)}';"
                        />
                    </div>

                    <div class="mt-2 border-l-2 ${detailsBorderClass} pl-5">
                        <div class="${providerTextClass}">${escapeHtml(course.providerName)}</div>
                        <div class="${campusRowClass}">
                            <svg class="${campusIconClass}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6 a2.5 2.5 0 0 1 0 5.5z"></path>
                            </svg>
                            <span>${escapeHtml(campusName)}</span>
                        </div>
                        <div class="${metaRowClass}">
                            <span>${escapeHtml(course.courseCode)}</span>
                            <span class="${durationClass}">
                                ${escapeHtml(duration)}
                                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    ${rightColumnMarkup}
                </div>
            </article>
        `;
    }

    function renderVetCourseCard(course) {
        const articleClass = 'group border-t-[2px] border-[#2ca5b8]/40 bg-white shadow-sm rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-[#1a3a5c]/5 hover:ring-1 hover:ring-[#2ca5b8]/20';
        const compactClass = state.compact ? 'gap-4 px-4 py-5 sm:grid-cols-[minmax(0,1fr)_360px] lg:grid-cols-[minmax(0,1fr)_420px]' : 'gap-6 px-5 py-6 sm:grid-cols-[minmax(0,1fr)_360px] lg:grid-cols-[minmax(0,1fr)_420px]';
        const campusName = Array.isArray(course.availableCities) && course.availableCities.length
            ? course.availableCities.join(' / ')
            : getCampusName(course);
        const logo = buildLogoUrl(course);
        const logoBgClass = course.providerKey === 'mit'
            ? 'bg-[#0f4d92]'
            : (course.providerKey === 'igi' ? 'bg-black' : 'bg-white');

        return `
            <article class="${articleClass}">
                <div class="${compactClass} grid items-start gap-8">
                    <div class="min-w-0">
                        <div class="grid gap-5 sm:grid-cols-[220px_minmax(0,1fr)] sm:items-start">
                            <div class="flex h-[110px] items-center justify-start sm:justify-center">
                                ${logo.primary ? `
                                    <img
                                        src="${escapeHtml(logo.primary)}"
                                        alt="${escapeHtml(course.providerName || 'Provider logo')}"
                                        class="h-auto w-auto max-h-[116px] max-w-[260px] rounded-xl border border-slate-200 ${logoBgClass} object-contain p-1"
                                        onerror="${logo.fallback ? `this.onerror=null;this.src='${escapeHtml(logo.fallback)}';` : 'this.style.display=\'none\';'}"
                                    />
                                ` : `
                                    <div class="inline-flex h-[92px] w-[210px] items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-center text-[0.8rem] font-semibold text-slate-500">
                                        ${escapeHtml(course.providerName || 'Institution')}
                                    </div>
                                `}
                            </div>

                            <div class="min-w-0 border-l-2 border-[#2ca5b8]/30 pl-4 sm:pl-5">
                                <div class="text-[0.84rem] font-semibold text-slate-700">${escapeHtml(course.providerName || 'Institution')}</div>

                                <a
                                    href="${buildCourseLink(course)}"
                                    class="mt-2 block min-w-0 text-[0.95rem] font-bold leading-snug text-[#1a3a5c] transition hover:text-[#2ca5b8] hover:underline group-hover:text-[#1e6fa0] group-hover:underline sm:text-[1.05rem]"
                                >
                                    ${escapeHtml(course.title)}
                                </a>

                                <div class="mt-4 flex flex-wrap items-center gap-4 text-[0.82rem] text-slate-500">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4 text-[#2ca5b8]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"></path>
                                        </svg>
                                        ${escapeHtml(campusName)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-[#f8fbfd] p-4">
                            <div class="text-[0.75rem] uppercase tracking-widest text-slate-400">Fees</div>
                            <div class="mt-2 grid gap-2 text-[0.85rem] text-slate-600">
                                <div>Enrollment: ${escapeHtml(course.enrollmentFee || 'N/A')}</div>
                                <div>Material: ${escapeHtml(course.materialFee || 'N/A')}</div>
                                <div>Tuition: ${escapeHtml(course.tuitionFee || 'N/A')}</div>
                                <div>Promo: ${escapeHtml(course.promoFee || 'N/A')}</div>
                            </div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <div class="text-[0.75rem] uppercase tracking-widest text-slate-400">Duration</div>
                            <div class="mt-2 text-[0.85rem] text-slate-600 font-semibold">
                                ${escapeHtml(course.duration || 'N/A')}
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        `;
    }

    function renderCategoryButtons() {
        if (state.searchType !== 'vet' || state.filters.level.length > 0 || state.search !== '') {
            return '';
        }

        const vetCategories = [
            { key: 'certificate', name: 'Certificate' },
            { key: 'diploma', name: 'Diploma' },
            { key: 'advanced_diploma', name: 'Advanced Diploma' },
            { key: 'graduate_diploma', name: 'Graduate Diploma' },
            { key: 'english', name: 'English' },
            { key: 'other', name: 'Other' },
        ];
        
        const categoryCounts = {};
        if (state.filtersData?.courseLevel) {
            state.filtersData.courseLevel.forEach((cat) => {
                categoryCounts[cat.key] = cat.count;
            });
        }
        
        return `
            <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                ${vetCategories.map((cat) => `
                    <button
                        type="button"
                        data-category-filter="${cat.key}"
                        class="group flex items-center justify-between gap-3 rounded-xl border ${state.filters.level.includes(cat.key) ? 'border-[#2ca5b8] bg-[#e8f3f8]/50' : 'border-slate-200 bg-white'} px-4 py-3 text-left shadow-sm transition hover:border-[#2ca5b8] hover:bg-[#e8f3f8]/30"
                    >
                        <span class="text-[0.88rem] font-semibold ${state.filters.level.includes(cat.key) ? 'text-[#1a3a5c]' : 'text-slate-700'}">${escapeHtml(cat.name)}</span>
                        ${categoryCounts[cat.key] !== undefined ? `<span class="text-[0.75rem] font-medium ${state.filters.level.includes(cat.key) ? 'text-[#2ca5b8]' : 'text-slate-400'}">${numberFormatter.format(categoryCounts[cat.key])}</span>` : ''}
                    </button>
                `).join('')}
            </div>
        `;
    }

    function renderContent() {
        if (state.error) {
            content.innerHTML = `
                <div class="rounded-2xl border border-red-200 bg-red-50 px-6 py-5 text-red-700 shadow-sm">
                    ${escapeHtml(state.error)}
                </div>
            `;
            return;
        }

        if (!hasActiveCriteria()) {
            content.innerHTML = renderEmptyState();
            return;
        }

        if (state.loadingResults) {
            content.innerHTML = spinnerMarkup('Loading courses...');
            return;
        }

        const results = state.results?.results || [];

        if (!results.length) {
            if (state.searchType === 'vet') {
                content.innerHTML = `
                    <div class="space-y-5">
                        ${renderCategoryButtons()}
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-8 text-center shadow-sm">
                            <svg class="mx-auto mb-4 h-12 w-12 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 8h.01M11 12h1v4h1"></path>
                            </svg>
                            <p class="text-lg font-semibold text-amber-900 mb-2">No Results Found</p>
                            <p class="text-sm text-amber-700">No courses match your search criteria. Please check back later or select a different course type.</p>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = emptyNoticeMarkup('No courses found matching your search and filters.');
            }
            return;
        }

        content.innerHTML = `
            <div class="space-y-5">
                ${renderCategoryButtons()}
                <div class="space-y-5">
                    ${results.map((course) => renderResultCard(course)).join('')}
                </div>

                <div class="border-t border-slate-200 pt-6">
                    ${renderToolbarMarkup('text-[0.96rem]')}
                </div>
            </div>
        `;
    }

    function renderAll() {
        renderSidebar();
        renderToolbar();
        renderContent();
    }

    function setSearchInput() {
        searchInput.value = state.search;
    }

    async function refresh({ updateUrl = true } = {}) {
        if (updateUrl) {
            updateBrowserUrl();
        }

        const token = ++state.requestToken;
        state.error = '';
        state.loadingFilters = true;
        state.loadingResults = hasActiveCriteria();
        renderAll();

        try {
            const filtersUrl = buildApiUrl(endpoints.filters, buildApiParams(false));
            const tasks = [fetchJson(filtersUrl), ensureCampusData()];

            if (hasActiveCriteria()) {
                tasks.push(fetchJson(buildApiUrl(endpoints.search, buildApiParams(true))));
            } else {
                tasks.push(Promise.resolve(null));
            }

            const [filtersData, , resultsData] = await Promise.all(tasks);

            if (token !== state.requestToken) {
                return;
            }

            state.filtersData = filtersData;
            state.results = resultsData;
        } catch (error) {
            if (token !== state.requestToken) {
                return;
            }

            state.error = error.message || 'Unable to load course search data right now.';
            state.results = null;
        } finally {
            if (token !== state.requestToken) {
                return;
            }

            state.loadingFilters = false;
            state.loadingResults = false;
            setSearchInput();
            renderAll();
        }
    }

    function toggleArrayFilter(group, value) {
        const values = new Set(state.filters[group]);

        if (values.has(value)) {
            values.delete(value);
        } else {
            values.add(value);
        }

        state.filters[group] = [...values];
        state.page = 1;
        refresh();
    }

    function toggleFlag(key) {
        state.filters[key] = !state.filters[key];
        state.page = 1;
        refresh();
    }

    function clearFilters() {
        state.filters.inst = [];
        state.filters.fos = [];
        state.filters.level = [];
        state.filters.fee = [];
        state.filters.start = [];
        state.filters.attendance = [];
        state.filters.pathway = false;
        state.filters.status = false;
        state.filters.content = false;
        state.page = 1;
        refresh();
    }

    function removeFilterChip(group, value = null, isFlag = false) {
        if (isFlag) {
            if (!state.filters[group]) {
                return;
            }

            state.filters[group] = false;
        } else {
            state.filters[group] = state.filters[group].filter((item) => item !== value);
        }

        state.page = 1;
        refresh();
    }

    function setSearchTypeMenuOpen(isOpen) {
        searchTypeMenu.classList.toggle('hidden', !isOpen);
        searchTypeChevron.classList.toggle('rotate-180', isOpen);
        searchTypeToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    function changePage(page) {
        if (!Number.isFinite(page) || page < 1 || page === state.page) {
            return;
        }

        state.page = page;
        refresh();
    }

    function changeSize(size) {
        if (!allowedSizes.includes(size) || size === state.size) {
            return;
        }

        state.size = size;
        state.page = 1;
        refresh();
    }

    sidebar.addEventListener('click', (event) => {
        if (event.target.closest('[data-clear-filters]')) {
            clearFilters();
            return;
        }

        const accordion = event.target.closest('[data-accordion]');

        if (accordion) {
            const key = accordion.dataset.accordion;

            if (state.openAccordions.has(key)) {
                state.openAccordions.delete(key);
            } else {
                state.openAccordions.add(key);
            }

            renderSidebar();
            return;
        }
    });

    sidebar.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-filter-group]');

        if (checkbox) {
            toggleArrayFilter(checkbox.dataset.filterGroup, checkbox.dataset.filterValue);
            return;
        }

        const toggle = event.target.closest('[data-toggle-flag]');

        if (toggle) {
            toggleFlag(toggle.dataset.toggleFlag);
            return;
        }
    });

    // Add event handler for category filter buttons in content area
    content.addEventListener('click', (event) => {
        const categoryButton = event.target.closest('[data-category-filter]');

        if (categoryButton) {
            toggleArrayFilter('level', categoryButton.dataset.categoryFilter);
            return;
        }
    });

    content.addEventListener('change', (event) => {
        const checkbox = event.target.closest('[data-filter-group]');

        if (checkbox) {
            toggleArrayFilter(checkbox.dataset.filterGroup, checkbox.dataset.filterValue);
            return;
        }

        const toggle = event.target.closest('[data-toggle-flag]');

        if (toggle) {
            toggleFlag(toggle.dataset.toggleFlag);
        }
    });

    toolbar.addEventListener('click', (event) => {
        if (event.target.closest('[data-clear-filters]')) {
            clearFilters();
            return;
        }

        const removeButton = event.target.closest('[data-remove-filter-group]');

        if (removeButton) {
            removeFilterChip(
                removeButton.dataset.removeFilterGroup,
                removeButton.dataset.removeFilterValue || null,
                removeButton.dataset.removeFilterFlag === '1'
            );
            return;
        }

        const pageButton = event.target.closest('[data-page]');

        if (pageButton) {
            changePage(Number.parseInt(pageButton.dataset.page || '', 10));
            return;
        }

        const sizeButton = event.target.closest('[data-size]');

        if (sizeButton) {
            changeSize(Number.parseInt(sizeButton.dataset.size || '', 10));
            return;
        }

        if (event.target.closest('[data-toggle-compact]')) {
            state.compact = !state.compact;
            updateBrowserUrl();
            renderToolbar();
            renderContent();
        }
    });

    content.addEventListener('click', (event) => {
        if (event.target.closest('[data-clear-filters]')) {
            clearFilters();
            return;
        }

        const pageButton = event.target.closest('[data-page]');

        if (pageButton) {
            changePage(Number.parseInt(pageButton.dataset.page || '', 10));
            return;
        }

        const sizeButton = event.target.closest('[data-size]');

        if (sizeButton) {
            changeSize(Number.parseInt(sizeButton.dataset.size || '', 10));
            return;
        }

        if (event.target.closest('[data-toggle-compact]')) {
            state.compact = !state.compact;
            updateBrowserUrl();
            renderToolbar();
            renderContent();
            return;
        }

        const tile = event.target.closest('[data-fos-tile]');

        if (!tile) {
            return;
        }

        state.filters.fos = [tile.dataset.fosTile];
        state.page = 1;
        refresh();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.search = searchInput.value.trim();
        state.page = 1;
        refresh();
    });

    searchTypeToggle.addEventListener('click', (event) => {
        event.preventDefault();
        const isClosed = searchTypeMenu.classList.contains('hidden');
        setSearchTypeMenuOpen(isClosed);
    });

    searchTypeMenu.addEventListener('click', (event) => {
        const option = event.target.closest('[data-search-type]');

        if (!option) {
            return;
        }

        const nextType = option.dataset.searchType;

        if (!searchTypeMeta[nextType]) {
            return;
        }

        window.location.href = buildSearchTypeUrl(nextType);
    });

    document.addEventListener('click', (event) => {
        if (!searchTypeToggle.contains(event.target) && !searchTypeMenu.contains(event.target)) {
            setSearchTypeMenuOpen(false);
        }
    });

    window.addEventListener('popstate', () => {
        parseStateFromUrl();
        setSearchInput();
        refresh({ updateUrl: false });
    });

    parseStateFromUrl();
    state.openAccordions = new Set();
    
    // Automatically open level accordion for VET courses to show categories
    if (state.searchType === 'vet') {
        state.openAccordions.add('level');
    }
    
    setSearchInput();
    renderAll();
    refresh({ updateUrl: false });
});
</script>
@endsection
