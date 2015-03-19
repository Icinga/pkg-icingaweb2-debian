<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Monitoring\DataView;

/**
 * Describes the data needed by the Contactgroup DataView
 */
class Contactgroup extends DataView
{

    /**
     * Retrieve columns provided by this view
     *
     * @return array
     */
    public function getColumns()
    {
        return array(
            'contact',
            'contact_name',
            'contactgroup',
            'contactgroup_name',
            'contactgroup_alias',
            'host',
            'service'
        );
    }

    /**
     * Retrieve default sorting rules for particular columns. These involve sort order and potential additional to sort
     *
     * @return array
     */
    public function getSortRules()
    {
        return array(
            'contactgroup_name' => array(
                'order' => self::SORT_ASC
            ),
            'contactgroup_alias' => array(
                'order' => self::SORT_ASC
            )
        );
    }
}
