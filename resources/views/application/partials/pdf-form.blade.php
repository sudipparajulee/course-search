@php
    use App\Support\BcApplicationForm;
    use App\Support\CollegeApplicationForm;

    $isReadOnly = $isReadOnly ?? false;
    $formSchema = $formSchema ?? [];
    $formData = $formData ?? [];
    $usesLegacySchema = isset($formSchema['checkbox_groups']);

    $valueFor = function (string $name, $default = '') use ($formData) {
        return old($name, $formData[$name] ?? $default);
    };

    $arrayValueFor = function (string $name) use ($formData): array {
        $value = old($name, $formData[$name] ?? []);

        if (is_array($value)) {
            return array_values(array_unique(array_filter(array_map('strval', $value))));
        }

        if ($value === null || $value === '') {
            return [];
        }

        return [strval($value)];
    };

    $checkboxValueFor = function (string $name) use ($formData): bool {
        $value = old($name, $formData[$name] ?? '');

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    };
@endphp

@once
    <style>
        .college-pdf-page {
            position: relative;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: white;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        .college-pdf-page img {
            display: block;
            width: 100%;
            height: auto;
        }

        .college-pdf-field {
            position: absolute;
            z-index: 2;
        }

        .college-pdf-text,
        .college-pdf-textarea,
        .college-pdf-value {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            color: #0f172a;
            font-size: clamp(9px, 0.95vw, 16px);
            line-height: 1.1;
            outline: none;
        }

        .college-pdf-text,
        .college-pdf-value {
            display: flex;
            align-items: center;
            padding: 0 0.3rem;
        }

        .college-pdf-text {
            transition: background-color 0.2s ease;
        }

        .college-pdf-text:focus,
        .college-pdf-textarea:focus {
            background: rgba(255, 255, 255, 0.72);
        }

        .college-pdf-textarea {
            resize: none;
            padding: 0.2rem 0.3rem;
        }

        .college-pdf-signature {
            font-family: "Snell Roundhand", "Segoe Script", cursive;
            font-style: italic;
            font-size: clamp(12px, 1.15vw, 20px);
        }

        .college-pdf-check {
            position: absolute;
            z-index: 3;
        }

        .college-pdf-check label,
        .college-pdf-check span {
            display: flex;
            height: 100%;
            width: 100%;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-size: clamp(10px, 1.05vw, 18px);
            font-weight: 700;
            line-height: 1;
        }

        .college-pdf-check input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .college-pdf-checkmark {
            opacity: 0;
            transition: opacity 0.15s ease;
        }

        .college-pdf-check input:checked + .college-pdf-checkmark {
            opacity: 1;
        }

        @media print {
            .college-pdf-page {
                box-shadow: none;
                border: none;
                border-radius: 0;
                break-after: page;
                page-break-after: always;
            }

            .college-pdf-page:last-child {
                break-after: auto;
                page-break-after: auto;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endonce

<div class="space-y-8">
    @foreach ($formSchema['pages'] ?? [] as $pageNumber => $page)
        <section class="mx-auto max-w-[980px]">
            <div class="college-pdf-page">
                <img src="{{ asset($page['image']) }}" alt="Application form page {{ $pageNumber }}">

                @if ($usesLegacySchema)
                    @foreach ($formSchema['text_fields'] as $name => $field)
                        @continue(($field['page'] ?? null) !== $pageNumber)

                        @php
                            $style = BcApplicationForm::rectStyle($field['rect']);
                            $value = $valueFor($name);
                            $kind = $field['kind'] ?? 'text';
                            $allowEdit = ! $isReadOnly && ! ($field['admin_only'] ?? false);
                        @endphp

                        <div
                            class="college-pdf-field"
                            style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                        >
                            @if ($allowEdit && $kind === 'textarea')
                                <textarea
                                    name="{{ $name }}"
                                    class="college-pdf-textarea"
                                    spellcheck="false"
                                >{{ $value }}</textarea>
                            @elseif ($allowEdit)
                                <input
                                    type="text"
                                    name="{{ $name }}"
                                    value="{{ $value }}"
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                    class="college-pdf-text {{ $kind === 'signature' ? 'college-pdf-signature' : '' }}"
                                    autocomplete="off"
                                    spellcheck="false"
                                >
                            @else
                                <div class="college-pdf-value {{ $kind === 'signature' ? 'college-pdf-signature' : '' }}">
                                    {{ $value }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @foreach ($formSchema['radio_groups'] as $name => $group)
                        @php $selected = $valueFor($name); @endphp

                        @foreach ($group['options'] as $option)
                            @continue(($option['page'] ?? null) !== $pageNumber)

                            @php $style = BcApplicationForm::rectStyle($option['rect']); @endphp

                            <div
                                class="college-pdf-check"
                                style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                            >
                                @if ($isReadOnly)
                                    <span class="college-pdf-checkmark" style="opacity: {{ $selected === $option['value'] ? 1 : 0 }};">&#10003;</span>
                                @else
                                    <label>
                                        <input
                                            type="radio"
                                            name="{{ $name }}"
                                            value="{{ $option['value'] }}"
                                            @checked($selected === $option['value'])
                                        >
                                        <span class="college-pdf-checkmark">&#10003;</span>
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    @endforeach

                    @foreach ($formSchema['checkbox_groups'] as $name => $group)
                        @php $selectedValues = $arrayValueFor($name); @endphp

                        @foreach ($group['options'] as $option)
                            @continue(($option['page'] ?? null) !== $pageNumber)

                            @php
                                $style = BcApplicationForm::rectStyle($option['rect']);
                                $isChecked = in_array($option['value'], $selectedValues, true);
                            @endphp

                            <div
                                class="college-pdf-check"
                                style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                            >
                                @if ($isReadOnly)
                                    <span class="college-pdf-checkmark" style="opacity: {{ $isChecked ? 1 : 0 }};">&#10003;</span>
                                @else
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="{{ $name }}[]"
                                            value="{{ $option['value'] }}"
                                            @checked($isChecked)
                                        >
                                        <span class="college-pdf-checkmark">&#10003;</span>
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                @else
                    @foreach ($formSchema['text_fields'] as $name => $field)
                        @continue(($field['page'] ?? null) !== $pageNumber)

                        @php
                            $style = CollegeApplicationForm::rectStyle($field['rect'], $formSchema);
                            $value = $valueFor($name);
                            $kind = $field['kind'] ?? 'text';
                        @endphp

                        <div
                            class="college-pdf-field"
                            style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                        >
                            @if (! $isReadOnly && $kind === 'textarea')
                                <textarea
                                    name="{{ $name }}"
                                    class="college-pdf-textarea"
                                    spellcheck="false"
                                >{{ $value }}</textarea>
                            @elseif (! $isReadOnly)
                                <input
                                    type="text"
                                    name="{{ $name }}"
                                    value="{{ $value }}"
                                    class="college-pdf-text {{ $kind === 'signature' ? 'college-pdf-signature' : '' }}"
                                    autocomplete="off"
                                    spellcheck="false"
                                >
                            @else
                                <div class="college-pdf-value {{ $kind === 'signature' ? 'college-pdf-signature' : '' }}">
                                    {{ $value }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @foreach ($formSchema['radio_groups'] as $name => $group)
                        @php $selected = $valueFor($name); @endphp

                        @foreach ($group['options'] as $option)
                            @continue(($option['page'] ?? null) !== $pageNumber)

                            @php $style = CollegeApplicationForm::rectStyle($option['rect'], $formSchema); @endphp

                            <div
                                class="college-pdf-check"
                                style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                            >
                                @if ($isReadOnly)
                                    <span class="college-pdf-checkmark" style="opacity: {{ $selected === $option['value'] ? 1 : 0 }};">&#10003;</span>
                                @else
                                    <label>
                                        <input
                                            type="radio"
                                            name="{{ $name }}"
                                            value="{{ $option['value'] }}"
                                            @checked($selected === $option['value'])
                                        >
                                        <span class="college-pdf-checkmark">&#10003;</span>
                                    </label>
                                @endif
                            </div>
                        @endforeach
                    @endforeach

                    @foreach ($formSchema['checkbox_fields'] as $name => $field)
                        @continue(($field['page'] ?? null) !== $pageNumber)

                        @php
                            $style = CollegeApplicationForm::rectStyle($field['rect'], $formSchema);
                            $isChecked = $checkboxValueFor($name);
                        @endphp

                        <div
                            class="college-pdf-check"
                            style="left: {{ $style['left'] }}%; top: {{ $style['top'] }}%; width: {{ $style['width'] }}%; height: {{ $style['height'] }}%;"
                        >
                            @if ($isReadOnly)
                                <span class="college-pdf-checkmark" style="opacity: {{ $isChecked ? 1 : 0 }};">&#10003;</span>
                            @else
                                <label>
                                    <input
                                        type="checkbox"
                                        name="{{ $name }}"
                                        value="1"
                                        @checked($isChecked)
                                    >
                                    <span class="college-pdf-checkmark">&#10003;</span>
                                </label>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    @endforeach

    @unless ($isReadOnly)
        @foreach ($formSchema['hidden_fields'] ?? [] as $hiddenField)
            <input type="hidden" name="{{ $hiddenField }}" value="{{ $valueFor($hiddenField) }}">
        @endforeach
    @endunless
</div>

@unless ($isReadOnly)
    @once
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const editableForms = document.querySelectorAll('[data-college-form-editable="1"], [data-bc-form-editable="1"]');

                editableForms.forEach((form) => {
                    const applicantName = form.querySelector('[name="applicant_name"]');
                    const declarationName = form.querySelector('[name="declaration_name"]');
                    const applicantSignature = form.querySelector('[name="applicant_signature"]');

                    const syncField = (target) => {
                        if (!applicantName || !target) {
                            return;
                        }

                        if (target.value.trim() === '' || target.dataset.autofill === '1') {
                            target.value = applicantName.value;
                            target.dataset.autofill = '1';
                        }
                    };

                    syncField(declarationName);
                    syncField(applicantSignature);

                    applicantName?.addEventListener('input', () => {
                        syncField(declarationName);
                        syncField(applicantSignature);
                    });

                    declarationName?.addEventListener('input', () => {
                        declarationName.dataset.autofill = '0';
                    });

                    applicantSignature?.addEventListener('input', () => {
                        applicantSignature.dataset.autofill = '0';
                    });
                });
            });
        </script>
    @endonce
@endunless
