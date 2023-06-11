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


/**
 * @var CView $this
 */

?>

<script type="text/javascript">
	// Transfer information about groups from PHP into JavaScript data Object
	var groups = [];
	var data = <?php
		echo '{';
		foreach ($data['host_groups'] as $group_name => $group) {
			if (count($group['children']) > 0) {
				echo "'".$group['groupid']."':[";
				print_children($data, $group);
				echo "],";
			}
		}
		echo '}';
		function print_children($data, $group) {
			$num_of_children = count($group['children']);
			$index = 0;
			foreach($group['children'] as $child_group_name) {
				$child_group_id = $data['host_groups'][$child_group_name]['groupid'];

				echo "'" . $child_group_id . "'";
				if ($index < $num_of_children-1) {
					echo ',';
				}

				$index++;
			}
		} ?>;

	function toggleChevronCollapsed($chevron, collapsed) {
		$chevron
			.removeClass(collapsed ? '<?= ZBX_STYLE_ARROW_DOWN ?>' : '<?= ZBX_STYLE_ARROW_RIGHT ?>')
			.addClass(collapsed ? '<?= ZBX_STYLE_ARROW_RIGHT ?>' : '<?= ZBX_STYLE_ARROW_DOWN ?>');
	}

	function isChevronCollapsed($chevron) {
		return $chevron.hasClass('<?= ZBX_STYLE_ARROW_RIGHT ?>');
	}

	function toggleGroup(group_id, collapsed) {
		var $chevron = $('.js-toggle[data-group_id_' + group_id + '="' + group_id + '"] span'),
			$rows = $('tr[data-group_id_' + group_id + '="' + group_id + '"]');

		toggleChevronCollapsed($chevron, collapsed);

		$rows.toggleClass('<?= ZBX_STYLE_DISPLAY_NONE ?>', collapsed);
	}

	$('.js-toggle').on('click', function() {
		var $toggle = $(this),
			collapsed = !isChevronCollapsed($toggle.find('span'));
		var group_id = 0;
		for (const key  in $toggle[0].attributes) {
			var attr = $toggle[0].attributes[key];
			if (attr.name.startsWith('data-')) {
				group_id = attr.value
				break;
			}
		};
		toggleGroup(group_id, collapsed);
		if (collapsed) {
			if (group_id in data) {
				collapseSubgroup(group_id);
			}
		}
	});

	function collapseSubgroup(group_id) {
		toggleGroup(group_id, true);
		for (var i = 0; i < data[group_id].length; i++) {
			if (data[group_id][i] in data) {
				collapseSubgroup(data[group_id][i]);
			} else {
				toggleGroup(data[group_id][i], true);
			}
		}
	}
</script>
