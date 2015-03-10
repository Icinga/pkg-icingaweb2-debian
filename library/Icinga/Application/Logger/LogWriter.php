<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Application\Logger;

use Icinga\Data\ConfigObject;

/**
 * Abstract class for writers that write messages to a log
 */
abstract class LogWriter
{
    /**
     * @var ConfigObject
     */
    protected $config;

    /**
     * Create a new log writer initialized with the given configuration
     */
    public function __construct(ConfigObject $config)
    {
        $this->config = $config;
    }

    /**
     * Log a message with the given severity
     */
    abstract public function log($severity, $message);
}
