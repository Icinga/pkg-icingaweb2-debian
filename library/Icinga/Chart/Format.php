<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Chart;

class Format
{
    /**
     * Format a number into a number-string as defined by the SVG-Standard
     *
     * @see http://www.w3.org/TR/SVG/types.html#DataTypeNumber
     *
     * @param $number
     *
     * @return string
     */
    public static function formatSVGNumber($number)
    {
        return number_format($number, 1, '.', '');
    }
}
