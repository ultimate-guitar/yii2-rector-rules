<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Symplify\EasyParallel\ValueObject\EasyParallelConfig;

return static function (RectorConfig $rectorConfig): void {
    // make use of https://github.com/symplify/easy-parallel
    $rectorConfig->import(EasyParallelConfig::FILE_PATH);

    $rectorConfig->paths([]);
    $rectorConfig->skip([]);

    $services = $rectorConfig->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Muse\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/Rector']);
};
