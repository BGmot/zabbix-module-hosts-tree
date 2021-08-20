<?php
/*
** Zabbix
** Copyright (C) 2001-2021 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

$this->includeJsFile('monitoring.host.view.refresh.js.php');

$form = (new CForm())->setName('host_view');

$table = (new CTableInfo());

$view_url = $data['view_curl']->getUrl();

$table->setHeader([
	make_sorting_header(_('Name'), 'name', $data['sort'], $data['sortorder'], $view_url),
	(new CColHeader(_('Interface'))),
	(new CColHeader(_('Availability'))),
	(new CColHeader(_('Tags'))),
	// Fix: problems renamed to triggers to distinguish from the problems counter column
	(new CColHeader(_('Triggers'))),
	make_sorting_header(_('Status'), 'status', $data['sort'], $data['sortorder'], $view_url),
	(new CColHeader(_('Latest data'))),
	(new CColHeader(_('Problems'))),
	(new CColHeader(_('Graphs'))),
	(new CColHeader(_('Dashboards'))),
	(new CColHeader(_('Web')))
]);


foreach ($data['host_groups'] as $group_name => $group) {
	if ($group['parent_group_name'] == '') {
		// Add only top level groups, children will be added recursively in addGroupRow()

		$child_stat = array('hosts_count'=>0, 'groups_count'=>0, 'severity'=>[]);
		$rows = [];
		addGroupRow($data, $rows, $group_name, '', 0, $child_stat);

		foreach ($rows as $idx=>$row) {
			$table->addRow($row);
		}
	}
}

$form->addItem([$table,	$data['paging']]);

echo $form;

// Added child stat as a parameter to display group's totals
// child stat is a severeties and total count array
// it's expected that function will return stats as summ of both of its subgroups and own hosts
// to recursively count the numbers

// Also, due to group and host counting is done during recursive calls and we need
// group summary information before renedering the group and its subgroups and
// hosts, so the function saves generatied rows to an intermediate array
// and then after having summaries, the array is copied to a "global" array
// which is rendered to the table in level 0
function addGroupRow($data, &$rows, $group_name, $parent_group_name, $level, &$child_stat) {
	$interface_types = [INTERFACE_TYPE_AGENT, INTERFACE_TYPE_SNMP, INTERFACE_TYPE_JMX, INTERFACE_TYPE_IPMI];
	$my_stat = array('hosts_count'=>0, 'groups_count'=>0, 'severity'=>[]);

	$group = $data['host_groups'][$group_name];

	$host_rows = [];
	foreach ($group['hosts'] as $hostid) {
		$host = $data['hosts'][$hostid];
		$host_name = (new CLinkAction($host['name']))->setMenuPopup(CMenuPopupHelper::getHost($hostid));

		$interface = null;
		if ($host['interfaces']) {
			foreach ($interface_types as $interface_type) {
				$host_interfaces = array_filter($host['interfaces'], function(array $host_interface) use ($interface_type) {
					return ($host_interface['type'] == $interface_type);
				});
				if ($host_interfaces) {
					$interface = reset($host_interfaces);
					break;
				}
			}
		}

		$problems_div = (new CDiv())->addClass(ZBX_STYLE_PROBLEM_ICON_LIST);
		$total_problem_count = 0;

		// Fill the severity icons by problem count and style, and calculate the total number of problems.
		// Need this to have cosntant order of triggers from disater to information.
		krsort($host['problem_count'],true);

		foreach ($host['problem_count'] as $severity => $count) {
			if (($count > 0 && $data['filter']['severities'] && in_array($severity, $data['filter']['severities']))
					|| (!$data['filter']['severities'] && $count > 0)) {
				$total_problem_count += $count;
				isset ($my_stat['severity'][$severity]) ? $my_stat['severity'][$severity] += $count:
											  $my_stat['severity'][$severity] = $count;

				$problems_div->addItem((new CSpan($count))
					->addClass(ZBX_STYLE_PROBLEM_ICON_LIST_ITEM)
					->addClass(getSeverityStatusStyle($severity))
					->setAttribute('title', getSeverityName($severity))
				);
			}
		}

		$maintenance_icon = '';

		if ($host['status'] == HOST_STATUS_MONITORED && $host['maintenance_status'] == HOST_MAINTENANCE_STATUS_ON) {
			if (array_key_exists($host['maintenanceid'], $data['maintenances'])) {
				$maintenance = $data['maintenances'][$host['maintenanceid']];
				$maintenance_icon = makeMaintenanceIcon($host['maintenance_type'], $maintenance['name'],
					$maintenance['description']
				);
			}
			else {
				$maintenance_icon = makeMaintenanceIcon($host['maintenance_type'],
					_('Inaccessible maintenance'), ''
				);
			}
		}

		$table_row_host = new CRow([
			(new CCol())
				//added a little bit of span to make the leveling prettier
				-> addItem(str_repeat('&nbsp;', 10 + $level*5))
				-> addItem($host_name)
				-> addItem($maintenance_icon),
			//[$host_name, $maintenance_icon],
			(new CCol(getHostInterface($interface)))->addClass(ZBX_STYLE_NOWRAP),
			getHostAvailabilityTable($host['interfaces']),
			$host['tags'],
			$problems_div,
			($host['status'] == HOST_STATUS_MONITORED)
				? (new CSpan(_('Enabled')))->addClass(ZBX_STYLE_GREEN)
				: (new CSpan(_('Disabled')))->addClass(ZBX_STYLE_RED),
			[
				$data['allowed_ui_latest_data']
					? new CLink(_('Latest data'),
						(new CUrl('zabbix.php'))
							->setArgument('action', 'latest.view')
							->setArgument('filter_set', '1')
							->setArgument('filter_hostids', [$host['hostid']])
					)
					: _('Latest data')
			],
			[
				$data['allowed_ui_problems']
					? new CLink(_('Problems'),
						(new CUrl('zabbix.php'))
							->setArgument('action', 'problem.view')
							->setArgument('filter_name', '')
							->setArgument('severities', $data['filter']['severities'])
							->setArgument('hostids', [$host['hostid']])
					)
					: _('Problems'),
				CViewHelper::showNum($total_problem_count)
			],
			$host['graphs']
				? [
					new CLink(_('Graphs'),
						(new CUrl('zabbix.php'))
							->setArgument('action', 'charts.view')
							->setArgument('filter_set', '1')
							->setArgument('filter_hostids', (array) $host['hostid'])
					),
					CViewHelper::showNum($host['graphs'])
				]
				: (new CSpan(_('Graphs')))->addClass(ZBX_STYLE_DISABLED),
			$host['dashboards']
				? [
					new CLink(_('Dashboards'),
						(new CUrl('zabbix.php'))
							->setArgument('action', 'host.dashboard.view')
							->setArgument('hostid', $host['hostid'])
					),
					CViewHelper::showNum($host['dashboards'])
				]
				: (new CSpan(_('Dashboards')))->addClass(ZBX_STYLE_DISABLED),
			$host['httpTests']
				? [
					new CLink(_('Web'),
						(new CUrl('zabbix.php'))
							->setArgument('action', 'web.view')
							->setArgument('filter_set', '1')
							->setArgument('filter_hostids', (array) $host['hostid'])
					),
					CViewHelper::showNum($host['httpTests'])
				]
				: (new CSpan(_('Web')))->addClass(ZBX_STYLE_DISABLED)
		]);

		addParentGroupClass($data, $table_row_host, $group_name);
		$host_rows[] = $table_row_host;
	}

	$subgroup_rows=[];

	foreach ($data['host_groups'][$group_name]['children'] as $child_group_name) {
		addGroupRow($data, $subgroup_rows, $child_group_name, $group_name, $level + 1, $my_stat);
	}


	$is_collapsed = $data['host_groups'][$group_name]['is_collapsed'];
	$toggle_tag = (new CSimpleButton())
		->addClass(ZBX_STYLE_TREEVIEW)
		->addClass('js-toggle')
		->addItem(
			(new CSpan())->addClass($is_collapsed ? ZBX_STYLE_ARROW_RIGHT : ZBX_STYLE_ARROW_DOWN)
	);
	$toggle_tag->setAttribute(
		'data-group_id_'.$data['host_groups'][$group_name]['groupid'],
		$data['host_groups'][$group_name]['groupid']
	);

	// Counting hosts/groups totals
	$hosts_count = count($data['host_groups'][$group_name]['hosts']);
	$groups_count = count($data['host_groups'][$group_name]['hosts']);

	isset($my_stat['hosts_count'])
		? $my_stat['hosts_count'] += $hosts_count
		: $my_stat['hosts_count'] = $hosts_count;

	isset($my_stat['groups_count'])
		? $my_stat['groups_count'] += $groups_count
		: $my_stat['groups_count'] = $groups_count;

	$group_name_arr = explode('/', $group_name);
	$group_name_short = end($group_name_arr) .
			'&nbsp;(' . $my_stat['hosts_count']. ')';

	$group_problems_div = (new CDiv())->addClass(ZBX_STYLE_PROBLEM_ICON_LIST);

	// Now we have all the stats, genarating own row
	// to make things look nice making a sorted severities array
	krsort($my_stat['severity']);

	foreach ($my_stat['severity'] as $severity => $count) {
		$group_problems_div->addItem((new CSpan($count))
			->addClass(ZBX_STYLE_PROBLEM_ICON_LIST_ITEM)
			->addClass(getSeverityStatusStyle($severity))
			->setAttribute('title', getSeverityName($severity))
		);
	}

	$table_row = new CRow([
		(new CCol())
			-> setColSpan(4)
			-> addItem(str_repeat('&nbsp;', $level*5))
			-> addItem($toggle_tag)
			-> addItem(bold($group_name_short)),
			$group_problems_div,
		(new CCol())
			-> setColSpan(7)
	]);

	// We don't render here, but just add rows to the array
	addParentGroupClass($data, $table_row, $parent_group_name);

	$rows[] = $table_row;

	// Now all subgroup rows
	foreach ($subgroup_rows as $idx=>$row) {
		$rows[] = $row;
	}

	// And finally, the hosts rows
	foreach ($host_rows as $idx=>$row) {
		$rows[] = $row;
	}

	//adding own statistics to the $child_stat
	foreach ($my_stat['severity'] as $severity => $count) {
		isset($child_stat['severity'][$severity])
				? $child_stat['severity'][$severity] += $count
				: $child_stat['severity'][$severity] = $count;
	}

	$child_stat['hosts_count'] += $my_stat['hosts_count'];
}

// Adds class 'data-group_id_<group_id>=<group_id>' to $element
function addParentGroupClass($data, &$element, $parent_group_name) {
	if ($parent_group_name != '') {
		// Do not add the class to top level groups, we don't need to hide them ever
		$element->addClass(ZBX_STYLE_DISPLAY_NONE);
		$element->setAttribute(
			'data-group_id_'.$data['host_groups'][$parent_group_name]['groupid'],
			$data['host_groups'][$parent_group_name]['groupid']
		);
	}
}
