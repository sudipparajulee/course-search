<?php

if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/generate_college_form_schema.php <pdf-path> <slug> <output-json>\n");
    exit(1);
}

[$script, $pdfPath, $slug, $outputJson] = $argv;

if (! is_file($pdfPath)) {
    fwrite(STDERR, "PDF not found: {$pdfPath}\n");
    exit(1);
}

$qpdfJson = shell_exec('qpdf --json '.escapeshellarg($pdfPath).' - 2>/dev/null');

if (! is_string($qpdfJson) || trim($qpdfJson) === '') {
    fwrite(STDERR, "Failed to read qpdf JSON for {$pdfPath}\n");
    exit(1);
}

$pdfInfo = shell_exec('pdfinfo '.escapeshellarg($pdfPath).' 2>/dev/null');

if (! is_string($pdfInfo) || trim($pdfInfo) === '') {
    fwrite(STDERR, "Failed to read pdfinfo output for {$pdfPath}\n");
    exit(1);
}

$decoded = json_decode($qpdfJson, true);

if (! is_array($decoded)) {
    fwrite(STDERR, "Invalid qpdf JSON for {$pdfPath}\n");
    exit(1);
}

preg_match('/Page size:\s+([0-9.]+)\s+x\s+([0-9.]+)\s+pts/i', $pdfInfo, $pageSizeMatches);
$pageWidth = isset($pageSizeMatches[1]) ? (float) $pageSizeMatches[1] : 595.32;
$pageHeight = isset($pageSizeMatches[2]) ? (float) $pageSizeMatches[2] : 841.92;

$pageCount = count($decoded['pages'] ?? []);
$pages = [];

for ($page = 1; $page <= $pageCount; $page++) {
    $pages[] = [
        'page' => $page,
        'image' => "images/forms/{$slug}/page-{$page}.png",
    ];
}

$fields = [];
$usedKeys = [];

foreach ($decoded['acroform']['fields'] ?? [] as $index => $field) {
    $annotationObject = $field['annotation']['object'] ?? null;

    if (! is_string($annotationObject) || $annotationObject === '') {
        continue;
    }

    $objectKey = 'obj:'.$annotationObject;
    $rect = $decoded['qpdf'][1][$objectKey]['value']['/Rect'] ?? null;

    if (! is_array($rect) || count($rect) !== 4) {
        continue;
    }

    $rawName = trim((string) ($field['fullname']
        ?? $field['partialname']
        ?? $field['mappingname']
        ?? $field['alternativename']
        ?? 'field_'.$index));

    $baseKey = preg_replace('/[^a-z0-9]+/i', '_', strtolower($rawName));
    $baseKey = trim((string) $baseKey, '_');
    $baseKey = $baseKey !== '' ? $baseKey : 'field_'.$index;

    $key = $baseKey;
    $suffix = 2;

    while (isset($usedKeys[$key])) {
        $key = $baseKey.'_'.$suffix;
        $suffix++;
    }

    $usedKeys[$key] = true;

    $fieldFlags = (int) ($field['fieldflags'] ?? 0);
    $fieldType = (string) ($field['fieldtype'] ?? '/Tx');
    $rectHeight = abs((float) $rect[3] - (float) $rect[1]);

    $type = match ($fieldType) {
        '/Btn' => 'checkbox',
        '/Sig' => 'signature',
        default => (($fieldFlags & 4096) === 4096 || $rectHeight >= 22.0) ? 'textarea' : 'text',
    };

    $fields[] = [
        'key' => $key,
        'label' => $rawName,
        'type' => $type,
        'page' => (int) ($field['pageposfrom1'] ?? 1),
        'rect' => array_map('floatval', $rect),
        'field_type' => $fieldType,
        'field_flags' => $fieldFlags,
    ];
}

$payload = [
    'template_key' => $slug,
    'page_width' => $pageWidth,
    'page_height' => $pageHeight,
    'pages' => $pages,
    'fields' => $fields,
];

$outputDir = dirname($outputJson);

if (! is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

file_put_contents(
    $outputJson,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

fwrite(STDOUT, "Generated {$outputJson}\n");
