<?php
/**
 * Plugin rssroll
 *
 * @package	PLX
 * @version	0.5
 * @date	10/04/2013
 * @author	i M@N
 * @based on	Rockyhorror Blogroll
 **/
 
	if(!defined('PLX_ROOT')) exit; 
	
	# Control du token du formulaire
	plxToken::validateFormToken($_POST);
	
	if(!empty($_POST)) {
		$plxPlugin->setParam('rssroll', $_POST['rssroll'], 'cdata');
		$plxPlugin->setParam('pub_title', $_POST['pub_title'], 'cdata');
		$plxPlugin->saveParams();
		header('Location: parametres_plugin.php?p=RSSroll');
		exit;
	}
?>

<h2><?php $plxPlugin->lang('L_TITLE') ?></h2>
<p><?php $plxPlugin->lang('L_CONFIG_DESCRIPTION') ?></p>

<form action="parametres_plugin.php?p=RSSroll" method="post">
	<fieldset class="withlabel">
		<p><?php echo $plxPlugin->getLang('L_CONFIG_ROOT_PATH') ?></p>
		<?php plxUtils::printInput('rssroll', $plxPlugin->getParam('rssroll'), 'text'); ?>
		
		<p><?php echo $plxPlugin->getLang('L_CONFIG_PUB_TITLE') ?></p>
		<?php plxUtils::printInput('pub_title', $plxPlugin->getParam('pub_title'), 'text'); ?>
	</fieldset>
	<br />
	<?php echo plxToken::getTokenPostMethod() ?>
	<input type="submit" name="submit" value="<?php echo $plxPlugin->getLang('L_CONFIG_SAVE') ?>" />
</form>
