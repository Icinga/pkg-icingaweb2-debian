<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Module\Monitoring\Command\Object;

/**
 * Schedule a host downtime
 */
class ScheduleHostDowntimeCommand extends ScheduleServiceDowntimeCommand
{
    /**
     * (non-PHPDoc)
     * @see \Icinga\Module\Monitoring\Command\Object\ObjectCommand::$allowedObjects For the property documentation.
     */
    protected $allowedObjects = array(
        self::TYPE_HOST
    );

    /**
     * Whether to schedule a downtime for all services associated with a particular host
     *
     * @var bool
     */
    protected $forAllServices = false;

    /**
     * Set whether to schedule a downtime for all services associated with a particular host
     *
     * @param   bool $forAllServices
     *
     * @return  $this
     */
    public function setForAllServices($forAllServices = true)
    {
        $this->forAllServices = (bool) $forAllServices;
        return $this;
    }

    /**
     * Get whether to schedule a downtime for all services associated with a particular host
     *
     * @return bool
     */
    public function getForAllServices()
    {
        return $this->forAllServices;
    }
}
