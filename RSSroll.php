<?php
/**
 * Plugin rssroll
 *
 * @package	PLX
 * @version	1.2
 * @date	09/11/2013
 * @author	i M@N
 * @based on	Rockyhorror Blogroll
 * @disclaimer	may content unexpected lulz
 **/
 

class RSSroll extends plxPlugin {

	public $rssList = array(); # Tableau des rss
	
	/**
	 * Constructeur de la classe rssroll
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	i M@N
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);
		
		# Autorisation d'acces à la configuration du plugin
		$this->setConfigProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Autorisation d'accès à l'administration du plugin
		$this->setAdminProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Déclarations des hooks
		$this->addHook('showRSSrollHead', 'showRSSrollHead');
		$this->addHook('showRSSroll','showRSSroll');
	}

	public function OnActivate() {
		$plxMotor = plxMotor::getInstance();
		if (version_compare($plxMotor->version, "5.1.7", ">=")) {
			if (!file_exists(PLX_ROOT."data/configuration/plugins/RSSroll.xml")) {
				if (!copy(PLX_PLUGINS."RSSroll/parameters.xml", PLX_ROOT."data/configuration/plugins/RSSroll.xml")) {
					return plxMsg::Error(L_SAVE_ERR.' '.PLX_PLUGINS."RSSroll/parameters.xml");
				}
			}
		}
	}

	public function getRSSroll($filename) {
		
		if(!is_file($filename)) return;
		
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		if(isset($iTags['rssroll']) AND isset($iTags['title'])) {
			$nb = sizeof($iTags['title']);
			$size=ceil(sizeof($iTags['rssroll'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['rssroll'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation du titre
				$this->rssList[$number]['title']=plxUtils::getValue($values[$iTags['title'][$i]]['value']);
				# Recuperation du nom de la description
				$this->rssList[$number]['description']=plxUtils::getValue($values[$iTags['description'][$i]]['value']);
				# Recuperation de l'url
				$this->rssList[$number]['url']=plxUtils::getValue($values[$iTags['url'][$i]]['value']);
				# Recuperation de la langue
				$this->rssList[$number]['langue']=plxUtils::getValue($values[$iTags['langue'][$i]]['value']);
				
			}
		}
		
	}
	
	/**
	 * Méthode qui édite le fichier XML du rssroll selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel du rssroll
	 * @param	action	permet de forcer la mise àjour du fichier
	 * @return	string
	 * @author	Stephane F
	 **/
	public function editRSSlist($content, $action=false) {

		$save = $this->rssList;
		
		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idRSSroll'])) {
			foreach($content['idRSSroll'] as $rssroll_id) {
				unset($this->rssList[$rssroll_id]);
				$action = true;
			}
		}
		
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			foreach($content['rssNum'] as $rss_id) {
				$rss_name = $content[$rss_id.'_title'];
				if($rss_name!='') {
					$this->rssList[$rss_id]['title'] = $rss_name;
					$this->rssList[$rss_id]['url'] = $content[$rss_id.'_url'];
					$this->rssList[$rss_id]['description'] = $content[$rss_id.'_description'];
					$this->rssList[$rss_id]['langue'] = $content[$rss_id.'_langue'];
					$this->rssList[$rss_id]['ordre'] = intval($content[$rss_id.'_ordre']);
					$action = true;
				}
			}

		}
		# On va trier les clés selon l'ordre choisi
		if(sizeof($this->rssList)>0) uasort($this->rssList, create_function('$a, $b', 'return $a["ordre"]>$b["ordre"];'));
		
		# sauvegarde
		if($action) {
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->rssList as $rss_id => $rss) {

				$xml .= "\t<rssroll number=\"".$rss_id."\">";
				$xml .= "<title><![CDATA[".plxUtils::cdataCheck($rss['title'])."]]></title>";
				$xml .= "<description><![CDATA[".plxUtils::cdataCheck($rss['description'])."]]></description>";
				$xml .= "<url><![CDATA[".plxUtils::cdataCheck($rss['url'])."]]></url>";
				$xml .= "<langue><![CDATA[".plxUtils::cdataCheck($rss['langue'])."]]></langue>";
				$xml .= "</rssroll>\n";
			}
			$xml .= "</document>";
			
			# On écrit le fichier
			if(plxUtils::write($xml, PLX_ROOT.$this->getParam('rssroll')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else {
				$this->rssList = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.$filename);
			}			
		}
	}

	public function showRSSrollHead () {
		$title = plxUtils::strCheck($this->getParam('pub_title'));
		echo $title;
	}

	/**
	 * Méthode qui récupère le favicon de l'url et le met en cache
	 *
	 * @param	url	url du favicon à récupérer
	 * @param	saveto	nom du favicon = md5(url)
	 * @return	string
	 * @author	i M@N
	 **/
	public function grab_image($url,$saveto) {
/*favicon dir check*/
	if (!is_dir(PLX_PLUGINS."RSSroll/cache/favicon/")) {
	mkdir(PLX_PLUGINS."RSSroll/cache/favicon/");
	}
/*grab favicon*/
	if(!file_exists(PLX_PLUGINS."RSSroll/cache/favicon/".$saveto)){
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	$raw=curl_exec($ch);
	curl_close ($ch);
	$fp = fopen(PLX_PLUGINS."RSSroll/cache/favicon/".$saveto,'x');
	fwrite($fp, $raw);
	fclose($fp);
	}
}
	public function showRSSroll($format) {
/*default starting item*/
$start = 0;
/*default number of items to display. 0 = all*/
$length = 5;

if (extension_loaded('curl')) {
/*check for curl*/
	$curl = 1;
	#echo 'curl : '.$curl;//yeah that's just 4 debug ; )

/*curl use simplepie*/
	# lib/simplepie
	require_once(PLX_PLUGINS."RSSroll/lib/simplepie.php");
}
else {
/*use javascript fallback*/
/*require PluXML jQuery plugin*/
	echo '<script type="text/javascript">
/* <![CDATA[ */
	!window.jQuery && document.write(\'<script type="text/javascript" src="<?php echo PLX_PLUGINS;?>jquery/jquery.min.js"><\/script>\');
	!window.jQuery.jGFeed && document.write(\'<script type="text/javascript" src="<?php echo PLX_PLUGINS;?>RSSroll/js/jquery.jgfeed.js"><\/script>\');
/* !]]> */
</script>
';
}
		$this->getRSSroll(PLX_ROOT.$this->getParam('rssroll'));
		if(!$this->rssList) { return; }
		
#		if(!isset($format)) { $format = '<h2 style="background:url(\'http://g.etfv.co/#url\') no-repeat scroll 0 5px transparent;padding-left:20px;"><a target="_blank" href="#url" hreflang="#langue" title="#description">#title</a></h2>'; }
		if(!isset($format)) { $format = '<h2 style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"><a target="_blank" href="#url" hreflang="#langue" title="#description">#title</a></h2>'; }

		foreach($this->rssList as $link) {
if ($curl == 1) {
/*get favicon*/
		$this->grab_image('http://g.etfv.co/'.$link['url'],md5($link['url']).'.ico');
}
#echo '<img src="'PLX_PLUGINS.'RSSroll/cache/favicon/'. md5($link['url']).'.ico" height="16px" width="16px" title="favicon" alt="favicon" />';//could also display img

##			$row = str_replace('"#url"','"#url" onclick="window.open(this.href);return false;"',$format);
			$row = str_replace('"#url"','"#url"',$format);
			$row = str_replace('#url',$link['url'],$row);
if ($curl == 1) {
			$row = str_replace('#icon',PLX_PLUGINS.'RSSroll/cache/favicon/'.md5($link['url']).'.ico',$row);
}
else {
			$row = str_replace('#icon','http://g.etfv.co/'.$link['url'],$row);
}
			$row = str_replace('#description',plxUtils::strCheck($link['description']),$row);
			$row = str_replace('#title',plxUtils::strCheck($link['title']),$row);
			$row = str_replace('#langue',plxUtils::strCheck($link['langue']),$row);
			echo $row;
if ($curl == 1) {
/*We'll process this feed with some options*/
##$feed = new SimplePie($link['url'], PLX_PLUGINS . 'RSSroll/cache');//deprecated
$feed = new SimplePie();
$feed -> set_feed_url($link['url']);
$feed -> set_cache_location(PLX_PLUGINS . 'RSSroll/cache');
$feed -> enable_cache(true);
$feed -> set_cache_duration(3600);
/*This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it)*/
$feed->handle_content_type();
$feed->init();
##echo '<h2 style="background:url('.$feed->get_favicon().') no-repeat scroll 0 5px transparent;padding-left:20px;"><a target="_blank" href="'.$feed->get_permalink().'">'.$feed->get_title().'</a></h2>';//deprecated get_favicon()
#echo '<li>'.$feed->get_description().'</li>';//uncomment to display description
/*Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.*/
foreach ($feed->get_items($start,$length) as $item):
echo '<li><a target="_blank" href="'.$item->get_permalink().'" title="'.$item->get_title().'">'.$item->get_title().'</a> '.$item->get_date('Y/m/d').'</li>';
#echo '<p>'.$item->get_description().'</p>';
#echo '<p><small>Posted on '.$item->get_date('Y/m/d h:i').'</small></p>';
endforeach;
}
else {
/*javascript fall back mode*/
echo '<script type="text/javascript">
/*javascript '.$link['url'].' fallback mode*/
$(document).ready(function() {
var feed = \''.$link['url'].'\';
var limit = \''.$length.'\';
$.jGFeed(feed,
function(feeds){
if(!feeds){
// there was an error
return false;
}

for(var i=0;i<feeds.entries.length;i++){
var entry = feeds.entries[i];
//console.dir(entry);//uncomment to display console log
var title = entry.title;
var link = entry.link;
//var categories = entry.categories;//uncomment to display categories
//var description = entry.content;//uncomment to display description
var pubDate = entry.publishedDate;

var html = \'\';
html += \'<li id="entry-\' + i +\'">\';
html += \'<a target="_blank" href="\' + link + \'" title="\' + title + \'">\' + title + \'</a> \' + pubDate;
//html += \'<span class="categories">\' + categories + \'</span>\';//uncomment to display categories
//html += \'<span class="description">\' + description + \'</span>\';//uncomment to display description
html += \'</li>\';
$("#RSSroll-'.$start.'").append($(html));
}
}, limit);

})
</script>
<div id="RSSroll-'.$start.'"></div>';
}
$start++;
		}
		
	}
}
	
?>
