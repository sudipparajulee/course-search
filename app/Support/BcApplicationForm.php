<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BcApplicationForm
{
    public const PAGE_WIDTH = 595.32;

    public const PAGE_HEIGHT = 841.92;

    public static function schema(): array
    {
        return [
            'pages' => self::pages(),
            'text_fields' => self::textFields(),
            'radio_groups' => self::radioGroups(),
            'checkbox_groups' => self::checkboxGroups(),
            'hidden_fields' => [
                'source_course_id',
                'source_course_title',
            ],
        ];
    }

    public static function pages(): array
    {
        return [
            1 => ['image' => 'images/forms/bc/page-1.png'],
            2 => ['image' => 'images/forms/bc/page-2.png'],
            3 => ['image' => 'images/forms/bc/page-3.png'],
            4 => ['image' => 'images/forms/bc/page-4.png'],
        ];
    }

    public static function requestRules(bool $includeAdminFields = false): array
    {
        $courseValues = array_column(self::checkboxGroups()['selected_courses']['options'], 'value');
        $disabilityValues = array_column(self::checkboxGroups()['disability_types']['options'], 'value');
        $checklistValues = array_column(self::checkboxGroups()['document_checklist']['options'], 'value');

        $rules = [
            'selected_courses' => ['required', 'array', 'min:1'],
            'selected_courses.*' => ['string', Rule::in($courseValues)],
            'document_checklist' => ['nullable', 'array'],
            'document_checklist.*' => ['string', Rule::in($checklistValues)],
            'disability_types' => ['nullable', 'array'],
            'disability_types.*' => ['string', Rule::in($disabilityValues)],
            'title' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'string', 'max:40'],
            'end_date' => ['nullable', 'string', 'max:40'],
            'family_name' => ['required', 'string', 'max:255'],
            'given_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'string', 'max:40'],
            'nationality' => ['required', 'string', 'max:255'],
            'home_address' => ['nullable', 'string', 'max:255'],
            'home_country' => ['nullable', 'string', 'max:255'],
            'home_postcode' => ['nullable', 'string', 'max:50'],
            'home_phone' => ['nullable', 'string', 'max:100'],
            'home_email' => ['required', 'email', 'max:255'],
            'australia_address' => ['nullable', 'string', 'max:255'],
            'australia_state' => ['nullable', 'string', 'max:100'],
            'australia_postcode' => ['nullable', 'string', 'max:50'],
            'australia_phone' => ['nullable', 'string', 'max:100'],
            'australia_email' => ['nullable', 'email', 'max:255'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:100'],
            'emergency_address' => ['nullable', 'string', 'max:255'],
            'emergency_relationship' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['required', 'string', 'max:100'],
            'passport_expiry_date' => ['nullable', 'string', 'max:40'],
            'visa_type' => ['required', 'string', 'max:100'],
            'visa_subclass' => ['nullable', 'string', 'max:50'],
            'visa_expiry_date' => ['nullable', 'string', 'max:40'],
            'commencement_visa_type' => ['nullable', 'string', 'max:50'],
            'permanent_residency_applied' => ['nullable', 'string', 'max:10'],
            'permanent_residency_application_date' => ['nullable', 'string', 'max:40'],
            'usi_number' => ['nullable', 'string', 'max:100'],
            'birth_country_option' => ['nullable', 'string', 'max:50'],
            'birth_country_other' => ['nullable', 'string', 'max:255'],
            'other_language_at_home' => ['nullable', 'string', 'max:10'],
            'english_proficiency' => ['nullable', 'string', 'max:50'],
            'schooling_completed' => ['nullable', 'string', 'max:10'],
            'highest_qualification' => ['nullable', 'string', 'max:255'],
            'previous_institute_name' => ['nullable', 'string', 'max:255'],
            'completed_year' => ['nullable', 'string', 'max:40'],
            'disability_condition' => ['nullable', 'string', 'max:10'],
            'employment_status' => ['nullable', 'string', 'max:100'],
            'study_reason' => ['nullable', 'string', 'max:100'],
            'rpl_credit_requested' => ['nullable', 'string', 'max:10'],
            'transferring_from_provider' => ['nullable', 'string', 'max:10'],
            'transfer_completed_six_months' => ['nullable', 'string', 'max:10'],
            'agent_name_business_name' => ['nullable', 'string', 'max:2000'],
            'agent_certification_statement' => ['nullable', 'string', 'max:2000'],
            'agent_signature' => ['nullable', 'string', 'max:255'],
            'declaration_name' => ['nullable', 'string', 'max:255'],
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_signature' => ['required', 'string', 'max:255'],
            'applicant_date' => ['nullable', 'string', 'max:40'],
            'source_course_id' => ['nullable', 'string', 'max:255'],
            'source_course_title' => ['nullable', 'string', 'max:255'],
        ];

        if ($includeAdminFields) {
            $rules = array_merge($rules, [
                'office_received_date' => ['nullable', 'string', 'max:40'],
                'office_approved_date' => ['nullable', 'string', 'max:40'],
                'approved_by' => ['nullable', 'string', 'max:255'],
                'office_signature' => ['nullable', 'string', 'max:255'],
            ]);
        }

        return $rules;
    }

    public static function formData(array $values = [], ?string $selectedCourseTitle = null, ?string $selectedCourseId = null): array
    {
        $data = array_merge(self::defaultPayload(), Arr::only($values, self::submissionKeys(true)));

        foreach (['selected_courses', 'document_checklist', 'disability_types'] as $arrayField) {
            $value = $data[$arrayField] ?? [];
            $data[$arrayField] = is_array($value)
                ? array_values(array_unique(array_filter(array_map('strval', $value))))
                : [];
        }

        if ($data['selected_courses'] === [] && $selectedCourseTitle) {
            $data['selected_courses'] = self::matchCourseSelections($selectedCourseTitle);
        }

        if ($data['source_course_id'] === '' && $selectedCourseId !== null) {
            $data['source_course_id'] = trim($selectedCourseId);
        }

        if ($data['source_course_title'] === '' && $selectedCourseTitle !== null) {
            $data['source_course_title'] = trim($selectedCourseTitle);
        }

        if ($data['declaration_name'] === '' && $data['applicant_name'] !== '') {
            $data['declaration_name'] = $data['applicant_name'];
        }

        return $data;
    }

    public static function submissionKeys(bool $includeAdminFields = false): array
    {
        $keys = array_merge(
            array_keys(self::textFields()),
            array_keys(self::radioGroups()),
            array_keys(self::checkboxGroups()),
            [
                'source_course_id',
                'source_course_title',
            ]
        );

        if (! $includeAdminFields) {
            $keys = array_diff($keys, self::adminFieldNames());
        }

        return array_values(array_unique($keys));
    }

    public static function adminFieldNames(): array
    {
        return [
            'office_received_date',
            'office_approved_date',
            'approved_by',
            'office_signature',
        ];
    }

    public static function courseSummary(array $data): string
    {
        $selected = Arr::get($data, 'selected_courses', []);
        $labels = collect(self::courseOptions())
            ->keyBy('value')
            ->only($selected)
            ->pluck('label')
            ->values()
            ->all();

        return implode(', ', $labels);
    }

    public static function courseIds(array $data): string
    {
        return implode(',', Arr::get($data, 'selected_courses', []));
    }

    public static function courseOptions(): array
    {
        return self::checkboxGroups()['selected_courses']['options'];
    }

    public static function rectStyle(array $rect): array
    {
        [$x1, $y1, $x2, $y2] = $rect;

        return [
            'left' => round(($x1 / self::PAGE_WIDTH) * 100, 4),
            'top' => round(((self::PAGE_HEIGHT - $y2) / self::PAGE_HEIGHT) * 100, 4),
            'width' => round((($x2 - $x1) / self::PAGE_WIDTH) * 100, 4),
            'height' => round((($y2 - $y1) / self::PAGE_HEIGHT) * 100, 4),
        ];
    }

    protected static function defaultPayload(): array
    {
        return [
            'selected_courses' => [],
            'document_checklist' => [],
            'disability_types' => [],
            'title' => '',
            'gender' => '',
            'start_date' => '',
            'end_date' => '',
            'family_name' => '',
            'given_name' => '',
            'dob' => '',
            'nationality' => '',
            'home_address' => '',
            'home_country' => '',
            'home_postcode' => '',
            'home_phone' => '',
            'home_email' => '',
            'australia_address' => '',
            'australia_state' => '',
            'australia_postcode' => '',
            'australia_phone' => '',
            'australia_email' => '',
            'emergency_name' => '',
            'emergency_phone' => '',
            'emergency_address' => '',
            'emergency_relationship' => '',
            'passport_number' => '',
            'passport_expiry_date' => '',
            'visa_type' => '',
            'visa_subclass' => '',
            'visa_expiry_date' => '',
            'commencement_visa_type' => '',
            'permanent_residency_applied' => '',
            'permanent_residency_application_date' => '',
            'usi_number' => '',
            'birth_country_option' => '',
            'birth_country_other' => '',
            'other_language_at_home' => '',
            'english_proficiency' => '',
            'schooling_completed' => '',
            'highest_qualification' => '',
            'previous_institute_name' => '',
            'completed_year' => '',
            'disability_condition' => '',
            'employment_status' => '',
            'study_reason' => '',
            'rpl_credit_requested' => '',
            'transferring_from_provider' => '',
            'transfer_completed_six_months' => '',
            'agent_name_business_name' => '',
            'agent_certification_statement' => '',
            'agent_signature' => '',
            'declaration_name' => '',
            'applicant_name' => '',
            'applicant_signature' => '',
            'applicant_date' => '',
            'office_received_date' => '',
            'office_approved_date' => '',
            'approved_by' => '',
            'office_signature' => '',
            'source_course_id' => '',
            'source_course_title' => '',
        ];
    }

    protected static function textFields(): array
    {
        return [
            'start_date' => ['page' => 1, 'rect' => [181.32, 315.48, 282.36, 325.8], 'placeholder' => 'dd/mm/yy'],
            'end_date' => ['page' => 1, 'rect' => [454.8, 315.48, 555.84, 325.8], 'placeholder' => 'dd/mm/yy'],
            'family_name' => ['page' => 1, 'rect' => [211.68, 255, 300.12, 265.2]],
            'given_name' => ['page' => 1, 'rect' => [392.16, 254.64, 556.08, 264.96]],
            'dob' => ['page' => 1, 'rect' => [211.2, 232.8, 300.12, 243.12], 'placeholder' => 'dd/mm/yy'],
            'nationality' => ['page' => 1, 'rect' => [451.44, 233.04, 556.08, 243.36]],
            'home_address' => ['page' => 1, 'rect' => [117.6, 188.04, 335.64, 198.24]],
            'home_country' => ['page' => 1, 'rect' => [381, 188.52, 436.32, 198.84]],
            'home_postcode' => ['page' => 1, 'rect' => [490.56, 188.52, 556.2, 198.84]],
            'home_phone' => ['page' => 1, 'rect' => [165.72, 168.72, 291, 179.04]],
            'home_email' => ['page' => 1, 'rect' => [330, 171.72, 556.08, 182.04]],
            'australia_address' => ['page' => 1, 'rect' => [120.24, 124.8, 333.24, 135.12]],
            'australia_state' => ['page' => 1, 'rect' => [370.08, 124.32, 435.6, 134.64]],
            'australia_postcode' => ['page' => 1, 'rect' => [490.56, 123.36, 556.2, 133.68]],
            'australia_phone' => ['page' => 1, 'rect' => [165.48, 108, 290.76, 118.32]],
            'australia_email' => ['page' => 1, 'rect' => [329.76, 107.88, 555.84, 118.2]],
            'emergency_name' => ['page' => 1, 'rect' => [115.44, 65.88, 300.24, 76.2]],
            'emergency_phone' => ['page' => 1, 'rect' => [383.28, 66.48, 556.68, 77.88]],
            'emergency_address' => ['page' => 1, 'rect' => [125.28, 49.08, 300.12, 59.4]],
            'emergency_relationship' => ['page' => 1, 'rect' => [425.88, 49.08, 556.8, 59.4]],
            'passport_number' => ['page' => 2, 'rect' => [165.24, 712.2, 300.48, 722.52]],
            'passport_expiry_date' => ['page' => 2, 'rect' => [395.88, 711.48, 555.96, 721.68], 'placeholder' => 'dd/mm/yy'],
            'visa_type' => ['page' => 2, 'rect' => [133.32, 696.24, 246.24, 706.56]],
            'visa_subclass' => ['page' => 2, 'rect' => [302.16, 696.24, 336, 706.56]],
            'visa_expiry_date' => ['page' => 2, 'rect' => [395.88, 696, 555.96, 706.32], 'placeholder' => 'dd/mm/yy'],
            'permanent_residency_application_date' => ['page' => 2, 'rect' => [252.48, 639.48, 387.6, 649.68], 'placeholder' => 'dd/mm/yy'],
            'usi_number' => ['page' => 2, 'rect' => [148.08, 594.48, 283.2, 604.8]],
            'birth_country_other' => ['page' => 2, 'rect' => [441.6, 516, 557.4, 526.32]],
            'highest_qualification' => ['page' => 2, 'rect' => [180.6, 385.32, 428.52, 395.64]],
            'previous_institute_name' => ['page' => 2, 'rect' => [168.24, 371.76, 348.72, 381.96]],
            'completed_year' => ['page' => 2, 'rect' => [431.88, 372.6, 557.4, 382.92]],
            'agent_name_business_name' => ['page' => 3, 'rect' => [72.12, 549.96, 291.6, 578.76], 'kind' => 'textarea'],
            'agent_certification_statement' => ['page' => 3, 'rect' => [293.4, 549.96, 521.16, 564], 'kind' => 'textarea'],
            'agent_signature' => ['page' => 3, 'rect' => [339.84, 519.159, 486.0, 547.239], 'kind' => 'signature'],
            'declaration_name' => ['page' => 4, 'rect' => [78.3596, 639.009, 185.236, 654.698]],
            'applicant_name' => ['page' => 4, 'rect' => [188.76, 348.72, 463.56, 366.84]],
            'applicant_signature' => ['page' => 4, 'rect' => [189.24, 307.32, 463.92, 325.56], 'kind' => 'signature'],
            'applicant_date' => ['page' => 4, 'rect' => [95.7145, 271.403, 168.933, 293.403], 'placeholder' => 'dd/mm/yy'],
            'office_received_date' => ['page' => 4, 'rect' => [147.397, 138.349, 259.914, 150.357], 'placeholder' => 'dd/mm/yyyy', 'admin_only' => true],
            'office_approved_date' => ['page' => 4, 'rect' => [149.927, 119.729, 262.046, 129.767], 'placeholder' => 'dd/mm/yyyy', 'admin_only' => true],
            'approved_by' => ['page' => 4, 'rect' => [139.2, 96.72, 211.32, 119.04], 'admin_only' => true],
            'office_signature' => ['page' => 4, 'rect' => [291.48, 96.72, 380.4, 119.04], 'kind' => 'signature', 'admin_only' => true],
        ];
    }

    protected static function radioGroups(): array
    {
        return [
            'title' => [
                'options' => [
                    ['value' => 'Mr', 'page' => 1, 'rect' => [110.28, 273.12, 120.24, 279.72]],
                    ['value' => 'Mrs', 'page' => 1, 'rect' => [143.04, 273.12, 153, 279.72]],
                    ['value' => 'Ms', 'page' => 1, 'rect' => [180.72, 273.12, 190.68, 279.72]],
                    ['value' => 'Dr', 'page' => 1, 'rect' => [218.04, 273.12, 228, 279.72]],
                    ['value' => 'Other', 'page' => 1, 'rect' => [249.72, 273.12, 259.68, 279.72]],
                ],
            ],
            'gender' => [
                'options' => [
                    ['value' => 'Male', 'page' => 1, 'rect' => [378.48, 273.12, 388.44, 279.72]],
                    ['value' => 'Female', 'page' => 1, 'rect' => [438, 273.12, 447.96, 279.72]],
                    ['value' => 'Other', 'page' => 1, 'rect' => [507.36, 273.24, 517.32, 279.84]],
                ],
            ],
            'commencement_visa_type' => [
                'options' => [
                    ['value' => 'Student', 'page' => 2, 'rect' => [111.36, 667.92, 121.32, 677.88]],
                    ['value' => 'Working Holiday', 'page' => 2, 'rect' => [201.72, 669.92, 211.68, 679.88]],
                    ['value' => 'Tourist', 'page' => 2, 'rect' => [327.72, 667.92, 337.68, 677.88]],
                    ['value' => 'Other', 'page' => 2, 'rect' => [422.64, 667.92, 432.6, 677.88]],
                ],
            ],
            'permanent_residency_applied' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 2, 'rect' => [401.52, 652.68, 411.48, 662.64]],
                    ['value' => 'No', 'page' => 2, 'rect' => [460.8, 652.68, 470.76, 662.64]],
                ],
            ],
            'birth_country_option' => [
                'options' => [
                    ['value' => 'Australia', 'page' => 2, 'rect' => [251.76, 518.4, 261.72, 525]],
                    ['value' => 'Other', 'page' => 2, 'rect' => [325.68, 518.4, 335.64, 525]],
                ],
            ],
            'other_language_at_home' => [
                'options' => [
                    ['value' => 'No', 'page' => 2, 'rect' => [333.12, 490.8, 343.08, 497.4]],
                    ['value' => 'Yes', 'page' => 2, 'rect' => [375.36, 490.8, 385.32, 497.4]],
                ],
            ],
            'english_proficiency' => [
                'options' => [
                    ['value' => 'Very well', 'page' => 2, 'rect' => [251.04, 458.28, 261, 464.88]],
                    ['value' => 'Well', 'page' => 2, 'rect' => [326.76, 458.28, 336.72, 464.88]],
                    ['value' => 'Not well', 'page' => 2, 'rect' => [378.36, 456.24, 388.32, 462.84]],
                    ['value' => 'Not at all', 'page' => 2, 'rect' => [449.04, 456.24, 459, 462.84]],
                ],
            ],
            'schooling_completed' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 2, 'rect' => [258.96, 399.84, 268.92, 406.44]],
                    ['value' => 'No', 'page' => 2, 'rect' => [375.36, 397.8, 385.32, 404.4]],
                ],
            ],
            'disability_condition' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 2, 'rect' => [462.96, 329.52, 472.92, 336.12]],
                    ['value' => 'No', 'page' => 2, 'rect' => [523.44, 329.52, 533.4, 336.12]],
                ],
            ],
            'employment_status' => [
                'options' => [
                    ['value' => 'Full-time employee', 'page' => 2, 'rect' => [97.92, 242.64, 106.92, 248.64]],
                    ['value' => 'Part-time employee', 'page' => 2, 'rect' => [220.44, 242.64, 229.44, 248.64]],
                    ['value' => 'Self-employed', 'page' => 2, 'rect' => [350.04, 242.64, 359.04, 248.64]],
                    ['value' => 'Employer', 'page' => 2, 'rect' => [471.6, 242.64, 480.6, 248.64]],
                    ['value' => 'Employed in a family business', 'page' => 2, 'rect' => [97.92, 228.36, 106.92, 234.36]],
                    ['value' => 'Unemployed - seeking work', 'page' => 2, 'rect' => [245.4, 228.36, 254.4, 234.36]],
                    ['value' => 'Not employed - not seeking employment', 'page' => 2, 'rect' => [378, 228.36, 387, 234.36]],
                ],
            ],
            'study_reason' => [
                'options' => [
                    ['value' => 'To get a job', 'page' => 2, 'rect' => [105, 173.64, 114.96, 180.24]],
                    ['value' => 'To start my own business', 'page' => 2, 'rect' => [105, 160.68, 114.96, 167.28]],
                    ['value' => 'To get a better job or promotion', 'page' => 2, 'rect' => [105, 147.72, 114.96, 154.32]],
                    ['value' => 'To get into another course of study', 'page' => 2, 'rect' => [105, 134.76, 114.96, 141.36]],
                    ['value' => 'Other reasons', 'page' => 2, 'rect' => [105, 121.8, 114.96, 128.4]],
                    ['value' => 'To develop my existing business', 'page' => 2, 'rect' => [298.08, 173.64, 308.04, 180.24]],
                    ['value' => 'To try for a different career', 'page' => 2, 'rect' => [298.08, 160.68, 308.04, 167.28]],
                    ['value' => 'It was a requirement of my job', 'page' => 2, 'rect' => [298.08, 147.72, 308.04, 154.32]],
                    ['value' => 'For personal interest or self-development', 'page' => 2, 'rect' => [298.08, 134.76, 308.04, 141.36]],
                ],
            ],
            'rpl_credit_requested' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 2, 'rect' => [446.28, 81.48, 456.24, 88.08]],
                    ['value' => 'No', 'page' => 2, 'rect' => [515.16, 81.48, 525.12, 88.08]],
                ],
            ],
            'transferring_from_provider' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 3, 'rect' => [447.48, 706.8, 457.44, 713.4]],
                    ['value' => 'No', 'page' => 3, 'rect' => [516.36, 706.8, 526.32, 713.4]],
                ],
            ],
            'transfer_completed_six_months' => [
                'options' => [
                    ['value' => 'Yes', 'page' => 3, 'rect' => [447, 693.84, 456.96, 700.44]],
                    ['value' => 'No', 'page' => 3, 'rect' => [515.88, 693.84, 525.84, 700.44]],
                ],
            ],
        ];
    }

    protected static function checkboxGroups(): array
    {
        return [
            'selected_courses' => [
                'options' => [
                    ['value' => 'bsb40820', 'label' => 'BSB40820 Certificate IV in Marketing and Communication', 'page' => 1, 'rect' => [75.3622, 663.935, 85.3307, 673.106]],
                    ['value' => 'bsb50620', 'label' => 'BSB50620 Diploma of Marketing and Communication', 'page' => 1, 'rect' => [75.3622, 646.967, 85.3307, 656.138]],
                    ['value' => 'bsb60520', 'label' => 'BSB60520 Advanced Diploma of Marketing and Communication', 'page' => 1, 'rect' => [75.3622, 629.998, 85.3307, 639.169]],
                    ['value' => 'bsb50120', 'label' => 'BSB50120 Diploma of Business', 'page' => 1, 'rect' => [74.9635, 613.03, 85.3307, 622.201]],
                    ['value' => 'bsb60120', 'label' => 'BSB60120 Advanced Diploma of Business', 'page' => 1, 'rect' => [75.3622, 596.092, 85.3307, 605.264]],
                    ['value' => 'bsb50420', 'label' => 'BSB50420 Diploma of Leadership and Management', 'page' => 1, 'rect' => [75.3622, 579.155, 85.3307, 588.326]],
                    ['value' => 'bsb60420', 'label' => 'BSB60420 Advanced Diploma of Leadership and Management', 'page' => 1, 'rect' => [75.3622, 562.187, 85.3307, 571.358]],
                    ['value' => 'bsb80120', 'label' => 'BSB80120 Graduate Diploma of Management (Learning)', 'page' => 1, 'rect' => [75.3622, 538.281, 85.3307, 547.452]],
                    ['value' => 'sit30821', 'label' => 'SIT30821 Certificate III in Commercial Cookery', 'page' => 1, 'rect' => [75.3622, 485.313, 85.3307, 494.484]],
                    ['value' => 'sit40521', 'label' => 'SIT40521 Certificate IV in Kitchen Management', 'page' => 1, 'rect' => [75.3622, 468.344, 85.3307, 477.515]],
                    ['value' => 'sit50422', 'label' => 'SIT50422 Diploma of Hospitality Management', 'page' => 1, 'rect' => [75.3622, 451.376, 85.3307, 460.547]],
                    ['value' => 'sit60322', 'label' => 'SIT60322 Advanced Diploma of Hospitality Management', 'page' => 1, 'rect' => [75.3622, 434.407, 85.3307, 443.578]],
                    ['value' => 'sit31016', 'label' => 'SIT31016 Certificate III in Patisserie', 'page' => 1, 'rect' => [75.3622, 411.279, 85.3307, 420.45]],
                    ['value' => 'eap', 'label' => 'English for Academic Purposes', 'page' => 1, 'rect' => [75.3622, 359.588, 85.3307, 368.759]],
                    ['value' => 'general_english', 'label' => 'General English', 'page' => 1, 'rect' => [75.3622, 342.898, 85.3307, 352.069]],
                ],
            ],
            'disability_types' => [
                'options' => [
                    ['value' => 'Hearing/Deaf', 'page' => 2, 'rect' => [110.4, 306.12, 119.4, 312.12]],
                    ['value' => 'Physical', 'page' => 2, 'rect' => [219.96, 306.12, 228.96, 312.12]],
                    ['value' => 'Intellectual', 'page' => 2, 'rect' => [305.04, 306.12, 314.04, 311.721]],
                    ['value' => 'Acquired Brain Impairment', 'page' => 2, 'rect' => [414.48, 306.12, 423.48, 312.12]],
                    ['value' => 'Mental Illness', 'page' => 2, 'rect' => [110.4, 294.48, 119.4, 300.48]],
                    ['value' => 'Vision', 'page' => 2, 'rect' => [219.48, 294.48, 228.48, 300.48]],
                    ['value' => 'Medical Condition', 'page' => 2, 'rect' => [305.52, 294.48, 314.52, 300.48]],
                    ['value' => 'Other', 'page' => 2, 'rect' => [413.52, 294.48, 422.52, 300.48]],
                ],
            ],
            'document_checklist' => [
                'options' => [
                    ['value' => 'Passport bio-data pages', 'page' => 4, 'rect' => [72, 240.36, 81.96, 246.96]],
                    ['value' => 'IELTS (or other English Language test) Results (if applicable)', 'page' => 4, 'rect' => [72, 227.4, 81.96, 234]],
                    ['value' => 'Evidence of highest academic qualifications', 'page' => 4, 'rect' => [72, 214.44, 81.96, 221.04]],
                    ['value' => 'Copy of current Australian Visa (if applicable)', 'page' => 4, 'rect' => [72, 201.48, 81.96, 208.08]],
                    ['value' => 'OSHC Certificate (if applicable)', 'page' => 4, 'rect' => [72, 188.64, 81.96, 195.24]],
                ],
            ],
        ];
    }

    protected static function matchCourseSelections(?string $selectedCourseTitle): array
    {
        $needle = self::normalized((string) $selectedCourseTitle);

        if ($needle === '') {
            return [];
        }

        return collect(self::courseOptions())
            ->filter(function (array $option) use ($needle): bool {
                $value = self::normalized($option['value']);
                $label = self::normalized($option['label']);

                return str_contains($needle, $value)
                    || str_contains($needle, $label)
                    || str_contains($label, $needle);
            })
            ->pluck('value')
            ->values()
            ->all();
    }

    protected static function normalized(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }
}
