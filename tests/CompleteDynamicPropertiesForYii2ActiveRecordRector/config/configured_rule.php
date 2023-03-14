<?php

declare(strict_types=1);

use Muse\Rector\CompleteDynamicPropertiesForYii2ActiveRecordRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../config/config.php');
    $rectorConfig->rule(CompleteDynamicPropertiesForYii2ActiveRecordRector::class);
};
