<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Module\Monitoring\Backend\Livestatus\Query;

use Icinga\Protocol\Livestatus\Query;
// SHITTY IT IS
class ServicegroupQuery extends Query
{
    protected $table = 'servicegroups';

    protected $available_columns = array(
        'servicegroup_name'   => 'name',
        'servicegroup_alias'  => 'alias',
        'host'                => array('members'),
        'host_name'           => array('members'),
        'service'             => array('members'),
        'service_host_name'   => array('members'),
        'service_description' => array('members'),
    );

    public function xxcombineResult_service_host_name(& $row, & $res)
    {
    return;
    var_dump($res);
        die('Here you go');
    }


    public function completeRow(& $row)
    {
    die('FU');
        $row->severity = 12;
    }
}
