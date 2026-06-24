#!/usr/bin/env php
<?php

/**
 * Adds PHPDoc blocks to PHP files under app/ that are missing class or public method documentation.
 *
 * Usage: php scripts/document-php.php [--dry-run]
 */

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv, true);
$root = dirname(__DIR__).'/app';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

sort($files);

$updated = 0;

foreach ($files as $path) {
    $original = file_get_contents($path);
    if ($original === false) {
        continue;
    }

    $relative = str_replace(dirname(__DIR__).'/', '', $path);
    $documented = documentFile($original, $relative);

    if ($documented !== $original) {
        $updated++;
        if (! $dryRun) {
            file_put_contents($path, $documented);
        }
        echo ($dryRun ? '[dry-run] ' : '')."Documented: {$relative}\n";
    }
}

echo "\nDone. {$updated} file(s) ".($dryRun ? 'would be ' : '')."updated.\n";

/**
 * @return non-empty-string
 */
function documentFile(string $content, string $relativePath): string
{
    if (! preg_match('/\b(class|enum|interface|trait)\s+([A-Za-z0-9_]+)/', $content, $classMatch, PREG_OFFSET_CAPTURE)) {
        return $content;
    }

    $className = $classMatch[2][0];
    $classOffset = $classMatch[0][1];

    // Move docblock before class modifiers (abstract, readonly, final).
    $modifierPattern = '/\b(abstract|readonly|final)\s+$/';
    $insertOffset = $classOffset;
    $beforeClass = substr($content, 0, $classOffset);
    if (preg_match($modifierPattern, rtrim($beforeClass), $modifierMatch, PREG_OFFSET_CAPTURE)) {
        $insertOffset = $modifierMatch[0][1];
    }

    if (! hasDocblockBefore($content, $insertOffset)) {
        $classDoc = buildClassDocblock($className, $relativePath);
        $content = insertBeforeOffset($content, $insertOffset, $classDoc);
    }

    return documentMethods($content, $className, $relativePath);
}

function hasDocblockBefore(string $content, int $offset): bool
{
    $before = rtrim(substr($content, 0, $offset));

    return (bool) preg_match('/\/\*\*[\s\S]*?\*\/\s*$/', $before);
}

/**
 * @return non-empty-string
 */
function buildClassDocblock(string $className, string $relativePath): string
{
    $description = inferClassDescription($className, $relativePath);

    return "/**\n * {$description}\n */\n";
}

function inferClassDescription(string $className, string $relativePath): string
{
    $suffixDescriptions = [
        'Controller' => 'HTTP controller',
        'Service' => 'Domain service',
        'Policy' => 'Authorization policy',
        'Request' => 'Form request validation',
        'Seeder' => 'Database seeder',
        'Factory' => 'Model factory',
        'Provider' => 'Service provider',
        'Middleware' => 'HTTP middleware',
        'Command' => 'Artisan console command',
        'DTO' => 'Data transfer object',
        'Scope' => 'Eloquent global scope',
        'Driver' => 'Gateway driver implementation',
        'Manager' => 'Manager for related drivers or resources',
        'Resolver' => 'Resolves dynamic values for the application',
        'Renderer' => 'Renders structured output for the application',
    ];

    foreach ($suffixDescriptions as $suffix => $label) {
        if (str_ends_with($className, $suffix)) {
            $subject = trim(preg_replace('/([a-z])([A-Z])/', '$1 $2', str_replace($suffix, '', $className)) ?? $className);

            return "{$label} for {$subject}.";
        }
    }

    if (str_contains($relativePath, '/Models/')) {
        return 'Eloquent model for '.humanize($className).'.';
    }

    if (str_contains($relativePath, '/Enums/')) {
        return 'Enumeration for '.humanize($className).'.';
    }

    if (str_contains($relativePath, '/Features/')) {
        if (preg_match('#Features/([^/]+)/#', $relativePath, $match)) {
            return humanize($match[1]).' feature: '.humanize($className).'.';
        }
    }

    return 'Application class for '.humanize($className).'.';
}

function humanize(string $value): string
{
    $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value) ?? $value;

    return strtolower(str_replace('_', ' ', $value));
}

function documentMethods(string $content, string $className, string $relativePath): string
{
    $pattern = '/(?P<indent>[ \t]*)(?P<modifiers>(?:(?:public|protected|private|static|final|abstract)\s+)*)(function\s+)(?P<name>__construct|__invoke|[a-zA-Z_][a-zA-Z0-9_]*)\s*\((?P<params>[^)]*)\)(?:\s*:\s*(?P<return>[^\s{]+))?/m';

    $offset = 0;

    while (preg_match($pattern, $content, $match, PREG_OFFSET_CAPTURE, $offset)) {
        $functionOffset = $match[0][1];
        $indent = $match['indent'][0];
        $name = $match['name'][0];
        $modifiers = $match['modifiers'][0];
        $params = trim($match['params'][0]);
        $returnType = $match['return'][0] ?? null;

        $offset = $functionOffset + strlen($match[0][0]);

        if (! str_contains($modifiers, 'public') && $name !== '__construct') {
            continue;
        }

        if (hasDocblockBefore($content, $functionOffset)) {
            continue;
        }

        $methodDoc = buildMethodDocblock($name, $params, $returnType, $className, $relativePath, $indent);
        $content = insertBeforeOffset($content, $functionOffset, $methodDoc);
        $offset += strlen($methodDoc);
    }

    return $content;
}

/**
 * @return non-empty-string
 */
function buildMethodDocblock(string $name, string $params, ?string $returnType, string $className, string $relativePath, string $indent): string
{
    $lines = ['/**'];

    if ($name === '__construct') {
        $lines[] = ' * Create a new instance.';
    } elseif ($name === '__invoke') {
        $lines[] = ' * Handle the incoming request.';
    } else {
        $lines[] = ' * '.ucfirst(humanize($name)).'.';
    }

    if ($params !== '') {
        foreach (array_filter(array_map('trim', explode(',', $params))) as $param) {
            if (preg_match('/(?:(?:public|protected|private|readonly)\s+)?(?:[\\\\\w\|?]+\s+)?\$(\w+)/', $param, $paramMatch)) {
                $paramName = $paramMatch[1];
                $type = inferParamType($param, $name);
                $lines[] = " * @param  {$type}  \${$paramName}";
            }
        }
    }

    if ($returnType !== null && $returnType !== 'void') {
        $lines[] = ' * @return '.mapReturnType($returnType);
    }

    $lines[] = ' */';

    return $indent.implode("\n".$indent, $lines)."\n".$indent;
}

function inferParamType(string $param, string $methodName): string
{
    if (preg_match('/([\\\\\w\|?]+)\s+\$/', $param, $match)) {
        return mapReturnType($match[1]);
    }

    if (str_contains($param, 'array')) {
        return $methodName === '__construct' ? 'mixed' : 'array<string, mixed>';
    }

    return 'mixed';
}

function mapReturnType(string $type): string
{
    $type = trim($type);

    return match (true) {
        $type === 'array' => 'array<string, mixed>',
        str_starts_with($type, 'array<') => $type,
        default => $type,
    };
}

function insertBeforeOffset(string $content, int $offset, string $insertion): string
{
    $lineStart = strrpos(substr($content, 0, $offset), "\n");
    $lineStart = $lineStart === false ? 0 : $lineStart + 1;
    $linePrefix = substr($content, $lineStart, $offset - $lineStart);

    if (preg_match('/^[ \t]+$/', $linePrefix)) {
        $offset = $lineStart;
    }

    return substr($content, 0, $offset).$insertion.substr($content, $offset);
}
