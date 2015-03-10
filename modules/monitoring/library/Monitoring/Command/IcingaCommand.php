<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Module\Monitoring\Command;

/**
 * Base class for commands sent to an Icinga instance
 */
abstract class IcingaCommand
{
    /**
     * Get the name of the command
     *
     * @return string
     */
    public function getName()
    {
        $nsParts = explode('\\', get_called_class());
        return substr_replace(end($nsParts), '', -7);  // Remove 'Command' Suffix
    }
}
