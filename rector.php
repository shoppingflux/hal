<?php

declare(strict_types=1);

namespace ShoppingFeed;

use Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector;
use Rector\EarlyReturn;
use Rector\Instanceof_;
use Rector\Php81;
use Rector\Php82;
use Rector\Php83;
use Rector\Php84;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
    )
    ->withRules([
        Php84\Rector\Param\ExplicitNullableParamTypeRector::class,

        // From 8.3 ruleset
        Php83\Rector\ClassConst\AddTypeToConstRector::class,
        Php83\Rector\FuncCall\CombineHostPortLdapUriRector::class,
        Php83\Rector\FuncCall\RemoveGetClassGetParentClassNoArgsRector::class,
    ]);
