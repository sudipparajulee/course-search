<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

$splitQueryValues = function (Request $request, string ...$keys): array {
    $values = [];
    $rawQueryString = (string) $request->server('QUERY_STRING', '');

    if ($rawQueryString !== '') {
        foreach (explode('&', $rawQueryString) as $segment) {
            if ($segment === '') {
                continue;
            }

            [$rawKey, $rawValue] = array_pad(explode('=', $segment, 2), 2, '');
            $decodedKey = urldecode($rawKey);
            $decodedValue = trim(urldecode($rawValue));

            if ($decodedValue === '') {
                continue;
            }

            foreach ($keys as $key) {
                if ($decodedKey === $key || $decodedKey === $key.'[]') {
                    $values[] = $decodedValue;
                    break;
                }
            }
        }
    }

    if ($values !== []) {
        return array_values(array_unique($values));
    }

    foreach ($keys as $key) {
        $raw = $request->query($key);

        if ($raw === null) {
            continue;
        }

        $items = is_array($raw) ? $raw : explode(',', (string) $raw);

        foreach ($items as $item) {
            $item = trim((string) $item);

            if ($item !== '') {
                $values[] = $item;
            }
        }
    }

    return array_values(array_unique($values));
};

$buildQueryString = function (array $params): string {
    return collect($params)
        ->flatMap(function ($value, $key) {
            if ($value === null || $value === '' || $value === []) {
                return [];
            }

            $items = is_array($value) ? $value : [$value];

            return collect($items)
                ->filter(fn ($item) => $item !== null && $item !== '')
                ->map(fn ($item) => rawurlencode((string) $key).'='.rawurlencode((string) $item))
                ->all();
        })
        ->implode('&');
};

$courseSearchTypes = [
    'undergraduate' => [
        'label' => 'Undergraduate',
        'page_slug' => 'find-a-course-undergraduate',
        'heading_suffix' => 'on-campus courses',
    ],
    'postgraduate' => [
        'label' => 'Postgraduate',
        'page_slug' => 'find-a-course-postgraduate',
        'heading_suffix' => 'on-campus courses',
    ],
    'international' => [
        'label' => 'International',
        'page_slug' => 'find-a-course-international',
        'heading_suffix' => 'on-campus courses',
    ],
    'vet' => [
        'label' => 'Vet',
        'page_slug' => 'find-a-course-vet',
        'heading_suffix' => 'on-campus courses',
    ],
    // 'online' => [
    //     'label' => 'Online',
    //     'page_slug' => 'find-a-course-online',
    //     'heading_suffix' => 'courses',
    // ],
];

$resolveCourseSearchType = function (?string $type) use ($courseSearchTypes): string {
    return array_key_exists((string) $type, $courseSearchTypes) ? (string) $type : 'international';
};

$courseSearchPath = function (string $type) use ($courseSearchTypes): string {
    return '/course-search/search/'.$courseSearchTypes[$type]['page_slug'];
};

$courseSearchTypeMeta = function () use ($courseSearchTypes, $courseSearchPath): array {
    return collect($courseSearchTypes)
        ->mapWithKeys(fn ($config, $type) => [
            $type => [
                'label' => $config['label'],
                'pagePath' => $courseSearchPath($type),
                'detailPath' => '/course-search/search/'.$type.'/course',
                'headingSuffix' => $config['heading_suffix'],
            ],
        ])
        ->all();
};

$forwardJson = function (string $url) {
    try {
        $response = Http::acceptJson()
            ->timeout(20)
            ->retry(1, 200)
            ->get($url);

        return response()->json($response->json(), $response->status());
    } catch (\Throwable $exception) {
        return response()->json([
            'message' => 'Failed to reach the UAC course search service.',
            'error' => $exception->getMessage(),
        ], 502);
    }
};

$getVetCourseCategory = function (string $courseName): string {
    $name = strtolower($courseName);

    if (strpos($name, 'certificate') !== false) {
        return 'Certificate';
    }

    if (strpos($name, 'advanced diploma') !== false) {
        return 'Advanced Diploma';
    }

    if (strpos($name, 'graduate diploma') !== false) {
        return 'Graduate Diploma';
    }

    if (strpos($name, 'diploma') !== false) {
        return 'Diploma';
    }

    if (strpos($name, 'english') !== false) {
        return 'English';
    }

    return 'Other';
};

$parseVetFeeAmount = function (?string $value): ?float {
    $text = trim((string) $value);

    if ($text === '') {
        return null;
    }

    if (!preg_match('/-?\d+(?:\.\d+)?/', str_replace(',', '', $text), $matches)) {
        return null;
    }

    return (float) $matches[0];
};

$getVetFeeBucket = function (?string $tuitionFee) use ($parseVetFeeAmount): ?string {
    $text = strtolower(trim((string) $tuitionFee));

    if ($text === '') {
        return null;
    }

    if (str_contains($text, 'per level') || str_contains($text, 'per week') || str_contains($text, 'per month')) {
        return 'variable';
    }

    $amount = $parseVetFeeAmount($tuitionFee);

    if ($amount === null) {
        return null;
    }

    if ($amount <= 5000) {
        return 'fee_low';
    }

    if ($amount <= 10000) {
        return 'fee_mid';
    }

    return 'fee_high';
};

$getVetAreaKeys = function (string $courseName): array {
    $name = strtolower($courseName);
    $areas = [];

    $keywordMap = [
        'building' => 'building-construction',
        'construction' => 'building-construction',
        'carpentry' => 'trades',
        'painting' => 'trades',
        'tiling' => 'trades',
        'cabinet' => 'trades',
        'plumbing' => 'trades',
        'plastering' => 'trades',
        'stonemasonry' => 'trades',
        'ceiling' => 'trades',
        'glazing' => 'trades',
        'business' => 'business-management',
        'leadership' => 'business-management',
        'management' => 'business-management',
        'marketing' => 'marketing-communication',
        'communication' => 'marketing-communication',
        'cookery' => 'hospitality-cookery',
        'kitchen' => 'hospitality-cookery',
        'hospitality' => 'hospitality-cookery',
        'patisserie' => 'hospitality-cookery',
        'english' => 'english-language',
        'academic purposes' => 'english-language',
    ];

    foreach ($keywordMap as $needle => $areaKey) {
        if (str_contains($name, $needle)) {
            $areas[] = $areaKey;
        }
    }

    if ($areas === []) {
        $areas[] = 'other';
    }

    return array_values(array_unique($areas));
};

$getVetAreaLabel = function (string $areaKey): string {
    $labels = [
        'building-construction' => 'Building & Construction',
        'trades' => 'Trades',
        'business-management' => 'Business & Management',
        'marketing-communication' => 'Marketing & Communication',
        'hospitality-cookery' => 'Hospitality & Cookery',
        'english-language' => 'English Language',
        'other' => 'Other',
    ];

    return $labels[$areaKey] ?? $areaKey;
};

$getVetIntakeMonths = function (?string $text): array {
    $value = strtolower((string) $text);
    if (trim($value) === '') {
        return [];
    }

    $months = [
        'january' => '01',
        'february' => '02',
        'march' => '03',
        'april' => '04',
        'may' => '05',
        'june' => '06',
        'july' => '07',
        'august' => '08',
        'september' => '09',
        'october' => '10',
        'november' => '11',
        'december' => '12',
    ];

    $detected = [];
    foreach ($months as $name => $key) {
        if (str_contains($value, $name)) {
            $detected[] = $key;
        }
    }

    return array_values(array_unique($detected));
};

$getVetCsvFiles = function (): array {
    return array_filter(
        glob(public_path('*.csv')) ?: [],
        fn (string $path): bool => !in_array(basename($path), ['vet.csv', 'website.csv'], true)
    );
};

$normalizeCollegeKey = function (string $value): string {
    $normalized = (string) preg_replace('/[^a-z0-9]+/i', '', strtolower($value));
    $aliases = [
        'acbt' => 'actb',
        'actb' => 'actb',
        'northwest' => 'nortwest',
        'northwestcollege' => 'nortwest',
        'nortwestcollege' => 'nortwest',
        'pakhanem' => 'pakenham',
        'pakhanemcollege' => 'pakenham',
        'pakenhamcollege' => 'pakenham',
        'menziesinstituteoftechnology' => 'mit',
        'bentleycollege' => 'bentley',
        'lanewaycollege' => 'laneway',
        'datumcollege' => 'datum',
        'brittscollege' => 'britts',
    ];

    return $aliases[$normalized] ?? $normalized;
};

$extractWebsiteHost = function (?string $websiteUrl): ?string {
    $url = trim((string) $websiteUrl);

    if ($url === '') {
        return null;
    }

    $host = parse_url($url, PHP_URL_HOST);

    if (!is_string($host) || $host === '') {
        return null;
    }

    return $host;
};

$buildLogoFromWebsite = function (?string $websiteUrl) use ($extractWebsiteHost): ?string {
    $host = $extractWebsiteHost($websiteUrl);

    if ($host === null) {
        return null;
    }

    return 'https://logo.clearbit.com/'.rawurlencode($host);
};

$buildLogoFallbackFromWebsite = function (?string $websiteUrl) use ($extractWebsiteHost): ?string {
    $host = $extractWebsiteHost($websiteUrl);

    if ($host === null) {
        return null;
    }

    return 'https://www.google.com/s2/favicons?domain='.rawurlencode($host).'&sz=128';
};

$isTruthyCsvFlag = function (?string $value): bool {
    return in_array(strtolower(trim((string) $value)), ['yes', 'y', 'true', '1'], true);
};

$getCollegeMetadata = function () use ($normalizeCollegeKey, $buildLogoFromWebsite, $buildLogoFallbackFromWebsite, $isTruthyCsvFlag): array {
    $websitePath = public_path('website.csv');
    $mappings = [];
    $logoOverrides = [
        'britts' => 'https://brittscollege.edu.au/wp-content/uploads/2024/03/Britts-Logo.webp',
    ];

    if (!file_exists($websitePath)) {
        return $mappings;
    }

    $csvFile = fopen($websitePath, 'r');
    if ($csvFile === false) {
        return $mappings;
    }

    while (($row = fgetcsv($csvFile)) !== false) {
        $collegeName = trim((string) ($row[0] ?? ''));

        if ($collegeName === '' || strtolower($collegeName) === 'college') {
            continue;
        }

        $websiteUrl = trim((string) ($row[5] ?? ''));
        $providerKey = $normalizeCollegeKey($collegeName);
        $directLogoUrl = trim((string) ($row[7] ?? ''));

        if ($directLogoUrl === '') {
            $directLogoUrl = $logoOverrides[$providerKey] ?? '';
        }

        if ($directLogoUrl === '') {
            $directLogoUrl = (string) ($buildLogoFromWebsite($websiteUrl) ?? '');
        }

        if ($directLogoUrl === '') {
            Log::warning('Missing college logo URL mapping', [
                'providerKey' => $providerKey,
                'college' => $collegeName,
                'websiteUrl' => $websiteUrl,
            ]);
        }

        $mappings[$providerKey] = [
            'college' => $collegeName,
            'locations' => [
                'Sydney' => $isTruthyCsvFlag($row[1] ?? ''),
                'Melbourne' => $isTruthyCsvFlag($row[2] ?? ''),
                'Adelaide' => $isTruthyCsvFlag($row[3] ?? ''),
                'Brisbane' => $isTruthyCsvFlag($row[4] ?? ''),
            ],
            'websiteUrl' => $websiteUrl,
            'applyFormLink' => trim((string) ($row[6] ?? '')),
            'logoUrl' => $directLogoUrl,
            'logoFallbackUrl' => $buildLogoFallbackFromWebsite($websiteUrl),
        ];
    }

    fclose($csvFile);

    return $mappings;
};

$getVetCoursesFromCsvPaths = function (array $paths, array $collegeMetadata) use ($getVetCourseCategory, $normalizeCollegeKey, $getVetFeeBucket, $getVetAreaKeys, $getVetIntakeMonths): array {
    $courses = [];
    $courseIndex = 0;

    foreach ($paths as $csvPath) {
        $csvFile = fopen($csvPath, 'r');
        if ($csvFile === false) {
            continue;
        }

        $institutionLine = fgets($csvFile);
        if ($institutionLine === false) {
            fclose($csvFile);
            continue;
        }

        $institutionValues = array_filter(str_getcsv($institutionLine), fn ($value) => trim((string) $value) !== '');
        $csvBaseName = pathinfo($csvPath, PATHINFO_FILENAME);
        $providerKeyFromFile = $normalizeCollegeKey($csvBaseName);
        $providerKeyFromInstitution = $normalizeCollegeKey((string) ($institutionValues[0] ?? ''));
        $providerKey = isset($collegeMetadata[$providerKeyFromInstitution])
            ? $providerKeyFromInstitution
            : $providerKeyFromFile;
        $metadata = $collegeMetadata[$providerKey] ?? [];
        $providerNameFromCsv = trim((string) ($institutionValues[0] ?? ''));
        $providerName = trim((string) ($metadata['college'] ?? ($institutionValues[0] ?? $csvBaseName)));
        $logoSourceUrl = trim((string) ($metadata['logoUrl'] ?? ''));
        $logoFallbackUrl = trim((string) ($metadata['logoFallbackUrl'] ?? ''));
        $logoProxyUrl = ($logoSourceUrl !== '' || $logoFallbackUrl !== '')
            ? '/api/course-search/logo/'.rawurlencode($providerKey)
            : '';
        $locations = $metadata['locations'] ?? [
            'Sydney' => false,
            'Melbourne' => false,
            'Adelaide' => false,
            'Brisbane' => false,
        ];
        $availableCities = array_values(array_keys(array_filter($locations)));

        fgetcsv($csvFile); // Skip header row

        while (($row = fgetcsv($csvFile)) !== false) {
            if (empty($row[0])) {
                continue;
            }

            $courseIndex++;
            $courseName = trim($row[0]);
            $category = $getVetCourseCategory($courseName);
            $categoryKey = strtolower(str_replace(' ', '_', $category));
            $tuitionFee = $row[4] ?? '';
            $feeBucket = $getVetFeeBucket($tuitionFee);
            $areaKeys = $getVetAreaKeys($courseName);
            $intakeMonths = $getVetIntakeMonths(($row[6] ?? '').' '.$courseName);
            $courseId = md5($courseName.'|'.$providerKey);
            $legacyIds = array_values(array_unique(array_filter([
                md5($courseName.'|'.$providerName),
                $providerNameFromCsv !== '' ? md5($courseName.'|'.$providerNameFromCsv) : null,
                md5($courseName),
            ])));

            $courses[] = [
                'id' => $courseId,
                'title' => $courseName,
                'courseName' => $courseName,
                'courseCode' => strtoupper($csvBaseName).'-'.str_pad($courseIndex, 4, '0', STR_PAD_LEFT),
                'duration' => $row[1] ?? '',
                'enrollmentFee' => $row[2] ?? '',
                'materialFee' => $row[3] ?? '',
                'tuitionFee' => $tuitionFee,
                'promoFee' => $row[5] ?? '',
                'notes' => $row[6] ?? '',
                'courseUrl' => $courseId,
                'legacyIds' => $legacyIds,
                'status' => 'O',
                'courseStatus' => 'O',
                'type' => 'vet',
                'isVet' => true,
                'providerKey' => $providerKey,
                'providerName' => $providerName,
                'categoryKey' => $categoryKey,
                'category' => $category,
                'fieldOfStudy' => $areaKeys,
                'feeBucket' => $feeBucket,
                'intakeMonths' => $intakeMonths,
                'csvFile' => basename($csvPath),
                'studyLocations' => $locations,
                'availableCities' => $availableCities,
                'websiteUrl' => trim((string) ($metadata['websiteUrl'] ?? '')),
                'applyFormLink' => trim((string) ($metadata['applyFormLink'] ?? '')),
                'logoUrl' => $logoProxyUrl,
                'logoSourceUrl' => $logoSourceUrl,
                'logoFallbackUrl' => $logoFallbackUrl,
            ];
        }

        fclose($csvFile);
    }

    return $courses;
};

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FrontendController;

Route::get('/',[FrontendController::class, 'home'])->name('home');



Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');

Route::get('/apply', function () {
    return view('apply-closed');
});


Route::get('/search', function (Request $request) use ($courseSearchPath) {
    $queryString = $request->getQueryString();

    return redirect($courseSearchPath('international').($queryString ? '?'.$queryString : ''));
});

foreach ($courseSearchTypes as $type => $config) {
    Route::get('/course-search/search/'.$config['page_slug'], function () use ($type, $config, $courseSearchTypeMeta) {
        return view('search', [
            'searchType' => $type,
            'searchTypeLabel' => $config['label'],
            'searchHeadingSuffix' => $config['heading_suffix'],
            'searchTypeMeta' => $courseSearchTypeMeta(),
        ]);
    });
}

Route::get('/search/international/course/{id}', function (string $id, Request $request) {
    $queryString = $request->getQueryString();

    return redirect('/course-search/search/international/course/'.rawurlencode($id).($queryString ? '?'.$queryString : ''));
})->where('id', '[A-Za-z0-9_-]+');

Route::get('/search/{type}/course/{id}', function (string $type, string $id, Request $request) use ($resolveCourseSearchType) {
    $resolvedType = $resolveCourseSearchType($type);
    $queryString = $request->getQueryString();

    return redirect('/course-search/search/'.$resolvedType.'/course/'.rawurlencode($id).($queryString ? '?'.$queryString : ''));
})->where('type', 'undergraduate|postgraduate|international|vet|online')->where('id', '[A-Za-z0-9_-]+');

Route::get('/course-search/search/{type}/course/{id}', function (string $type, string $id) use ($resolveCourseSearchType, $courseSearchTypes, $courseSearchPath) {
    $resolvedType = $resolveCourseSearchType($type);

    return view('course-detail', [
        'courseId' => $id,
        'searchType' => $resolvedType,
        'searchTypeLabel' => $courseSearchTypes[$resolvedType]['label'],
        'searchPagePath' => $courseSearchPath($resolvedType),
    ]);
})->where('type', 'undergraduate|postgraduate|international|vet|online')->where('id', '[A-Za-z0-9_-]+');

Route::get('/api/course-search/details/{id}', function (string $id) use ($forwardJson) {
    return $forwardJson('https://coursehub.uac.edu.au/backend/course-search/api/details/international/course/'.rawurlencode($id));
})->where('id', '[A-Za-z0-9_-]+');

Route::get('/api/course-search', function (Request $request) use ($resolveCourseSearchType, $splitQueryValues, $buildQueryString, $forwardJson, $getVetCsvFiles, $getCollegeMetadata, $getVetCoursesFromCsvPaths) {
    $type = $resolveCourseSearchType($request->query('type'));

    // Return vet courses from CSV files for 'vet' type
    if ($type === 'vet') {
        $csvPaths = $getVetCsvFiles();
        $collegeMetadata = $getCollegeMetadata();
        $courses = $getVetCoursesFromCsvPaths($csvPaths, $collegeMetadata);

        $providerFilters = $splitQueryValues($request, 'inst', 'p');
        if ($providerFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($providerFilters): bool {
                return in_array((string) ($course['providerKey'] ?? ''), $providerFilters, true);
            });
        }

        // Filter by search query if provided
        $search = trim((string) $request->query('search', $request->query('query', '')));
        if ($search !== '') {
            $courses = array_filter($courses, function($course) use ($search) {
                return stripos($course['title'], $search) !== false || 
                       stripos($course['courseCode'], $search) !== false;
            });
        }

        // Filter by category (level) if provided
        $level = $request->query('level');
        if ($level) {
            $levelArray = is_array($level) ? $level : [$level];
            $courses = array_filter($courses, function($course) use ($levelArray) {
                return in_array((string) ($course['categoryKey'] ?? ''), $levelArray, true);
            });
        }

        $fosFilters = $splitQueryValues($request, 'fos');
        if ($fosFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($fosFilters): bool {
                $areas = is_array($course['fieldOfStudy'] ?? null) ? $course['fieldOfStudy'] : [];
                return count(array_intersect($areas, $fosFilters)) > 0;
            });
        }

        $feeFilters = $splitQueryValues($request, 'fee');
        if ($feeFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($feeFilters): bool {
                return in_array((string) ($course['feeBucket'] ?? ''), $feeFilters, true);
            });
        }

        $startFilters = $splitQueryValues($request, 'start', 'mm');
        if ($startFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($startFilters): bool {
                $months = is_array($course['intakeMonths'] ?? null) ? $course['intakeMonths'] : [];
                return count(array_intersect($months, $startFilters)) > 0;
            });
        }

        // Implement pagination
        $page = max((int) $request->query('page', 1), 1);
        $size = in_array((int) $request->query('size', 10), [10, 30, 50, 100, 500], true)
            ? (int) $request->query('size', 10)
            : 10;

        $total = count($courses);
        $offset = ($page - 1) * $size;
        $paginatedCourses = array_slice(array_values($courses), $offset, $size);

        return response()->json([
            'results' => $paginatedCourses,
            'stats' => [
                'total' => $total,
                'page' => $page,
                'size' => $size,
            ],
        ]);
    }

    $search = trim((string) $request->query('search', $request->query('query', '')));
    $pathway = $splitQueryValues($request, 'pathway', 'courseStageFlag');
    $status = $splitQueryValues($request, 'status');

    $size = (int) $request->query('size', 10);
    $size = in_array($size, [10, 30, 50, 100, 500], true) ? $size : 10;

    $queryString = $buildQueryString([
        's' => $search !== '' ? $search : null,
        'page' => max((int) $request->query('page', 1), 1),
        'size' => $size,
        'sort' => $request->query('sort'),
        'p' => $splitQueryValues($request, 'inst', 'p'),
        'fos' => $splitQueryValues($request, 'fos'),
        'courseLevel' => $splitQueryValues($request, 'level', 'courseLevel'),
        'fee' => $splitQueryValues($request, 'fee'),
        'mm' => $splitQueryValues($request, 'start', 'mm'),
        'moa' => $splitQueryValues($request, 'attendance', 'moa'),
        'courseStageFlag' => $pathway,
        'status' => $status,
        'content' => $request->boolean('content') ? 'true' : null,
    ]);

    $url = 'https://coursehub.uac.edu.au/backend/course-search/api/search/'.$type;
    $url .= $queryString !== '' ? '?'.$queryString : '';

    return $forwardJson($url);
});

Route::get('/api/course-search/filters', function (Request $request) use ($resolveCourseSearchType, $splitQueryValues, $buildQueryString, $forwardJson, $getVetCsvFiles, $getCollegeMetadata, $getVetCoursesFromCsvPaths, $getVetAreaLabel) {
    $type = $resolveCourseSearchType($request->query('type'));

    // Return vet filters from CSV files for 'vet' type
    if ($type === 'vet') {
        $csvPaths = $getVetCsvFiles();
        $collegeMetadata = $getCollegeMetadata();
        $courses = $getVetCoursesFromCsvPaths($csvPaths, $collegeMetadata);

        $providerFilters = $splitQueryValues($request, 'inst', 'p');
        if ($providerFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($providerFilters): bool {
                return in_array((string) ($course['providerKey'] ?? ''), $providerFilters, true);
            });
        }

        $search = trim((string) $request->query('search', $request->query('query', '')));
        if ($search !== '') {
            $courses = array_filter($courses, function (array $course) use ($search): bool {
                return stripos((string) ($course['title'] ?? ''), $search) !== false
                    || stripos((string) ($course['courseCode'] ?? ''), $search) !== false;
            });
        }

        $levelFilters = $splitQueryValues($request, 'level', 'courseLevel');
        if ($levelFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($levelFilters): bool {
                return in_array((string) ($course['categoryKey'] ?? ''), $levelFilters, true);
            });
        }

        $fosFilters = $splitQueryValues($request, 'fos');
        if ($fosFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($fosFilters): bool {
                $areas = is_array($course['fieldOfStudy'] ?? null) ? $course['fieldOfStudy'] : [];
                return count(array_intersect($areas, $fosFilters)) > 0;
            });
        }

        $feeFilters = $splitQueryValues($request, 'fee');
        if ($feeFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($feeFilters): bool {
                return in_array((string) ($course['feeBucket'] ?? ''), $feeFilters, true);
            });
        }

        $startFilters = $splitQueryValues($request, 'start', 'mm');
        if ($startFilters !== []) {
            $courses = array_filter($courses, function (array $course) use ($startFilters): bool {
                $months = is_array($course['intakeMonths'] ?? null) ? $course['intakeMonths'] : [];
                return count(array_intersect($months, $startFilters)) > 0;
            });
        }

        $categories = [];
        foreach ($courses as $course) {
            $category = $course['category'];
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            $categories[$category]++;
        }

        $categoryFilters = [];
        foreach ($categories as $category => $count) {
            $categoryFilters[] = [
                'key' => strtolower(str_replace(' ', '_', $category)),
                'name' => $category,
                'count' => $count,
            ];
        }

        $providerGroups = [];
        $fieldOfStudyGroups = [];
        $feeGroups = [];
        $startMonthGroups = [];
        foreach ($courses as $course) {
            $providerKey = (string) ($course['providerKey'] ?? '');

            if ($providerKey === '') {
                continue;
            }

            if (!isset($providerGroups[$providerKey])) {
                $providerGroups[$providerKey] = [
                    'key' => $providerKey,
                    'name' => (string) ($course['providerName'] ?? $providerKey),
                    'count' => 0,
                ];
            }

            $providerGroups[$providerKey]['count']++;

            foreach ((array) ($course['fieldOfStudy'] ?? []) as $areaKey) {
                if (!isset($fieldOfStudyGroups[$areaKey])) {
                    $fieldOfStudyGroups[$areaKey] = [
                        'key' => $areaKey,
                        'name' => $getVetAreaLabel($areaKey),
                        'count' => 0,
                    ];
                }
                $fieldOfStudyGroups[$areaKey]['count']++;
            }

            $feeKey = (string) ($course['feeBucket'] ?? '');
            if ($feeKey !== '') {
                $feeNames = [
                    'fee_low' => 'Tuition up to $5,000',
                    'fee_mid' => 'Tuition $5,001 - $10,000',
                    'fee_high' => 'Tuition above $10,000',
                    'variable' => 'Variable / Per level',
                ];

                if (!isset($feeGroups[$feeKey])) {
                    $feeGroups[$feeKey] = [
                        'key' => $feeKey,
                        'name' => $feeNames[$feeKey] ?? $feeKey,
                        'count' => 0,
                    ];
                }
                $feeGroups[$feeKey]['count']++;
            }

            foreach ((array) ($course['intakeMonths'] ?? []) as $monthKey) {
                if (!isset($startMonthGroups[$monthKey])) {
                    $startMonthGroups[$monthKey] = [
                        'key' => $monthKey,
                        'count' => 0,
                    ];
                }
                $startMonthGroups[$monthKey]['count']++;
            }
        }

        usort($categoryFilters, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
        $providerFiltersPayload = array_values($providerGroups);
        $fieldOfStudyPayload = array_values($fieldOfStudyGroups);
        $feePayload = array_values($feeGroups);
        $startMonthsPayload = array_values($startMonthGroups);
        usort($providerFiltersPayload, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
        usort($fieldOfStudyPayload, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
        usort($feePayload, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));
        usort($startMonthsPayload, fn (array $a, array $b): int => strcmp($a['key'], $b['key']));

        return response()->json([
            'providers' => $providerFiltersPayload,
            'fieldOfStudy' => $fieldOfStudyPayload,
            'courseLevel' => $categoryFilters,
            'feeTypes' => $feePayload,
            'startMonths' => $startMonthsPayload,
            'modeOfAttendance' => [],
            'courseStatus' => [],
            'target' => [
                'os' => count($courses),
            ],
        ]);
    }

    $search = trim((string) $request->query('search', $request->query('query', '')));
    $pathway = $splitQueryValues($request, 'pathway', 'courseStageFlag');
    $status = $splitQueryValues($request, 'status');

    $queryString = $buildQueryString([
        'level' => $type,
        's' => $search !== '' ? $search : null,
        'p' => $splitQueryValues($request, 'inst', 'p'),
        'fos' => $splitQueryValues($request, 'fos'),
        'courseLevel' => $splitQueryValues($request, 'level', 'courseLevel'),
        'fee' => $splitQueryValues($request, 'fee'),
        'mm' => $splitQueryValues($request, 'start', 'mm'),
        'moa' => $splitQueryValues($request, 'attendance', 'moa'),
        'courseStageFlag' => $pathway,
        'status' => $status,
        'content' => $request->boolean('content') ? 'true' : null,
    ]);

    return $forwardJson('https://coursehub.uac.edu.au/backend/course-search/api/filters?'.$queryString);
});

Route::get('/api/course-search/vet/colleges', function (Request $request) use ($getVetCsvFiles, $getCollegeMetadata, $getVetCoursesFromCsvPaths) {
    $csvPaths = $getVetCsvFiles();
    $collegeMetadata = $getCollegeMetadata();
    $courses = $getVetCoursesFromCsvPaths($csvPaths, $collegeMetadata);

    $providerFilter = strtolower(trim((string) $request->query('provider', '')));
    $colleges = [];

    foreach ($courses as $course) {
        $providerKey = (string) ($course['providerKey'] ?? '');

        if ($providerKey === '') {
            continue;
        }

        if ($providerFilter !== '' && $providerFilter !== strtolower($providerKey)) {
            continue;
        }

        if (!isset($colleges[$providerKey])) {
            $colleges[$providerKey] = [
                'providerKey' => $providerKey,
                'providerName' => (string) ($course['providerName'] ?? $providerKey),
                'csvFile' => (string) ($course['csvFile'] ?? ''),
                'websiteUrl' => (string) ($course['websiteUrl'] ?? ''),
                'applyFormLink' => (string) ($course['applyFormLink'] ?? ''),
                'logoUrl' => (string) ($course['logoUrl'] ?? ''),
                'logoFallbackUrl' => (string) ($course['logoFallbackUrl'] ?? ''),
                'availableCities' => (array) ($course['availableCities'] ?? []),
                'studyLocations' => (array) ($course['studyLocations'] ?? []),
                'totalCourses' => 0,
                'categories' => [],
            ];
        }

        $colleges[$providerKey]['totalCourses']++;
        $categoryName = (string) ($course['category'] ?? 'Other');
        $categoryKey = (string) ($course['categoryKey'] ?? strtolower(str_replace(' ', '_', $categoryName)));

        if (!isset($colleges[$providerKey]['categories'][$categoryKey])) {
            $colleges[$providerKey]['categories'][$categoryKey] = [
                'key' => $categoryKey,
                'name' => $categoryName,
                'count' => 0,
            ];
        }

        $colleges[$providerKey]['categories'][$categoryKey]['count']++;
    }

    $payload = array_values(array_map(function (array $college): array {
        $college['categories'] = array_values($college['categories']);
        usort($college['categories'], fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $college;
    }, $colleges));

    usort($payload, fn (array $a, array $b): int => strcmp($a['providerName'], $b['providerName']));

    return response()->json([
        'totalColleges' => count($payload),
        'results' => $payload,
    ]);
});

Route::get('/api/course-search/logo/{provider}', function (string $provider) use ($normalizeCollegeKey, $getCollegeMetadata) {
    $providerKey = $normalizeCollegeKey($provider);
    $metadata = $getCollegeMetadata()[$providerKey] ?? null;

    if (!is_array($metadata)) {
        return response('', 404);
    }

    $logoSourceUrl = trim((string) ($metadata['logoUrl'] ?? ''));
    $fallbackUrl = trim((string) ($metadata['logoFallbackUrl'] ?? ''));
    $websiteUrl = trim((string) ($metadata['websiteUrl'] ?? ''));
    $targetUrl = $logoSourceUrl !== '' ? $logoSourceUrl : $fallbackUrl;

    if ($targetUrl === '') {
        return response('', 404);
    }

    $redirectToUrl = function (?string $url) {
        $url = trim((string) $url);

        if ($url === '') {
            return response('', 404);
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return redirect()->away($url);
        }

        return response('', 404);
    };

    $headers = [
        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
    ];

    if ($websiteUrl !== '') {
        $headers['Referer'] = $websiteUrl;

        $origin = parse_url($websiteUrl, PHP_URL_SCHEME).'://'.parse_url($websiteUrl, PHP_URL_HOST);
        if ($origin !== '://') {
            $headers['Origin'] = $origin;
        }
    }

    try {
        $response = Http::withHeaders($headers)
            ->timeout(20)
            ->retry(1, 200)
            ->get($targetUrl);

        $contentType = strtolower((string) $response->header('Content-Type', ''));

        if ((!$response->successful() || !str_starts_with($contentType, 'image/')) && $fallbackUrl !== '' && $targetUrl !== $fallbackUrl) {
            $response = Http::withHeaders($headers)
                ->timeout(20)
                ->retry(1, 200)
                ->get($fallbackUrl);
            $contentType = strtolower((string) $response->header('Content-Type', ''));
        }

        if (!$response->successful()) {
            return $redirectToUrl($targetUrl !== '' ? $targetUrl : $fallbackUrl);
        }

        if (!str_starts_with($contentType, 'image/')) {
            return $redirectToUrl($targetUrl !== '' ? $targetUrl : $fallbackUrl);
        }

        return response($response->body(), 200)
            ->header('Content-Type', $contentType)
            ->header('Cache-Control', 'public, max-age=86400');
    } catch (\Throwable $exception) {
        return $redirectToUrl($targetUrl !== '' ? $targetUrl : $fallbackUrl);
    }
})->where('provider', '[A-Za-z0-9_-]+');

Route::get('/api/course-search/campus', function () use ($forwardJson) {
    return $forwardJson('https://coursehub.uac.edu.au/backend/course-search/api/campus');
});

Route::get('/api/course-search/details/{type}/{id}', function (string $type, string $id) use ($resolveCourseSearchType, $forwardJson, $getVetCsvFiles, $getCollegeMetadata, $getVetCoursesFromCsvPaths) {
    $resolvedType = $resolveCourseSearchType($type);

    // Return vet course details from CSV files for 'vet' type
    if ($resolvedType === 'vet') {
        $csvPaths = $getVetCsvFiles();
        $collegeMetadata = $getCollegeMetadata();
        $courses = $getVetCoursesFromCsvPaths($csvPaths, $collegeMetadata);

        foreach ($courses as $course) {
            $legacyIds = is_array($course['legacyIds'] ?? null) ? $course['legacyIds'] : [];

            if ($course['id'] === $id || in_array($id, $legacyIds, true)) {
                return response()->json([
                    'course' => $course,
                    'contentJson' => [
                        'courseTitle' => $course['title'],
                    ],
                    'courseDoc' => [],
                    'courseList' => [],
                ]);
            }
        }

        return response()->json([
            'message' => 'Course not found',
        ], 404);
    }

    return $forwardJson('https://coursehub.uac.edu.au/backend/course-search/api/details/'.$resolvedType.'/course/'.rawurlencode($id));
})->where('type', 'undergraduate|postgraduate|international|vet|online')->where('id', '[A-Za-z0-9_-]+');

Route::get('/api/course-search/apply-info', function () {
    $portalUrl = '#';
    $loginUrl = '#';
    $registerUrl = '#';

    $extractText = function (?string $html): string {
        $html = str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $html);
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text ?? '');
        $text = preg_replace("/\n{2,}/", "\n", $text ?? '');

        return trim((string) $text);
    };

    try {
        $response = Http::timeout(20)
            ->retry(1, 200)
            ->withHeaders(['Accept' => 'text/html'])
            ->get($portalUrl);

        $html = (string) $response->body();

        preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $html, $titleMatch);
        preg_match('/<p[^>]*class="[^"]*\bwell\b[^"]*"[^>]*>(.*?)<\/p>/is', $html, $messageMatch);

        $title = $extractText($titleMatch[1] ?? '');
        $message = $extractText($messageMatch[1] ?? '');
        $frameOptions = strtoupper((string) $response->header('X-Frame-Options', ''));
        $statusText = strtolower($title.' '.$message);

        return response()->json([
            'available' => $response->successful(),
            'title' => $title !== '' ? $title : 'Apply through UAC',
            'message' => $message !== '' ? $message : 'Continue on the official UAC International application portal.',
            'portalUrl' => $portalUrl,
            'loginUrl' => $loginUrl,
            'registerUrl' => $registerUrl,
            'isClosed' => str_contains($statusText, 'applications are closed'),
            'canEmbed' => $frameOptions === '' ? null : $frameOptions !== 'SAMEORIGIN' && $frameOptions !== 'DENY',
            'xFrameOptions' => $frameOptions !== '' ? $frameOptions : null,
        ], 200);
    } catch (\Throwable $exception) {
        return response()->json([
            'available' => false,
            'title' => 'Apply through UAC',
            'message' => 'Continue on the official UAC International application portal.',
            'portalUrl' => $portalUrl,
            'loginUrl' => $loginUrl,
            'registerUrl' => $registerUrl,
            'isClosed' => null,
            'canEmbed' => false,
            'xFrameOptions' => null,
            'error' => $exception->getMessage(),
        ], 200);
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/',           [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users',      [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/contact',    [AdminController::class, 'contact'])->name('contact');
        Route::delete('/contact/{contact}', [AdminController::class, 'destroy'])->name('contact.destroy');
    });
});

require __DIR__.'/auth.php';
