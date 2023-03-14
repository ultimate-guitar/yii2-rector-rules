<?php

declare(strict_types=1);

namespace Muse\ActiveRecordAnalyzer;

interface ActiveRecordConst
{
    public const READ_WRITE_PROPERTY = '@property';
    public const READ_PROPERTY = '@property-read';
    public const METHOD = '@method';

    public const YII2_ACTIVE_RECORD_PATH = 'yii\db\ActiveRecord';
    public const YII2_ACTIVE_QUERY_PATH = 'yii\db\ActiveQuery';

    public const ALTERNATIVE_OPTION_TO_LINK_AR = '@mixin';
    public const ACTIVE_RECORD_RULES = 'rules';
    public const ACTIVE_RECORD_FIND = 'find';

    public const VALIDATOR_TO_TYPE = [
        'string' => 'string',
        'integer' => 'int',
        'number' => 'float',
    ];
    public const GETTER_PREFIX = 'get';
    public const HAS_ONE = 'hasOne';
    public const HAS_MANY = 'hasMany';
}
