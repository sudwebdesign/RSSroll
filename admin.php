<?php if(!defined('PLX_ROOT')) exit; 

# Control du token du formulaire
plxToken::validateFormToken($_POST);


$plxPlugin->getRSSroll(PLX_ROOT.PLX_CONFIG_PATH.'/plugins/'.$plxPlugin->getParam('rssroll'));

# On �dite les cat�gories
if(!empty($_POST)) {
	$plxPlugin->editRSSlist($_POST);
	header('Location: plugin.php?p=RSSroll');
	exit;
}

?>

<h2><?php $plxPlugin->lang('L_TITLE'); ?></h2>

<form action="plugin.php?p=RSSroll" method="post" id="form_rssroll">

	<table class="table">
	<thead>
		<tr>
			<th class="checkbox"><input type="checkbox" onclick="checkAll(this.form, 'idRSSroll[]')" /></th>
			<th class="title"><?php $plxPlugin->lang('L_ADMIN_LIST_ID'); ?></th>
			<th><?php $plxPlugin->lang('L_ADMIN_LIST_NAME') ?></th>
			<th><?php $plxPlugin->lang('L_ADMIN_LIST_URL') ?></th>
			<th><?php $plxPlugin->lang('L_ADMIN_LIST_DESC') ?></th>
			<th><?php $plxPlugin->lang('L_ADMIN_LIST_LANG') ?></th>
			<th><?php $plxPlugin->lang('L_ADMIN_LIST_ORDER') ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$num = 0;
		if ($plxPlugin->rssList) {
			foreach($plxPlugin->rssList as $k=>$v) {
				$ordre = ++$num;
				echo '<tr class="line-'.($num%2).'">';
				echo '<td><input type="checkbox" name="idRSSroll[]" value="'.$k.'" /><input type="hidden" name="rssNum[]" value="'.$k.'" /></td>';
				echo '<td>'.$plxPlugin->getLang('L_ADMIN_LIST_LINK').' '.$k.'</td><td>';
				plxUtils::printInput($k.'_title', plxUtils::strCheck($v['title']), 'text', '15-50');
				echo '</td><td>';
				plxUtils::printInput($k.'_url', plxUtils::strCheck($v['url']), 'text', '25-150');
				echo '</td><td>';
				plxUtils::printInput($k.'_description', plxUtils::strCheck($v['description']), 'text', '25-150');
				echo '</td><td>';
				plxUtils::printInput($k.'_langue', plxUtils::strCheck($v['langue']), 'text', '2-3');
				echo '</td><td>';
				plxUtils::printInput($k.'_ordre', $ordre, 'text', '3-3');
				echo '</td></tr>';
			}
			# On r�cup�re le dernier identifiant
			$a = array_keys($plxPlugin->rssList);
			rsort($a);
		}
		else {
			$a['0'] = 0;
		}
		$new_rssid = str_pad($a['0']+1, 3, "0", STR_PAD_LEFT);
		 ?>
		 <tr class="new">
			
			<?php
				echo '<td><input type="hidden" name="rssNum[]" value="'.$new_rssid.'" /></td>';
				echo '<td>'.$plxPlugin->getLang('L_ADMIN_LIST_NEW').'</td><td>';
				plxUtils::printInput($new_rssid.'_title', '', 'text', '15-50');
				echo '</td><td>';
				plxUtils::printInput($new_rssid.'_url', '', 'text', '25-150');
				echo '</td><td>';
				plxUtils::printInput($new_rssid.'_description', '', 'text', '25-150');
				echo '</td><td>';
				plxUtils::printInput($new_rssid.'_langue', '', 'text', '2-3');
				echo '</td><td>';
				plxUtils::printInput($new_rssid.'_ordre', ++$num, 'text', '3-3');
				echo '</td>';
			?>
		</tr>
	</tbody>
	</table>

	<p class="center">
	<?php echo plxToken::getTokenPostMethod() ?>
		<input class="button update " type="submit" name="update" value="<?php $plxPlugin->lang('L_ADMIN_APPLY_BUTTON'); ?>" />
	</p>
	<p>
		<?php plxUtils::printSelect('selection', array( '' => $plxPlugin->getLang('L_ADMIN_SELECTION'), 'delete' => $plxPlugin->getLang('L_ADMIN_DELETE')), '') ?>
		<input class="button submit" type="submit" name="submit" value="<?php echo L_OK ?>" />
	</p>

</form>

