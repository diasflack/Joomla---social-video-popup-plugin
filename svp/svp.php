<?php
/**
 * @version		0.0.1
 * @package		Social video popup (plugin)
 * @author    	diasflack - https://github.com/diasflack/
 * @copyright	Copyright (c) 2013 cleverleafs. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

//direct access restriction 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgContentSVP extends JPlugin { 
	var $plg_tag = "svp";
	var $plg_name = "svp";
	
	function plgContentSVP( &$subject ) {
		
        //stariting plugin constructor
        parent::__construct( $subject );
 
        //loading 
       if (!defined('DS')){
			define('DS', DIRECTORY_SEPARATOR);
		}
	}

	public function onContentPrepare($context, &$row , &$params, $page = 0){
		$plg_tag = "svp";
		
		$document=JFactory::getDocument();   
	 	
	 	$file_css = 'svp.css';
		$file_js = 'svp.js';
		$folder = '/svp/';
	 	
	 	$document->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'); //get jquery
        
        $siteUrl  = JURI::root(true);
        $pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name; //getting plugin path
        
		$regex = "#{".$this->plg_tag."}(.*?){/".$this->plg_tag."}#is"; //regular expression for tag name
		
		preg_match_all($regex, $row->text, $matches); //find matches on the page and get it to $matches array

		$count = count($matches[0]);// Number of founded mathes
		
		//Starrting tags replacment
		foreach ($matches[0] as $key => $match) {
			$tagcontent = preg_replace("/{.+?}/", "", $match);
			$you = '';
			
			$url = parse_url($tagcontent, PHP_URL_HOST); //getting url from matches
			
			//vimeo section
			if ($url === 'vimeo.com') {
				$imgid = (int) substr(parse_url($tagcontent, PHP_URL_PATH), 1);
				$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgid.php")); //getting video info through vimeo api
				$src = $hash[0]['thumbnail_large'];  //get thumbnail
				$title = $hash[0]['title']; //get title
				$tagOutput = '<a class="svp_video" href="'.$tagcontent.'"><img class="svp" src="'.$src.'" /><img class="splay" src="'.$pluginLivePath.'/play.png" /><h3 class="svp">'.$title.'</h3></a>';
			}
			
			//youtube section
			elseif ($url === 'www.youtube.com') {
			
				$cont = str_replace('amp;','', $tagcontent); //ampersand replasment cause its real troubles with it all the time
				parse_str(parse_url( $cont, PHP_URL_QUERY), $my_var);
				$src = $my_var['v']; //parse video id    
				
				$title_url = "http://gdata.youtube.com/feeds/api/videos/".$src; //getting youtube video info through id
				$doc = new DOMDocument();
				$doc->load($title_url);
				$name = $doc->getElementsByTagName("title")->item(0)->nodeValue;//get title
				$tagOutput = '<a class="svp_video" href="http://www.youtube.com/watch?v='.$src.'"><img class="svp" src="http://img.youtube.com/vi/'.$src.'/0.jpg" /><img class="play" src="'.$pluginLivePath.'/play.png" /><h3 class="svp">'.$name.'</h3></a>';
				
			}
		
		
		$row->text = preg_replace("#{".$plg_tag."}".preg_quote($tagcontent)."{/".$plg_tag."}#s", $tagOutput , $row->text);//plugin tag replacment with our video
		
		}
		
		//get fancybox
		$document->addCustomTag('<link href="'.$pluginLivePath.$folder.'/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />' );
        $document->addScript($pluginLivePath.$folder.'fancybox/jquery.fancybox.pack.js');
        $document->addScript($pluginLivePath.$folder.'fancybox/helpers/jquery.fancybox-media.js');
		
		//get styles and script
		$document->addCustomTag('<link href="'.$pluginLivePath.$folder.$file_css.'" rel="stylesheet" type="text/css" />' );
        $document->addScript($pluginLivePath.$folder.$file_js);
				
		}
		
}
 
?>
