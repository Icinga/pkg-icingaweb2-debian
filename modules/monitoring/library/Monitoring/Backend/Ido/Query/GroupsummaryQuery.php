<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Module\Monitoring\Backend\Ido\Query;

use Zend_Db_Select;

class GroupSummaryQuery extends IdoQuery
{
    protected $useSubqueryCount = true;

    protected $columnMap = array(
        'hoststatussummary' => array(
            'hosts_up'                      => 'SUM(CASE WHEN object_type = \'host\' AND state = 0 THEN 1 ELSE 0 END)',
            'hosts_unreachable'             => 'SUM(CASE WHEN object_type = \'host\' AND state = 2 THEN 1 ELSE 0 END)',
            'hosts_unreachable_handled'     => 'SUM(CASE WHEN object_type = \'host\' AND state = 2 AND acknowledged + in_downtime != 0 THEN 1 ELSE 0 END)',
            'hosts_unreachable_unhandled'   => 'SUM(CASE WHEN object_type = \'host\' AND state = 2 AND acknowledged + in_downtime = 0 THEN 1 ELSE 0 END)',
            'hosts_down'                    => 'SUM(CASE WHEN object_type = \'host\' AND state = 1 THEN 1 ELSE 0 END)',
            'hosts_down_handled'            => 'SUM(CASE WHEN object_type = \'host\' AND state = 1 AND acknowledged + in_downtime != 0 THEN 1 ELSE 0 END)',
            'hosts_down_unhandled'          => 'SUM(CASE WHEN object_type = \'host\' AND state = 1 AND acknowledged + in_downtime = 0 THEN 1 ELSE 0 END)',
            'hosts_pending'                 => 'SUM(CASE WHEN object_type = \'host\' AND state = 99 THEN 1 ELSE 0 END)',
            'hostgroup'                     => 'hostgroup',
            'hostgroup_alias'               => 'hostgroup_alias'
        ),
        'servicestatussummary' => array(
            'services_total'                                => 'SUM(CASE WHEN object_type = \'service\' THEN 1 ELSE 0 END)',
            'services_ok'                                   => 'SUM(CASE WHEN object_type = \'service\' AND state = 0 THEN 1 ELSE 0 END)',
            'services_pending'                              => 'SUM(CASE WHEN object_type = \'service\' AND state = 99 THEN 1 ELSE 0 END)',
            'services_warning'                              => 'SUM(CASE WHEN object_type = \'service\' AND state = 1 THEN 1 ELSE 0 END)',
            'services_warning_handled'                      => 'SUM(CASE WHEN object_type = \'service\' AND state = 1 AND acknowledged + in_downtime + host_state > 0 THEN 1 ELSE 0 END)',
            'services_critical'                             => 'SUM(CASE WHEN object_type = \'service\' AND state = 2 THEN 1 ELSE 0 END)',
            'services_critical_handled'                     => 'SUM(CASE WHEN object_type = \'service\' AND state = 2 AND acknowledged + in_downtime + host_state > 0 THEN 1 ELSE 0 END)',
            'services_unknown'                              => 'SUM(CASE WHEN object_type = \'service\' AND state = 3 THEN 1 ELSE 0 END)',
            'services_unknown_handled'                      => 'SUM(CASE WHEN object_type = \'service\' AND state = 3 AND acknowledged + in_downtime + host_state > 0 THEN 1 ELSE 0 END)',
            'services_warning_unhandled'                    => 'SUM(CASE WHEN object_type = \'service\' AND state = 1 AND acknowledged + in_downtime + host_state = 0 THEN 1 ELSE 0 END)',
            'services_critical_unhandled'                   => 'SUM(CASE WHEN object_type = \'service\' AND state = 2 AND acknowledged + in_downtime + host_state = 0 THEN 1 ELSE 0 END)',
            'services_unknown_unhandled'                    => 'SUM(CASE WHEN object_type = \'service\' AND state = 3 AND acknowledged + in_downtime + host_state = 0 THEN 1 ELSE 0 END)',
            'services_severity'                             => 'MAX(CASE WHEN object_type = \'service\' THEN severity ELSE 0 END)',
            'services_ok_last_state_change'                 => 'MAX(CASE WHEN object_type = \'service\' AND state = 0 THEN state_change ELSE 0 END)',
            'services_pending_last_state_change'            => 'MAX(CASE WHEN object_type = \'service\' AND state = 99 THEN state_change ELSE 0 END)',
            'services_warning_last_state_change_handled'    => 'MAX(CASE WHEN object_type = \'service\' AND state = 1 AND acknowledged + in_downtime + host_state > 0 THEN state_change ELSE 0 END)',
            'services_critical_last_state_change_handled'   => 'MAX(CASE WHEN object_type = \'service\' AND state = 2 AND acknowledged + in_downtime + host_state > 0 THEN state_change ELSE 0 END)',
            'services_unknown_last_state_change_handled'    => 'MAX(CASE WHEN object_type = \'service\' AND state = 3 AND acknowledged + in_downtime + host_state > 0 THEN state_change ELSE 0 END)',
            'services_warning_last_state_change_unhandled'  => 'MAX(CASE WHEN object_type = \'service\' AND state = 1 AND acknowledged + in_downtime + host_state = 0 THEN state_change ELSE 0 END)',
            'services_critical_last_state_change_unhandled' => 'MAX(CASE WHEN object_type = \'service\' AND state = 2 AND acknowledged + in_downtime + host_state = 0 THEN state_change ELSE 0 END)',
            'services_unknown_last_state_change_unhandled'  => 'MAX(CASE WHEN object_type = \'service\' AND state = 3 AND acknowledged + in_downtime + host_state = 0 THEN state_change ELSE 0 END)',
            'servicegroup'                                  => 'servicegroup',
            'servicegroup_alias'                            => 'servicegroup_alias'
        )
    );

    protected function joinBaseTables()
    {
        $columns = array(
            'object_type',
            'host_state'
        );

        if (in_array('servicegroup', $this->desiredColumns)) {
            $columns[] = 'servicegroup';
            $columns[] = 'servicegroup_alias';
            $groupColumns = array('servicegroup', 'servicegroup_alias');
        } else {
            $columns[] = 'hostgroup';
            $columns[] = 'hostgroup_alias';
            $groupColumns = array('hostgroup', 'hostgroup_alias');
        }
        $hosts = $this->createSubQuery(
            'Hoststatus',
            $columns + array(
                'state'        => 'host_state',
                'acknowledged' => 'host_acknowledged',
                'in_downtime'  => 'host_in_downtime',
                'state_change' => 'host_last_state_change',
                'severity'     => 'host_severity'
            )
        );
        if (in_array('servicegroup', $this->desiredColumns)) {
            $hosts->group(array(
                'sgo.name1',
                'ho.object_id',
                'sg.alias',
                'state',
                'acknowledged',
                'in_downtime',
                'state_change',
                'severity'
            ));
        }
        $services = $this->createSubQuery(
            'Status',
            $columns + array(
                'state'        => 'service_state',
                'acknowledged' => 'service_acknowledged',
                'in_downtime'  => 'service_in_downtime',
                'state_change' => 'service_last_state_change',
                'severity'     => 'service_severity'
            )
        );
        $union = $this->db->select()->union(array($hosts, $services), Zend_Db_Select::SQL_UNION_ALL);
        $this->select->from(array('statussummary' => $union), array())->group($groupColumns);
        $this->joinedVirtualTables = array(
            'servicestatussummary'  => true,
            'hoststatussummary'     => true
        );
    }
}
