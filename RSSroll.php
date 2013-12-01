<?php
/**
 * Plugin rssroll
 *
 * @package	PLX
 * @version	1.3 
 * @date	01/12/2013
 * @author	i M@N, thom@s
 * @based on	Rockyhorror Blogroll
 * @disclaimer	may content unexpected lulz
 **/
 

class RSSroll extends plxPlugin {
	public $config = null; # fichier des données
	public $rssList = array(); # Tableau des rss
	public $sourcejs = null; # source jgfeed
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
		
		if(defined('PLX_CONF')) # version PluXml < 5.1.7
				$this->config = dirname(PLX_CONF).'/'.$this->getParam('rssroll');
		else # version PluXml >= 5.1.7
				$this->config = PLX_ROOT.PLX_CONFIG_PATH.'/plugins/'.$this->getParam('rssroll');
		
		
		# Autorisation d'acces à la configuration du plugin
		$this->setConfigProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Autorisation d'accès à l'administration du plugin
		$this->setAdminProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Déclarations des hooks
		$this->addHook('showRSSrollHead', 'showRSSrollHead');
		$this->addHook('showRSSroll','showRSSroll');
		
		if (($this->getParam('curlonly') == 'non')){/*check for curl*/
			$this->addHook('ThemeEndBody', 'ThemeEndBody');
		}
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

	public function getRSSroll() {
		
		if(!is_file($this->config)) return;
		
		# Mise en place du parseur XML
		$data = implode('',file($this->config));
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
			
			# On écrit le fichier ## Origin ##
			/*
			if(plxUtils::write($xml, PLX_ROOT.$this->getParam('rssroll')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else {
				$this->rssList = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.$filename);
			}
			*/
			# On écrit le fichier ## New ##
			if(plxUtils::write($xml, $this->config))
				return plxMsg::Info(L_SAVE_SUCCESSFUL.' '.$this->config);
			else {
				$this->rssList = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.$this->config);
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
		$curl = 0;
		$footJs ='';
		if ((extension_loaded('curl')) && ($this->getParam('curlonly') == 'oui')){/*check for curl*/
			$curl = 1;
			#echo 'curl : '.$curl;//yeah that's just 4 debug ; )
			require_once(PLX_PLUGINS."RSSroll/lib/simplepie.php");/*curl use simplepie*/
		}
		$this->getRSSroll($this->config);
		if(!$this->rssList) { return; }
		if(!isset($format)) { $format = '<h2 style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"><a target="_blank" href="#url" hreflang="#langue" title="#description">#title</a></h2>'; }
		foreach($this->rssList as $link) {
				$row = str_replace('"#url"','"#url"',$format);
				$row = str_replace('#url',$link['url'],$row);
				if ($curl == 1) {/*get favicon*/
					$this->grab_image('http://g.etfv.co/'.$link['url'],md5($link['url']).'.ico');
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
					$feed = new SimplePie();
					$feed -> set_feed_url($link['url']);
					$feed -> set_cache_location(PLX_PLUGINS . 'RSSroll/cache');
					$feed -> enable_cache(true);
					$feed -> set_cache_duration(3600);
					/*This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it)*/
					$feed->handle_content_type();
					$feed->init();
					/*Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.*/
					foreach ($feed->get_items($start,$length) as $item):
						echo '<li class="feed"><a target="_blank" href="'.$item->get_permalink().'" title="'.$item->get_title().'">'.$item->get_title().'</a></li>';
						echo '<div class="description">'.$this->tronque($item->get_description(),$this->getParam('truncate')).'</div>';
						echo '<p class="date">'.$this->getLang('L_POSTED_ON').' : '.$item->get_date('Y/m/d h:i').'</p>';
					endforeach;
				}
				else {
					/*javascript fall back mode*/
					echo '<div id="RSSroll-'.$start.'"></div>';
					$footJs .=  '
					/*javascript '.$link['url'].' fallback mode*/
					$(function(){
						var feed = \''.$link['url'].'\';
						var limit = \''.$length.'\';

						$.jGFeed(feed,
						function(feeds){
							if(feeds.error){// there was an error
								return false;
							}
							for(var i=0;i<feeds.entries.length;i++){
								var entry = feeds.entries[i];
								//console.dir(entry);//uncomment to display console log
								var title = entry.title;
								var link = \'href="\' +entry.link+ \'"\';//my pluxml add my host if href place in a.attribute, why? (little hack)
								//var categories = entry.categories;//uncomment to display categories
								var description = entry.content;//uncomment to display description
								//var descriptionSnippet = entry.contentSnippet;//uncomment to display snippet description
								var pubDate = new Date(entry.publishedDate);//entry.publishedDate;

								var html = \'\';
								html += \'<li class="feed"><a target="_blank" \' + link + \'" title="\' + title + \'">\' + title + \'</a></li>\';
								//html += \'<p class="categories">\' + categories + \'</p>\';//uncomment to display categories
								html += \'<div id="RSSroll'.$start.'entry\' + i +\'" class="description">\' + description + \'</div>\';//uncomment to display description
								//html += \'<p class="description snippet">\' + descriptionSnippet + \'</p>\';//uncomment to display snippet description
								html += \'<p class="date">'.$this->getLang('L_POSTED_ON').' : \' + pubDate.toLocaleString() + \'</p>\';

								$("#RSSroll-'.$start.'").append($(html));//$(html).truncated(36)
								$("#RSSroll'.$start.'entry"+ i).succinct({size: '.plxUtils::strCheck($this->getParam("truncate")).'});/*truncate*/
							}
						}, limit);
					})';
				}// fi else
				$start++;
			}/*fi foreach*/
		if ($curl == 0) {$this->sourcejs = $footJs;}//echo $footJs;		
	}
	
	public function ThemeEndBody() {/* for jGfeed */
?>
	<script type="text/javascript">
	if (typeof jQuery == 'undefined') {
		document.write('<script type="text\/javascript" src="<?php echo PLX_PLUGINS ?>RSSroll\/js\/jquery.min.js"><\/script>');
	}
	</script>
	<script type="text/javascript" src="<?php echo PLX_PLUGINS ?>RSSroll/js/jquery.jgfeed.js"></script>
	<script type="text/javascript" src="<?php echo PLX_PLUGINS ?>RSSroll/js/jQuery.succinct.min.js"></script>
	<script type="text/javascript">
	   $(document).ready(function(){
		  <?php	echo $this->sourcejs; ?>
		});
	</script>
<?php
	}
	
	public function tronque($chaine, $longueur = 369){/* for curl*/
        if (empty ($chaine)){return "";}
        elseif (strlen ($chaine) < $longueur){return $chaine;}
        elseif (preg_match ("/(.{1,$longueur})\s./ms", $chaine, $match)){return $match [1] . "...";}
        else{return substr ($chaine, 0, $longueur) . "...";}
    }
}
	
?>
