<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CollegeApplicationForm
{
    public const ADMIN_FIELDS = [
        'office_received_date',
        'office_approved_date',
        'approved_by',
        'office_signature',
    ];

    public const HIDDEN_FIELDS = [
        'form_template',
        'source_provider_key',
        'source_provider_name',
        'source_course_id',
        'source_course_title',
    ];

    protected const PROVIDER_TEMPLATE_MAP = [
        'actb' => 'acbt',
        'acbt' => 'acbt',
        'bentley' => 'bc',
        'bc' => 'bc',
        'britts' => 'britts',
        'datum' => 'datum',
        'dit' => 'dit',
        'igi' => 'bc',
        'laneway' => 'bc',
        'menzies' => 'menzies',
        'mit' => 'menzies',
        'nortwest' => 'nortwest',
        'northwest' => 'nortwest',
        'pakenham' => 'pakenham',
    ];

    protected const TEMPLATE_PDF_FILES = [
        'acbt' => 'acbt.pdf',
        'bc' => 'bc.pdf',
        'britts' => 'britts.pdf',
        'datum' => 'datum.pdf',
        'dit' => 'dit.pdf',
        'menzies' => 'menzies.pdf',
        'nortwest' => 'nortwest.pdf',
        'pakenham' => 'pakenham.pdf',
    ];

    public static function mirroredProviderKeys(): array
    {
        return array_values(array_unique(array_keys(self::PROVIDER_TEMPLATE_MAP)));
    }

    public static function mirroredProviderPdfUrls(): array
    {
        $urls = [];

        foreach (self::mirroredProviderKeys() as $providerKey) {
            $templateKey = self::resolveTemplateKey($providerKey);

            if ($templateKey !== null) {
                $urls[$providerKey] = self::originalPdfUrl($templateKey);
            }
        }

        return $urls;
    }

    public static function supportsProvider(?string $providerKey): bool
    {
        return self::resolveTemplateKey($providerKey) !== null;
    }

    public static function resolveTemplateKey(?string $providerKey = null, ?string $templateKey = null): ?string
    {
        $normalizedTemplate = self::normalizeTemplateKey($templateKey);

        if ($normalizedTemplate !== '' && file_exists(self::schemaPath($normalizedTemplate))) {
            return $normalizedTemplate;
        }

        $normalizedProvider = self::normalizeProviderKey($providerKey);

        if ($normalizedProvider === '') {
            return null;
        }

        $resolvedTemplate = self::PROVIDER_TEMPLATE_MAP[$normalizedProvider] ?? $normalizedProvider;

        return file_exists(self::schemaPath($resolvedTemplate)) ? $resolvedTemplate : null;
    }

    public static function schema(string $templateKey): array
    {
        static $cache = [];

        $resolvedTemplate = self::resolveTemplateKey(null, $templateKey);

        if ($resolvedTemplate === null) {
            throw new \InvalidArgumentException('Unsupported college form template.');
        }

        if (! isset($cache[$resolvedTemplate])) {
            $decoded = json_decode((string) file_get_contents(self::schemaPath($resolvedTemplate)), true);

            if (! is_array($decoded)) {
                throw new \RuntimeException('Invalid college form schema.');
            }

            $cache[$resolvedTemplate] = self::transformSchema($decoded);
        }

        return $cache[$resolvedTemplate];
    }

    public static function requestRules(array $schema, bool $includeAdminFields = false): array
    {
        $rules = [
            'form_template' => ['required', 'string', Rule::in([$schema['template_key']])],
            'source_provider_key' => ['nullable', 'string', 'max:100'],
            'source_provider_name' => ['nullable', 'string', 'max:255'],
            'source_course_id' => ['nullable', 'string', 'max:255'],
            'source_course_title' => ['nullable', 'string', 'max:255'],
        ];

        foreach ($schema['text_fields'] as $name => $field) {
            $maxLength = ($field['kind'] ?? 'text') === 'textarea' ? 5000 : 2000;
            $rules[$name] = ['nullable', 'string', 'max:'.$maxLength];
        }

        foreach ($schema['radio_groups'] as $name => $group) {
            $rules[$name] = [
                'nullable',
                'string',
                Rule::in(array_values(array_column($group['options'], 'value'))),
            ];
        }

        foreach ($schema['checkbox_fields'] as $name => $field) {
            $rules[$name] = ['nullable', 'boolean'];
        }

        if ($includeAdminFields) {
            foreach (self::ADMIN_FIELDS as $field) {
                $rules[$field] = ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }

    public static function formData(
        array $values,
        array $schema,
        ?string $selectedCourseTitle = null,
        ?string $selectedCourseId = null,
        ?string $providerKey = null,
        ?string $providerName = null
    ): array {
        $data = self::defaultPayload($schema);

        foreach ($schema['text_fields'] as $name => $field) {
            $data[$name] = trim((string) Arr::get($values, $name, ''));
        }

        foreach ($schema['radio_groups'] as $name => $group) {
            $selectedValue = trim((string) Arr::get($values, $name, ''));
            $allowedValues = array_values(array_column($group['options'], 'value'));

            $data[$name] = in_array($selectedValue, $allowedValues, true) ? $selectedValue : '';
        }

        foreach ($schema['checkbox_fields'] as $name => $field) {
            $data[$name] = self::isTruthy(Arr::get($values, $name)) ? '1' : '';
        }

        $resolvedTemplate = self::resolveTemplateKey(
            Arr::get($values, 'source_provider_key', $providerKey),
            Arr::get($values, 'form_template', $schema['template_key'])
        ) ?? $schema['template_key'];

        $resolvedProviderKey = self::normalizeProviderKey((string) Arr::get($values, 'source_provider_key', $providerKey));

        $data['form_template'] = $resolvedTemplate;
        $data['source_provider_key'] = $resolvedProviderKey;
        $data['source_provider_name'] = trim((string) Arr::get($values, 'source_provider_name', $providerName ?? ''));
        $data['source_course_id'] = trim((string) Arr::get($values, 'source_course_id', $selectedCourseId ?? ''));
        $data['source_course_title'] = trim((string) Arr::get($values, 'source_course_title', $selectedCourseTitle ?? ''));

        foreach (self::ADMIN_FIELDS as $field) {
            $data[$field] = trim((string) Arr::get($values, $field, ''));
        }

        return $data;
    }

    public static function courseId(array $formData): string
    {
        return trim((string) ($formData['source_course_id'] ?? ''))
            ?: trim((string) ($formData['source_provider_key'] ?? ''));
    }

    public static function courseName(array $formData): string
    {
        return trim((string) ($formData['source_course_title'] ?? ''))
            ?: trim((string) ($formData['source_provider_name'] ?? ''))
            ?: Str::headline((string) ($formData['form_template'] ?? 'Application'));
    }

    public static function rectStyle(array $rect, array $schema): array
    {
        [$x1, $y1, $x2, $y2] = array_map('floatval', $rect);

        $pageWidth = max((float) ($schema['page_width'] ?? 1), 1);
        $pageHeight = max((float) ($schema['page_height'] ?? 1), 1);

        return [
            'left' => round(($x1 / $pageWidth) * 100, 4),
            'top' => round((($pageHeight - $y2) / $pageHeight) * 100, 4),
            'width' => round((($x2 - $x1) / $pageWidth) * 100, 4),
            'height' => round((($y2 - $y1) / $pageHeight) * 100, 4),
        ];
    }

    public static function originalPdfUrl(string $templateKey): string
    {
        $resolvedTemplate = self::resolveTemplateKey(null, $templateKey);
        $pdfFile = self::TEMPLATE_PDF_FILES[$resolvedTemplate ?? ''] ?? null;

        if ($pdfFile === null) {
            throw new \InvalidArgumentException('Unsupported college PDF template.');
        }

        return asset($pdfFile);
    }

    protected static function defaultPayload(array $schema): array
    {
        $payload = [];

        foreach (array_keys($schema['text_fields']) as $name) {
            $payload[$name] = '';
        }

        foreach (array_keys($schema['radio_groups']) as $name) {
            $payload[$name] = '';
        }

        foreach (array_keys($schema['checkbox_fields']) as $name) {
            $payload[$name] = '';
        }

        foreach (self::HIDDEN_FIELDS as $name) {
            $payload[$name] = '';
        }

        foreach (self::ADMIN_FIELDS as $name) {
            $payload[$name] = '';
        }

        return $payload;
    }

    protected static function transformSchema(array $schema): array
    {
        $transformed = [
            'template_key' => (string) ($schema['template_key'] ?? ''),
            'page_width' => (float) ($schema['page_width'] ?? 595.32),
            'page_height' => (float) ($schema['page_height'] ?? 841.92),
            'pages' => collect($schema['pages'] ?? [])
                ->mapWithKeys(fn (array $page) => [(int) ($page['page'] ?? 0) => [
                    'page' => (int) ($page['page'] ?? 0),
                    'image' => (string) ($page['image'] ?? ''),
                ]])
                ->filter(fn (array $page, int $pageNumber) => $pageNumber > 0 && $page['image'] !== '')
                ->all(),
            'text_fields' => [],
            'radio_groups' => [],
            'checkbox_fields' => [],
            'hidden_fields' => self::HIDDEN_FIELDS,
        ];

        foreach ($schema['fields'] ?? [] as $field) {
            if (! is_array($field) || empty($field['key']) || ! isset($field['rect'])) {
                continue;
            }

            $fieldFlags = (int) ($field['field_flags'] ?? 0);
            $fieldType = (string) ($field['field_type'] ?? '');

            if ($fieldType === '/Btn' && ($fieldFlags & 65536) === 65536) {
                continue;
            }

            $normalizedField = [
                'key' => (string) $field['key'],
                'label' => trim((string) ($field['label'] ?? $field['key'])),
                'page' => (int) ($field['page'] ?? 0),
                'rect' => array_values(array_map('floatval', (array) $field['rect'])),
                'kind' => (string) ($field['type'] ?? 'text'),
                'field_type' => $fieldType,
                'field_flags' => $fieldFlags,
            ];

            if ($fieldType === '/Btn' && ($fieldFlags & 32768) === 32768) {
                $groupKey = self::radioGroupKey($normalizedField['key']);

                if (! isset($transformed['radio_groups'][$groupKey])) {
                    $transformed['radio_groups'][$groupKey] = [
                        'label' => $normalizedField['label'],
                        'options' => [],
                    ];
                }

                $transformed['radio_groups'][$groupKey]['options'][] = [
                    'value' => $normalizedField['key'],
                    'label' => $normalizedField['label'],
                    'page' => $normalizedField['page'],
                    'rect' => $normalizedField['rect'],
                ];

                continue;
            }

            if ($fieldType === '/Btn') {
                $transformed['checkbox_fields'][$normalizedField['key']] = $normalizedField;

                continue;
            }

            $transformed['text_fields'][$normalizedField['key']] = $normalizedField;
        }

        return $transformed;
    }

    protected static function radioGroupKey(string $fieldKey): string
    {
        return preg_replace('/_\d+$/', '', $fieldKey) ?: $fieldKey;
    }

    protected static function normalizeTemplateKey(?string $value): string
    {
        return (string) preg_replace('/[^a-z0-9]+/i', '', strtolower(trim((string) $value)));
    }

    protected static function normalizeProviderKey(?string $value): string
    {
        $normalized = self::normalizeTemplateKey($value);

        if ($normalized === '') {
            return '';
        }

        $aliases = [
            'acbt' => 'acbt',
            'actb' => 'actb',
            'bentleycollege' => 'bentley',
            'brittscollege' => 'britts',
            'datumcollege' => 'datum',
            'igi' => 'igi',
            'igicollege' => 'igi',
            'laneway' => 'laneway',
            'lanewaycollege' => 'laneway',
            'menzies' => 'mit',
            'menziesinstituteoftechnology' => 'mit',
            'northwest' => 'nortwest',
            'northwestcollege' => 'nortwest',
            'nortwestcollege' => 'nortwest',
            'pakhanem' => 'pakenham',
            'pakhanemcollege' => 'pakenham',
            'pakenhamcollege' => 'pakenham',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    protected static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    protected static function schemaPath(string $templateKey): string
    {
        return resource_path('forms/college-pdfs/'.$templateKey.'.json');
    }
}
