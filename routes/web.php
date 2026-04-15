<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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

Route::get('/api/course-search', function (Request $request) use ($resolveCourseSearchType, $splitQueryValues, $buildQueryString, $forwardJson) {
    $type = $resolveCourseSearchType($request->query('type'));

    // Return vet courses from CSV file for 'vet' type
    if ($type === 'vet') {
        $csvPath = public_path('vet.csv');
        $courses = [];
        
        // Function to determine category from course name
        $getCategory = function($courseName) {
            $name = strtolower($courseName);
            
            if (strpos($name, 'certificate') !== false) {
                return 'Certificate';
            } elseif (strpos($name, 'advanced diploma') !== false) {
                return 'Advanced Diploma';
            } elseif (strpos($name, 'graduate diploma') !== false) {
                return 'Graduate Diploma';
            } elseif (strpos($name, 'diploma') !== false) {
                return 'Diploma';
            } elseif (strpos($name, 'english') !== false) {
                return 'English';
            } else {
                return 'Other';
            }
        };
        
        if (file_exists($csvPath)) {
            $csvFile = fopen($csvPath, 'r');
            fgets($csvFile); // Skip institution name line
            fgetcsv($csvFile); // Skip header row
            
            $courseIndex = 0;
            
            while (($row = fgetcsv($csvFile)) !== false) {
                if (!empty($row[0])) { // Course name column
                    $courseIndex++;
                    $courseName = $row[0];
                    $duration = $row[1] ?? '';
                    $courseCode = 'VET' . str_pad($courseIndex, 4, '0', STR_PAD_LEFT);
                    $category = $getCategory($courseName);
                    
                    $courses[] = [
                        'id' => md5($courseName),
                        'title' => $courseName,
                        'courseName' => $courseName,
                        'courseCode' => $courseCode,
                        'duration' => $duration,
                        'enrollmentFee' => $row[2] ?? '',
                        'materialFee' => $row[3] ?? '',
                        'tuitionFee' => $row[4] ?? '',
                        'promoFee' => $row[5] ?? '',
                        'notes' => $row[6] ?? '',
                        'courseUrl' => md5($courseName),
                        'status' => 'O', // Open
                        'courseStatus' => 'O',
                        'type' => 'vet',
                        'isVet' => true, // Flag to identify vet courses for custom UI
                        'category' => $category, // Add category based on qualification level
                    ];
                }
            }
            fclose($csvFile);
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
                $categoryKey = strtolower(str_replace(' ', '_', $course['category']));
                return in_array($categoryKey, $levelArray);
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

Route::get('/api/course-search/filters', function (Request $request) use ($resolveCourseSearchType, $splitQueryValues, $buildQueryString, $forwardJson) {
    $type = $resolveCourseSearchType($request->query('type'));

    // Return vet filters from CSV file for 'vet' type
    if ($type === 'vet') {
        $csvPath = public_path('vet.csv');
        $courseCount = 0;
        $categories = [];
        
        // Function to determine category from course name
        $getCategory = function($courseName) {
            $name = strtolower($courseName);
            
            if (strpos($name, 'certificate') !== false) {
                return 'Certificate';
            } elseif (strpos($name, 'advanced diploma') !== false) {
                return 'Advanced Diploma';
            } elseif (strpos($name, 'graduate diploma') !== false) {
                return 'Graduate Diploma';
            } elseif (strpos($name, 'diploma') !== false) {
                return 'Diploma';
            } elseif (strpos($name, 'english') !== false) {
                return 'English';
            } else {
                return 'Other';
            }
        };
        
        if (file_exists($csvPath)) {
            $csvFile = fopen($csvPath, 'r');
            fgets($csvFile); // Skip institution name line
            fgetcsv($csvFile); // Skip header row
            
            while (($row = fgetcsv($csvFile)) !== false) {
                if (!empty($row[0])) {
                    $courseCount++;
                    $category = $getCategory($row[0]);
                    
                    // Count courses per category
                    if (!isset($categories[$category])) {
                        $categories[$category] = 0;
                    }
                    $categories[$category]++;
                }
            }
            fclose($csvFile);
        }
        
        // Convert categories to filter format
        $categoryFilters = [];
        foreach ($categories as $category => $count) {
            $categoryFilters[] = [
                'key' => strtolower(str_replace(' ', '_', $category)),
                'name' => $category,
                'count' => $count,
            ];
        }
        
        return response()->json([
            'providers' => [],
            'fieldOfStudy' => [],
            'courseLevel' => $categoryFilters, // Use courseLevel for qualification categories
            'feeTypes' => [],
            'startMonths' => [],
            'modeOfAttendance' => [],
            'courseStatus' => [],
            'target' => [
                'os' => $courseCount,
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

Route::get('/api/course-search/campus', function () use ($forwardJson) {
    return $forwardJson('https://coursehub.uac.edu.au/backend/course-search/api/campus');
});

Route::get('/api/course-search/details/{type}/{id}', function (string $type, string $id) use ($resolveCourseSearchType, $forwardJson) {
    $resolvedType = $resolveCourseSearchType($type);

    // Return vet course details from CSV file for 'vet' type
    if ($resolvedType === 'vet') {
        $csvPath = public_path('vet.csv');
        
        if (file_exists($csvPath)) {
            $csvFile = fopen($csvPath, 'r');
            fgets($csvFile); // Skip institution name line
            fgetcsv($csvFile); // Skip header row
            
            $courseIndex = 0;
            
            while (($row = fgetcsv($csvFile)) !== false) {
                if (!empty($row[0])) {
                    $courseIndex++;
                    $courseName = $row[0];
                    
                    if (md5($courseName) === $id) {
                        fclose($csvFile);
                        $duration = $row[1] ?? '';
                        $courseCode = 'VET' . str_pad($courseIndex, 4, '0', STR_PAD_LEFT);
                        
                        return response()->json([
                            'course' => [
                                'id' => md5($courseName),
                                'title' => $courseName,
                                'courseName' => $courseName,
                                'courseCode' => $courseCode,
                                'duration' => $duration,
                                'enrollmentFee' => $row[2] ?? '',
                                'materialFee' => $row[3] ?? '',
                                'tuitionFee' => $row[4] ?? '',
                                'promoFee' => $row[5] ?? '',
                                'notes' => $row[6] ?? '',
                                'courseUrl' => md5($courseName),
                                'status' => 'O', // Open
                                'courseStatus' => 'O',
                                'type' => 'vet',
                                'isVet' => true, // Flag to identify vet courses for custom UI
                            ],
                            'contentJson' => [
                                'courseTitle' => $courseName,
                            ],
                            'courseDoc' => [],
                            'courseList' => [],
                        ]);
                    }
                }
            }
            fclose($csvFile);
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
