<?php
/*
 * services_igmpproxy.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2004-2016 Electric Sheep Fencing, LLC
 * All rights reserved.
 *
 * originally based on m0n0wall (http://m0n0.ch/wall)
 * Copyright (c) 2003-2004 Manuel Kasper <mk@neon1.net>.
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

##|+PRIV
##|*IDENT=page-services-igmpproxy
##|*NAME=Services: IGMP Proxy
##|*DESCR=Allow access to the 'Services: IGMP Proxy' page.
##|*MATCH=services_igmpproxy.php*
##|-PRIV

require_once("guiconfig.inc");

if (!is_array($config['igmpproxy']['igmpentry'])) {
	$config['igmpproxy']['igmpentry'] = array();
}

//igmpproxy_sort();
$a_igmpproxy = &$config['igmpproxy']['igmpentry'];

if ($_POST) {
	$pconfig = $_POST;

	$retval = 0;
	/* reload all components that use igmpproxy */
	$retval = services_igmpproxy_configure();

	if (stristr($retval, "error") <> true) {
		$savemsg = get_std_save_message($retval);
	} else {
		$savemsg = $retval;
	}

	clear_subsystem_dirty('igmpproxy');
}

if ($_GET['act'] == "del") {
	if ($a_igmpproxy[$_GET['id']]) {
		unset($a_igmpproxy[$_GET['id']]);
		write_config();
		mark_subsystem_dirty('igmpproxy');
		header("Location: services_igmpproxy.php");
		exit;
	}
}

$pgtitle = array(gettext("Services"), gettext("IGMP Proxy"));
include("head.inc");

if ($savemsg) {
	print_info_box($savemsg, 'success');
}

if (is_subsystem_dirty('igmpproxy')) {
	print_apply_box(gettext('The IGMP entry list has been changed.') . '<br />' . gettext('The changes must be applied for them to take effect.'));
}
?>

<form action="services_igmpproxy.php" method="post">
	<div class="panel panel-default">
		<div class="panel-heading"><h2 class="panel-title"><?=gettext('IGMP Proxy')?></h2></div>
		<div class="panel-body">
			<div class="table-responsive">
				<table class="table table-striped table-hover table-condensed table-rowdblclickedit">
					<thead>
						<tr>
							<th><?=gettext("Name")?></th>
							<th><?=gettext("Type")?></th>
							<th><?=gettext("Values")?></th>
							<th><?=gettext("Description")?></th>
							<th><?=gettext("Actions")?></th>
						</tr>
					</thead>
					<tbody>
<?php
$i = 0;
foreach ($a_igmpproxy as $igmpentry):
?>
						<tr>
							<td>
								<?=htmlspecialchars(convert_friendly_interface_to_friendly_descr($igmpentry['ifname']))?>
							</td>
							<td>
								<?=htmlspecialchars($igmpentry['type'])?>
							</td>
							<td>
<?php
	$addresses = implode(", ", array_slice(explode(" ", $igmpentry['address']), 0, 10));
	print($addresses);

	if (count($addresses) < 10) {
		print(' ');
	} else {
		print('...');
	}
?>
							</td>
							<td>
								<?=htmlspecialchars($igmpentry['descr'])?>&nbsp;
							</td>
							<td>
								<a class="fa fa-pencil"	title="<?=gettext('Edit IGMP entry')?>" href="services_igmpproxy_edit.php?id=<?=$i?>"></a>
								<a class="fa fa-trash"	title="<?=gettext('Delete IGMP entry')?>" href="services_igmpproxy.php?act=del&amp;id=<?=$i?>"></a>
							</td>
						</tr>
<?php
	$i++;
endforeach;
?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</form>

<nav class="action-buttons">
	<button id="submit" name="submit" type="submit" class="btn btn-primary btn-sm" value="<?=gettext("Save")?>">
		<i class="fa fa-save icon-embed-btn"></i>
		<?=gettext("Save")?>
	</button>
	<a href="services_igmpproxy_edit.php" class="btn btn-success btn-sm">
		<i class="fa fa-plus icon-embed-btn"></i>
		<?=gettext('Add')?>
	</a>
</nav>

<div class="infoblock">
<?php print_info_box(gettext('Please add the interface for upstream, the allowed subnets, and the downstream interfaces for the proxy to allow. ' .
					   'Only one "upstream" interface can be configured.'), 'info', false); ?>
</div>
<?php
include("foot.inc");
