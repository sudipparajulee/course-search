@extends('layouts.master')

@section('content')
<style>
    .uac-richtext {
        color: #334155;
        font-size: 1rem;
        line-height: 1.9;
    }

    .uac-richtext p,
    .uac-richtext ul,
    .uac-richtext ol,
    .uac-richtext table {
        margin-top: 1rem;
    }

    .uac-richtext p:first-child,
    .uac-richtext ul:first-child,
    .uac-richtext ol:first-child,
    .uac-richtext table:first-child {
        margin-top: 0;
    }

    .uac-richtext ul,
    .uac-richtext ol {
        padding-left: 1.25rem;
    }

    .uac-richtext ul {
        list-style: disc;
    }

    .uac-richtext ol {
        list-style: decimal;
    }

    .uac-richtext a {
        color: #2276a5;
        text-decoration: underline;
    }

    .uac-richtext strong {
        color: #1e293b;
        font-weight: 700;
    }
</style>

<div class="min-h-screen bg-[#f8fafb] pb-20">
    <div id="course-detail-app" class="mx-auto max-w-[1600px] px-4 py-8 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
            <svg class="mx-auto mb-4 h-8 w-8 animate-spin text-[#2ca5b8]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.4 0 0 5.4 0 12h4zm2 5.3A8 8 0 0 1 4 12H0c0 3 1.1 5.8 3 7.9l3-2.6z"></path>
            </svg>
            <p class="text-sm font-medium text-slate-500">Loading course details...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const courseId = @json($courseId);
    const currentSearchType = @json($searchType);
    const currentSearchTypeLabel = @json($searchTypeLabel);
    const isInternationalSearch = currentSearchType === 'international';
    const endpoints = {
        details: @json(url('/api/course-search/details/'.$searchType)),
        campus: @json(url('/api/course-search/campus')),
        applyInfo: @json(url('/api/course-search/apply-info')),
        search: @json($searchPagePath),
        home: @json(url('/')),
    };

    const app = document.getElementById('course-detail-app');
    const dateFormatter = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
    const currencyFormatter = new Intl.NumberFormat('en-AU');

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeText(value) {
        return String(value ?? '').replace(/\s+/g, ' ').trim();
    }

    function unique(values) {
        return [...new Set(values)];
    }

    function stripHtml(value) {
        return String(value ?? '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    }

    function sanitizeUrl(value) {
        const url = String(value ?? '').trim();
        if (url.includes('uac.edu.au')) {
            return '#';
        }
        return url;
    }

    function sanitizeHtml(html) {
        if (!html) return '';
        // Replace hrefs containing uac.edu.au with #
        // This regex looks for href="..." or href='...' where the content contains uac.edu.au
        return String(html).replace(/href=(['"])([^'"]*uac\.edu\.au[^'"]*)\1/gi, 'href=$1#$1');
    }

    function titleizeKey(value) {
        return String(value ?? '')
            .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
            .replace(/[_-]+/g, ' ')
            .replace(/\s+/g, ' ')
            .trim()
            .replace(/\b\w/g, (match) => match.toUpperCase());
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return escapeHtml(value);
        }

        return dateFormatter.format(date).replace(/ /g, '-');
    }

    function formatDuration(values) {
        if (!Array.isArray(values) || values.length === 0) {
            return '-';
        }

        return unique(values).map((value) => {
            const parts = String(value).split('_');

            if (parts.length < 3) {
                return escapeHtml(value);
            }

            return `${parts[0]}${parts[1] === 'y' ? '' : parts[1]}${String(parts[2]).toUpperCase()}`;
        }).join(' / ');
    }

    function formatMoney(value) {
        const amount = Number(value);

        if (!Number.isFinite(amount)) {
            return '-';
        }

        return `A$${currencyFormatter.format(amount)}`;
    }

    function getApplyInfoFallback() {
        return {
            available: false,
            title: 'Apply through UAC',
            message: 'Continue on the official UAC application portal.',
            portalUrl: '#',
            loginUrl: '#',
            registerUrl: '#',
            isClosed: null,
            canEmbed: false,
            xFrameOptions: null,
        };
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

    function getCampusMap(items) {
        return (items || []).reduce((map, item) => {
            map[`${item.providerId}-${item.campusCode}`] = item.nameShort || item.nameLong || item.campusCode;
            return map;
        }, {});
    }

    function getCampusName(item, providerId, campusMap) {
        const key = `${providerId}-${item.campusCode}`;
        return campusMap[key] || item.campusCode || '-';
    }

    function getLogoUrl(providerId, fallback) {
        return {
            primary: `https://uac.edu.au/assets/images/Institution-logos/2025/${encodeURIComponent(providerId)}_h.svg`,
            fallback: fallback ? `https://uac.edu.au${fallback}` : '',
        };
    }

    function getAnnualFee(item) {
        const fee = item?.courseFee?.fees?.find((entry) => entry.valid) || item?.courseFee?.fees?.[0];
        return fee?.annual_fee || null;
    }

    function renderSection(id, title, html) {
        return `
            <section id="${escapeHtml(id)}" data-detail-section="${escapeHtml(id)}" class="scroll-mt-24 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h2 class="text-[1.55rem] font-semibold text-slate-900">${escapeHtml(title)}</h2>
                <div class="uac-richtext mt-5">${sanitizeHtml(html)}</div>
            </section>
        `;
    }

    function renderAboutFeature(feature) {
        return `
            <div class="grid gap-4 sm:grid-cols-[72px_minmax(0,1fr)] sm:items-start">
                <div class="flex items-start justify-center pt-1 text-slate-500">
                    ${feature.icon}
                </div>
                <div>
                    <h3 class="text-[1.35rem] font-semibold leading-tight text-slate-900">${escapeHtml(feature.title)}</h3>
                    <div class="mt-3">${sanitizeHtml(feature.html)}</div>
                </div>
            </div>
        `;
    }

    function renderAboutNote(html) {
        return `
            <div class="rounded-2xl border border-[#2276a5]/25 bg-[#f6fbff] p-5 text-slate-700">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#2276a5] text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 8h.01M11 12h1v4h1"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                        </svg>
                    </span>
                    <div class="uac-richtext !mt-0">${sanitizeHtml(html)}</div>
                </div>
            </div>
        `;
    }

    function renderLinkList(items) {
        if (!Array.isArray(items) || !items.length) {
            return '';
        }

        return `
            <ul class="space-y-2">
                ${items.map((item) => {
                    const label = item?.label || item?.value;
                    const value = item?.value || item?.label;

                    if (!label || !value) {
                        return '';
                    }

                    return `
                        <li>
                            <a href="${escapeHtml(sanitizeUrl(value))}" target="_blank" rel="noreferrer">${escapeHtml(label)}</a>
                        </li>
                    `;
                }).join('')}
            </ul>
        `;
    }



    function setupSectionNavigation(sections) {
        const links = Array.from(app.querySelectorAll('[data-section-link]'));
        const targets = sections.map((section) => ({
            id: section.id,
            element: app.querySelector(`[data-detail-section="${section.id}"]`),
        })).filter((item) => item.element);

        if (!links.length || !targets.length) {
            return;
        }

        const setActiveSection = (activeId) => {
            links.forEach((link) => {
                const isActive = link.dataset.sectionLink === activeId;
                const dot = link.querySelector('[data-section-dot]');

                link.classList.toggle('text-[#1a3a5c]', isActive);
                link.classList.toggle('font-bold', isActive);
                link.classList.toggle('text-slate-700', !isActive);

                if (dot) {
                    dot.classList.toggle('bg-[#2ca5b8]', isActive);
                    dot.classList.toggle('bg-white', !isActive);
                }
            });
        };

        const updateActiveSection = () => {
            let activeId = targets[0].id;
            const offset = 180;

            targets.forEach((target) => {
                if (target.element.getBoundingClientRect().top <= offset) {
                    activeId = target.id;
                }
            });

            setActiveSection(activeId);
        };

        links.forEach((link) => {
            link.addEventListener('click', () => {
                setActiveSection(link.dataset.sectionLink);
            });
        });

        updateActiveSection();
        window.addEventListener('scroll', updateActiveSection, { passive: true });
        window.addEventListener('resize', updateActiveSection);
    }



    function renderDetailsPage(payload, campusMap) {
        const course = payload.course || {};
        const content = payload.contentJson || {};
        const courseDoc = payload.courseDoc || {};
        const courseList = Array.isArray(payload.courseList) ? payload.courseList : [];
        const title = normalizeText(content.courseTitle || course.title || courseDoc.title || courseId);
        const providerName = normalizeText(course.providerName || courseDoc.providerName || '');
        const providerId = course.providerId || courseDoc.providerId || '';
        const logo = getLogoUrl(providerId, course.providerLogo);
        const feeNote = course.feeNote || '';

        document.title = `${title} | ${providerName || 'Course detail'}`;

        let admissionHtml = '';

        if (content.admissionCriteria) {
            admissionHtml += content.admissionCriteria;
        }

        if (content.secondaryAdmission?.rankInfoHeading) {
            admissionHtml += `<p><strong>${content.secondaryAdmission.rankInfoHeading}</strong></p>`;
        }

        if (content.secondaryAdmission?.assumedKnowledge) {
            admissionHtml += `<p><strong>Assumed knowledge</strong></p>${content.secondaryAdmission.assumedKnowledge}`;
        }

        if (content.secondaryAdmission?.recommendedStudies) {
            admissionHtml += `<p><strong>Recommended studies</strong></p>${content.secondaryAdmission.recommendedStudies}`;
        }

        if (content.allApplicants) {
            admissionHtml += `<p><strong>All applicants</strong></p>${content.allApplicants}`;
        }

        if (content.otherApplicants) {
            admissionHtml += `<p><strong>Other applicants</strong></p>${content.otherApplicants}`;
        }

        const feesAndChargesHtml = `${
            content.feesAndCharges?.contents || course.feesAndCharges || ''
        }${
            content.feesAndCharges?.urls?.length ? renderLinkList(content.feesAndCharges.urls) : ''
        }${
            course.feesAndChargesUrls ? renderLinkList(Object.entries(course.feesAndChargesUrls).map(([value, label]) => ({ label, value }))) : ''
        }`;

        const aboutDetailConfig = {
            areasOfStudy: {
                title: 'Areas of study',
                icon: `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M3 6.5C3 5.7 3.7 5 4.5 5h4.2c1.1 0 2.2.3 3.1.9l.2.2.2-.2c.9-.6 2-.9 3.1-.9h4.2c.8 0 1.5.7 1.5 1.5V18c0 .6-.4 1-1 1h-4.7c-1 0-2 .3-2.8.9l-.5.4-.5-.4c-.8-.6-1.8-.9-2.8-.9H4c-.6 0-1-.4-1-1V6.5Z"></path>
                        <path d="M12 6.2V19.5"></path>
                    </svg>
                `,
            },
            careerOpportunities: {
                title: 'Career opportunities',
                icon: `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M9 7V5.8c0-.9.7-1.6 1.6-1.6h2.8c.9 0 1.6.7 1.6 1.6V7"></path>
                        <path d="M4.5 7h15c.8 0 1.5.7 1.5 1.5v8c0 .8-.7 1.5-1.5 1.5h-15C3.7 18 3 17.3 3 16.5v-8C3 7.7 3.7 7 4.5 7Z"></path>
                        <path d="M3 11.5h18"></path>
                    </svg>
                `,
            },
            professionalRecognition: {
                title: 'Professional recognition',
                icon: `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"></path>
                        <path d="M19 10.8V8.5l-2-.6-.7-1.6.9-1.9-1.6-1.6-1.9.9-1.6-.7L11.5 1h-3l-.6 2-.4.2"></path>
                        <path d="M5.9 5.4 4.3 3.8l-1.6 1.6.9 1.9-.7 1.6-2 .6v2.3l2 .6.7 1.6-.9 1.9 1.6 1.6 1.9-.9 1.6.7.6 2h2.3l.6-2 1.6-.7 1.9.9 1.6-1.6-.9-1.9.7-1.6 2-.6"></path>
                    </svg>
                `,
            },
            honours: {
                title: 'Honours',
                icon: `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="m12 3 2.1 4.3 4.7.7-3.4 3.3.8 4.7-4.2-2.2-4.2 2.2.8-4.7L5.2 8l4.7-.7L12 3Z"></path>
                        <path d="M9.5 14.8V21l2.5-1.7 2.5 1.7v-6.2"></path>
                    </svg>
                `,
            },
            practicalExperience: {
                title: 'Practical experience',
                icon: `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M7 4h10"></path>
                        <path d="M9 2.5h6v3H9z"></path>
                        <path d="M12 9v4"></path>
                        <path d="M10 11h4"></path>
                        <path d="M5 6.5h14A1.5 1.5 0 0 1 20.5 8v10A1.5 1.5 0 0 1 19 19.5H5A1.5 1.5 0 0 1 3.5 18V8A1.5 1.5 0 0 1 5 6.5Z"></path>
                    </svg>
                `,
            },
        };
        const preferredAboutDetailKeys = ['areasOfStudy', 'careerOpportunities', 'professionalRecognition', 'honours'];
        const aboutDetails = content.aboutDetails || {};
        const orderedAboutDetailKeys = unique([
            ...preferredAboutDetailKeys,
            ...Object.keys(aboutDetails),
        ]);
        const aboutFeatures = orderedAboutDetailKeys.map((key) => {
            const html = aboutDetails[key];

            if (!stripHtml(html)) {
                return null;
            }

            const config = aboutDetailConfig[key] || {};

            return {
                title: config.title || titleizeKey(key),
                html,
                icon: config.icon || `
                    <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M12 3 4 7v5c0 5 3.4 8 8 9 4.6-1 8-4 8-9V7l-8-4Z"></path>
                        <path d="M9 12.5 11 14.5 15.5 10"></path>
                    </svg>
                `,
            };
        }).filter(Boolean);

        const aboutNote = content.note || courseDoc?.marketingContent?.note || '';
        const aboutSummary = content.aboutIntro || content.about || '';
        const aboutHtml = `${aboutNote ? `
            <div class="${aboutSummary || aboutFeatures.length ? 'mb-8' : ''}">
                ${renderAboutNote(aboutNote)}
            </div>
        ` : ''}${aboutSummary}${aboutFeatures.length ? `
            <div class="mt-10 space-y-10">
                ${aboutFeatures.map((feature) => renderAboutFeature(feature)).join('')}
            </div>
        ` : ''}`;

        const sections = [
            aboutSummary || aboutFeatures.length ? {
                id: 'about',
                title: 'About',
                html: aboutHtml,
            } : null,
            admissionHtml ? {
                id: 'admission-criteria',
                title: 'Admission criteria',
                html: admissionHtml,
            } : null,
            content.essential ? {
                id: 'essential-requirements',
                title: 'Essential requirements',
                html: content.essential,
            } : null,
            content.furtherInfo?.useUrl || content.furtherInfo?.contents ? {
                id: 'further-information',
                title: 'Further information',
                html: `${content.furtherInfo?.useUrl && content.furtherInfo?.url ? `
                    <p>
                        <a href="${escapeHtml(sanitizeUrl(content.furtherInfo.url))}" target="_blank" rel="noreferrer">
                            View all details of this course on the institution website
                        </a>
                    </p>
                ` : ''}${content.furtherInfo?.useUrl === false && content.furtherInfo?.contents ? content.furtherInfo.contents : ''}`,
            } : null,
            stripHtml(feesAndChargesHtml) ? {
                id: 'fees-and-charges',
                title: 'Fees and charges',
                html: feesAndChargesHtml,
            } : null,
            course.footer ? {
                id: 'course-updates',
                title: 'Course updates',
                html: course.footer,
            } : null,
        ].filter(Boolean);

        const tableRows = courseList.length ? courseList.map((item) => {
            const startDates = unique((item.offerings || []).map((entry) => entry.startDate).filter(Boolean));
            const annualFee = getAnnualFee(item);

            return `
                <tr class="border-t border-slate-200">
                    <td class="px-5 py-5 align-top">
                        <div class="flex items-start gap-3">
                            <div class="text-[1rem] font-medium text-slate-800">${escapeHtml(normalizeText(item.name || title))}</div>
                        </div>
                    </td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${escapeHtml(getCampusName(item, providerId, campusMap))}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${escapeHtml(item.courseCode || '-')}</td>
                    <td class="px-5 py-5 align-top text-[1rem] font-semibold text-slate-800">${escapeHtml(formatDuration(item.duration))}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${item.plsr != null ? escapeHtml(Number(item.plsr).toFixed(2)) : '-'}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${escapeHtml(item.cricosCourseCode || '-')}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${escapeHtml(item.feeType || '-')}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">${escapeHtml(formatMoney(annualFee))}</td>
                    <td class="px-5 py-5 align-top text-[1rem] text-slate-700">
                        ${startDates.length ? startDates.map((date) => `<div>${formatDate(date)}</div>`).join('') : '-'}
                    </td>
                </tr>
            `;
        }).join('') : `
            <tr class="border-t border-slate-200">
                <td colspan="9" class="px-5 py-10 text-center text-slate-500">No course instances were returned for this detail page.</td>
            </tr>
        `;

        app.innerHTML = `
            <div class="space-y-8">
                <nav class="flex flex-wrap items-center gap-3 text-[1rem] font-medium text-slate-700">
                    <a href="${endpoints.home}" class="underline underline-offset-4 hover:text-slate-900">Home</a>
                    <span>&gt;</span>
                    <a href="${endpoints.search}" class="underline underline-offset-4 hover:text-slate-900">${escapeHtml(currentSearchTypeLabel)} courses</a>
                </nav>

                <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_260px] lg:items-start">
                        <div>
                            <h1 class="text-[2rem] font-semibold leading-tight text-slate-900 sm:text-[2.35rem]">${escapeHtml(title)}</h1>
                            <p class="mt-3 text-[1.55rem] font-semibold text-slate-800">${escapeHtml(providerName)}</p>

                            <div class="mt-6 flex flex-wrap gap-x-8 gap-y-3 text-[1rem] text-slate-700">
                                ${course.providerCriscosId ? `<p><strong class="text-slate-900">CRICOS provider number:</strong> ${escapeHtml(course.providerCriscosId)}</p>` : ''}
                                ${course.providerTeqsaId ? `<p><strong class="text-slate-900">TEQSA provider ID:</strong> ${escapeHtml(course.providerTeqsaId)}</p>` : ''}
                            </div>
                        </div>

                        <div class="flex h-[120px] items-center justify-start lg:justify-end">
                            <img
                                src="${escapeHtml(logo.primary)}"
                                alt="${escapeHtml(providerName)}"
                                class="max-h-[110px] max-w-full object-contain object-left lg:object-right"
                                onerror="this.onerror=null;this.src='${escapeHtml(logo.fallback)}';"
                            />
                        </div>
                    </div>

                    <div class="mt-8 overflow-hidden rounded-[22px] border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-left">
                                <thead class="bg-slate-100 text-slate-900">
                                    <tr>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Course</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Campus</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Code</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Duration</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">PLSR</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">CRICOS code</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Fee type</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Indicative annual tuition fee^</th>
                                        <th class="px-5 py-5 text-[1.1rem] font-semibold">Start date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    ${feeNote ? `<div class="uac-richtext mt-5">${sanitizeHtml(feeNote)}</div>` : ''}
                </section>

                <div class="grid gap-8 lg:grid-cols-[280px_minmax(0,1fr)]">
                    <aside class="self-start lg:sticky lg:top-6">
                        <div class="space-y-5 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                                <a
                                    href="/apply"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1a3a5c] px-5 py-4 text-[1rem] font-semibold text-white shadow-sm transition hover:bg-[#2ca5b8]"
                                >
                                    <svg class='h-5 w-5' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M12 20h9'/><path d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'/></svg>
                                    Apply now
                                </a>

                            <a
                                href="${endpoints.search}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 text-[1rem] font-semibold text-[#1a3a5c] transition hover:bg-slate-100"
                            >
                                <svg class='h-4 w-4 text-[#2ca5b8]' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M19 12H5'/><path d='m12 5-7 7 7 7'/></svg>
                                Back to ${escapeHtml(currentSearchTypeLabel)} courses
                            </a>

                            <div class="space-y-3 pt-2">
                                ${sections.map((section) => `
                                    <a
                                        href="#${escapeHtml(section.id)}"
                                        data-section-link="${escapeHtml(section.id)}"
                                        class="flex items-center gap-3 text-[0.95rem] font-medium text-slate-700 transition hover:text-[#2ca5b8]"
                                    >
                                        <span data-section-dot class="inline-flex h-3.5 w-3.5 rounded-full border-2 border-[#2ca5b8] bg-white transition"></span>
                                        <span>${escapeHtml(section.title)}</span>
                                    </a>
                                `).join('')}
                            </div>
                        </div>
                    </aside>

                    <div class="space-y-8">
                        ${sections.map((section) => renderSection(section.id, section.title, section.html)).join('')}
                    </div>
                </div>

            </div>
        `;

        setupSectionNavigation(sections);
    }

    function renderError(message) {
        app.innerHTML = `
            <div class="rounded-[28px] border border-red-200 bg-red-50 px-6 py-6 text-red-700 shadow-sm">
                ${escapeHtml(message)}
            </div>
        `;
    }

    Promise.all([
        fetchJson(`${endpoints.details}/${encodeURIComponent(courseId)}`),
        fetchJson(endpoints.campus),
    ]).then(([details, campus]) => {
        renderDetailsPage(details, getCampusMap(campus));
    }).catch((error) => {
        renderError(error.message || 'Unable to load course details right now.');
    });
});
</script>
@endsection
