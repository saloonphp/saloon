<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->in([
        __DIR__,
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->exclude(['vendor', 'node_modules']);

$config = new PhpCsFixer\Config();

// Rules from: https://cs.symfony.com/doc/rules/index.html

return $config->setRules([
    '@PSR2' => true,
    'array_syntax' => ['syntax' => 'short'],
    'ordered_imports' => ['sort_algorithm' => 'length'],
    'no_unused_imports' => true,
    'not_operator_with_successor_space' => true,
    'trailing_comma_in_multiline' => true,
    'single_quote' => ['strings_containing_single_quote_chars' => true],
    'phpdoc_scalar' => true,
    'unary_operator_spaces' => true,
    'binary_operator_spaces' => true,
    'blank_line_before_statement' => [
        'statements' => ['declare', 'return', 'throw', 'try'],
    ],
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_var_without_name' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => true,
    ],
    'return_type_declaration' => [
        'space_before' => 'none'
    ],
    'declare_strict_types' => true,
    'blank_line_after_opening_tag' => true,
    'single_import_per_statement' => true,
    'mb_str_functions' => true,
    'no_superfluous_phpdoc_tags' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_empty_phpdoc' => true,
    'phpdoc_trim' => true,
])->setFinder($finder);
