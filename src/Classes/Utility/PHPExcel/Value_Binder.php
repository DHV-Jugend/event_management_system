<?php

namespace BIT\EMS\Utility\PHPExcel;

use PHPExcel_Cell;
use PHPExcel_Cell_DataType;
use PHPExcel_Cell_DefaultValueBinder;
use PHPExcel_Cell_IValueBinder;
use PHPExcel_Shared_String;

/**
 * @author Christoph Bessei
 * @version
 */
class Value_Binder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        // sanitize UTF-8 strings
        if (is_string($value)) {
            $value = PHPExcel_Shared_String::SanitizeUTF8($value);
        }

        // Implement your own override logic
        if (is_string($value) && $value[0] == '0') {
            $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);

            return true;
        }

        // Not bound yet? Use default value parent...
        return parent::bindValue($cell, $value);
    }
}