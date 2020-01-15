<?php

namespace MyProject\Model;

use Zer0\Model\Generic;

class File extends Generic
{
    public static $publicFields;
    protected static $primaryKey = 'id';
    protected static $storages;
    protected static $storagesConf = [
        'MySQL' => [
            'table' => 'files',
            'pdoName' => 'MySQL',
        ],
    ];
    protected static $indexes;
    protected static $indexesConf = [];
    protected static $rules = [
        'id' => 'integer',
        'size' => 'integer',
        'path' => 'string',
        'filename' => 'string',
        'filename' => 'string',
        'md5' => 'string',
        'deletion_key' => 'string',
        'uploadedAt' => 'integer',
    ];

    public function init()
    {
        $this->set('uploadedAt', time());
    }
}
