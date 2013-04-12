<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Aide</h2>
<p>Fichier d&#039;aide du plugin RSSroll</p>

<p>&nbsp;</p>
<h3>Installation</h3>
<p>Pensez &agrave; activer le plugin.<br/>
Editez le fichier template "sidebar.php". Ajoutez y le code suivant &agrave; l&#039;endroit o&ugrave; vous souhaitez voir apparaitre les liens:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
			&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSroll&#039;)); ?&gt;
		&lt;/ul&gt;
</pre>
<p>&nbsp;</p>
<p>Si vous souhaitez changer le format d&#039;affichage:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
&lt;?php eval($plxShow-&gt;callHook('showRSSroll', '&lt;h2 style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"&gt;
&lt;a target="_blank" href="#url" hreflang="#langue" title="#description"&gt;#title&lt;/a&gt;
&lt;/h2&gt;')); ?&gt;
		&lt;/ul&gt;
</pre>


<p>&nbsp;</p>
<h3>Utilisation</h3>
<p>
	Le plugin ajoute une entrée "blogroll" dans la barre des menus à gauche, dans l&#039;administration du site.<br>
	Vous pouvez administrer vos liens à partir de cette page.	
</p>

<p>&nbsp;</p>
<h3>Configuration</h3>
<p>
	Dans la configuration du plugin (Paramètres > plugins > RSSroll configuration), vous pouvez:
	<ul>
		<li>Changer l&#039;emplacement du fichier xml</li>
		<li>Changer le titre qui s&#039;affiche dans la sidebar de la partie publique.</li>
	</ul>
</p>
