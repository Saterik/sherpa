<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/config')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@PSR12' => true,
    '@PHP82Migration' => true,
    'strict_param' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'trailing_comma_in_multiline' => true,
    'phpdoc_scalar' => true,
    'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
    'phpdoc_to_return_type' => true,
    'phpdoc_to_param_type' => true,
    'native_function_invocation' => ['include' => ['@all']],
    'modernize_types_casting' => true,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
    'blank_line_before_statement' => ['statements' => ['return', 'throw', 'break', 'continue', 'declare', 'try', 'catch', 'finally']],
    'single_line_throw' => false,
])
->setFinder($finder)
->setRiskyAllowed(true)
->setCacheFile(__DIR__ . '/var/cache/php-cs-fixer.cache');