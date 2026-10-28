<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/bin', __DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(false)
    ->setFinder($finder)
    ->setRules([
        '@PER-CS2x0' => true,
        '@PHP8x2Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'modernize_strpos' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'strict_comparison' => true,
        'strict_param' => true,
        'yoda_style' => false,
        'native_function_invocation' => false,
        'phpdoc_to_comment' => false,
    ]);
