<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class EnumHelper
{
    public static function getEnumValues($table, $column)
    {
        $result = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'");
        $type = $result[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = str_getcsv($matches[1], ',', "'");
        return $enum;
    }
}
