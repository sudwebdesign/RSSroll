<?php if(!defined('PLX_ROOT')) exit; ?>

<h2>Help</h2>
<p>RSSroll plugin help file</p>

<p>&nbsp;</p>
<h3>Installation</h3>
<p>Activate plugin.<br/>
Edit the template file "sidebar.php". Add following code where you want to see your links:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
			&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSroll&#039;)); ?&gt;
		&lt;/ul&gt;
</pre>
<p>&nbsp;</p>
<p>If you want to specify format:</p>
<pre>
	&lt;h3&gt;&lt;?php eval($plxShow-&gt;callHook(&#039;showRSSrollHead&#039;)); ?&gt;&lt;/h3&gt;
		&lt;ul&gt;
&lt;?php eval($plxShow-&gt;callHook('showRSSroll', '&lt;h2 style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"&gt;
&lt;a target="_blank" href="#url" hreflang="#langue" title="#description"&gt;#title&lt;/a&gt;
&lt;/h2&gt;')); ?&gt;
		&lt;/ul&gt;
</pre>


<p>&nbsp;</p>
<h3>Usage</h3>
<p>
This plugin adds an entry "RSSroll" on the left side of the site administration page to manage your RSS.
</p>

<p>&nbsp;</p>
<h3>Configuration</h3>
<p>
in the configuration part of the plugin (Parameters > plugins > RSSroll configuration), you can change the configuration xml file location and the title that appears in the sidebar of the public part.
</p>
