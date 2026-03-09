#!/usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

$autoloadPath = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    fwrite(STDERR, "Composer dependencies are required. Run: composer install\n");
    exit(1);
}

require $autoloadPath;

if (!class_exists(Yaml::class)) {
    fwrite(STDERR, "symfony/yaml dependency not found. Run: composer install\n");
    exit(1);
}

$rootPath = dirname(__DIR__, 2);
$inputFile = $rootPath . '/modules/api/docs/openapi/base.yaml';
$outputFile = $rootPath . '/modules/api/docs/openapi.generated.yaml';
$legacyOutputFile = $rootPath . '/docs/swagger.yaml';

if (!file_exists($inputFile)) {
    fwrite(STDERR, "OpenAPI base file not found: {$inputFile}\n");
    exit(1);
}

/**
 * @var array<string, mixed> $fileCache
 */
$fileCache = [];

/**
 * @return mixed
 */
$parseFile = static function (string $filePath) use (&$fileCache) {
    if (!isset($fileCache[$filePath])) {
        $fileCache[$filePath] = Yaml::parseFile($filePath);
    }

    return $fileCache[$filePath];
};

/**
 * @return mixed
 */
$resolvePointer = static function (mixed $document, string $pointer) {
    if ($pointer === '' || $pointer === '/') {
        return $document;
    }

    $segments = explode('/', ltrim($pointer, '/'));
    $node = $document;

    foreach ($segments as $segment) {
        $segment = str_replace(['~1', '~0'], ['/', '~'], $segment);

        if (is_array($node) && array_key_exists($segment, $node)) {
            $node = $node[$segment];
            continue;
        }

        throw new RuntimeException(sprintf('Invalid JSON pointer segment "%s" in pointer "%s".', $segment, $pointer));
    }

    return $node;
};

/**
 * @return mixed
 */
$mergeNodes = static function (mixed $source, mixed $override) use (&$mergeNodes) {
    if (!is_array($source) || !is_array($override)) {
        return $override;
    }

    $result = $source;
    foreach ($override as $key => $value) {
        if (array_key_exists($key, $result)) {
            $result[$key] = $mergeNodes($result[$key], $value);
            continue;
        }

        $result[$key] = $value;
    }

    return $result;
};

/**
 * @return mixed
 */
$resolveNode = static function (mixed $node, string $currentFile, array $chain = []) use (&$resolveNode, $parseFile, $resolvePointer, $mergeNodes) {
    if (!is_array($node)) {
        return $node;
    }

    if (isset($node['$ref']) && is_string($node['$ref'])) {
        [$refPath, $refPointer] = array_pad(explode('#', $node['$ref'], 2), 2, '');

        if (!str_starts_with($refPath, 'http://') && !str_starts_with($refPath, 'https://')) {
            $targetFile = $currentFile;
            if ($refPath !== '') {
                $targetFile = realpath(dirname($currentFile) . DIRECTORY_SEPARATOR . $refPath);
                if ($targetFile === false) {
                    throw new RuntimeException(sprintf('Referenced file not found: %s', $refPath));
                }
            }

            $chainKey = $targetFile . '#' . $refPointer;
            if (in_array($chainKey, $chain, true)) {
                throw new RuntimeException(sprintf('Circular reference detected: %s', $chainKey));
            }

            $targetDocument = $parseFile($targetFile);
            $resolvedReference = $resolvePointer($targetDocument, $refPointer);
            $resolvedReference = $resolveNode($resolvedReference, $targetFile, array_merge($chain, [$chainKey]));

            $siblings = $node;
            unset($siblings['$ref']);
            if ($siblings !== []) {
                $resolvedSiblings = $resolveNode($siblings, $currentFile, $chain);
                return $mergeNodes($resolvedReference, $resolvedSiblings);
            }

            return $resolvedReference;
        }
    }

    foreach ($node as $key => $value) {
        $node[$key] = $resolveNode($value, $currentFile, $chain);
    }

    return $node;
};

$baseDocument = $parseFile($inputFile);
$resolved = $resolveNode($baseDocument, $inputFile);

$yaml = Yaml::dump($resolved, 20, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
file_put_contents($outputFile, $yaml);
file_put_contents($legacyOutputFile, $yaml);

fwrite(STDOUT, "OpenAPI bundle generated:\n- {$outputFile}\n- {$legacyOutputFile}\n");
