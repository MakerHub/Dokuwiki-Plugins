<?php
/********************************************************************************************************************************
*
* MakerHub Page Tile Plugin
*
* Written By Sam Kristoff
*
* www.github.com/makerhub
* www.labviewmakerhub.com
*
/*******************************************************************************************************************************/
  
 
// must be run within DokuWiki
if(!defined('DOKU_INC')) die();
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'syntax.php';

//Using PEAR Templates
require_once "HTML/Template/IT.php";

 

/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_makerhubincludepagetile extends DokuWiki_Syntax_Plugin 
{

	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@labviewmakerhub.com',
                     'date'   => '2015-01-19',
                     'name'   => 'MakerHub Inclue Page Tile',
                     'desc'   => 'MakerHub Include Page Tile Plugin',
                     'url'    => ' www.github.com/makerhub');
    }	
	
	//Store Variables To Render
	protected $page = '';
    protected $ns = '';
	protected $nameDelim = '';
	protected $nameDelimSide = 'before';
	protected $clearFloat = true;
	  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{MakerHub Include Page Tile.*?(?=.*?}})',$mode,'plugin_makerhubincludepagetile');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_makerhubincludepagetile');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_makerhubincludepagetile');
    }
	 
    function handle($match, $state, $pos, &$handler) 
	{			
		switch ($state) 
		{
		
			case DOKU_LEXER_ENTER :
				break;
			case DOKU_LEXER_MATCHED :					
				//Find The Token And Value (Before '=' remove white space, convert to lower case).
				$tokenDiv = strpos($match, '=');												//Find Token Value Divider ('=')
				$token = strtolower(trim(substr($match, 1, ($tokenDiv - 1))));	//Everything Before '=', Remove White Space, Convert To Lower Case
				$value = substr($match, ($tokenDiv + 1));									//Everything after '='
				switch($token)
				{
					case 'page':						
						$this->page = trim($value);
						break;
					case 'namespace':						
						$this->ns = trim($value);
						break;
					case 'name delimiter':						
						$this->nameDelim = trim($value);
						break;							
					case 'name delimiter side':						
						$this->nameDelimSide = strtolower(trim($value));
						break;
					case 'clear float':
						if( strtolower(trim($value)) == 'false')
						{
							$this->clearFloat = false;
						}
						else
						{
							$this->clearFloat = true;
						}
						break;	
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
			
				//Process Data					
			
				//Get File Path
				//$file = wikiFN($this->page);
				
				$thumbnailUrl = "";
				$targetOverview = "";
				$targetPageName = "";	
				$overviewMaxChars = 110;
				$output = "";	
				$nsDir = '';
				
				$wrap = false;
				
				//Prep Array Of All Pages To Include
				$pages = array();
				
						
				//Get All Pages In Namespace (Currently Does Not Include Sub Name Spaces)
				if($this->ns != '')
				{	
					
					//Start Container Div To Center Elements
					$output = "<div class='makerhubTileSet'>";	
					$wrap = true;
					
					$nsDir = substr(wikiFN($this->ns), 0, -4) . '/';
					
					$nsDirObjs = scandir($nsDir, 0);
					
					foreach($nsDirObjs as $key=>$object)
					{
						if(pathinfo($object)['extension'] != "txt")
						{
							unset($nsDirObjs[$key]);
						}						
					} 
					$pages = array_merge($pages, $nsDirObjs);
					
					//Sort By Page Title
					$index = 0;
					foreach($pages as $key=>$page)
					{
						$targetPageHeader = p_get_first_heading($this->ns . ":" . substr($page, 0, -4), false);
						if($targetPageHeader != "")
						{
							$pages[$key] = array(trim($targetPageHeader), $pages[$key]);
						}
						else
						{
							$pages[$key] = array("zzz", $pages[$key]);		//Force Sort To End
						}
						$index++;
					}
					sort($pages);
					
					foreach($pages as $key=>$page)
					{
						$pages[$key] = $page[1];
					}
				}
				
				if($this->page != '')
				{
					$pageName = substr($this->page, strrpos($this->page, ':') + 1);					
					$this->ns = substr($this->page, 0, (0-(strlen($pageName)+1)));		//+1 to get rid of trailing :
					$nsDir = substr(wikiFN($this->ns), 0, -4) . '/';
					array_push($pages, $pageName . ".txt");					
				}
				
				
				
				foreach($pages as $targetPage)
				{
					//Clear Defaults
					$thumbnailUrl = "";
					$targetOverview = "";
					$targetPageName = "";
					$properties = array();
					
					
					//Load Page Markup
					$pageMarkup = " " . file_get_contents($nsDir . $targetPage);
					
					//Check For MakeHub Thumbnail Data
					if( preg_match("/{{MakerHub Page Tile.*?\n}}/s", $pageMarkup, $matches) )
					{
						//Thumbnail Data Exists In Page, Parse Into Array				
						$lines = explode("\n|", $matches[0]);						
						
						//Remove Opening '{{...
						unset($lines[0]);
						
						//Remove Trailing '}}'
						end($lines);
						$lastKey = key($lines);
						$lines[$lastKey] = substr($lines[$lastKey], 0, -2);
						
						$index = 0;
						$propertyies= array();
						foreach ($lines as $propertLine)
						{
							$properties[$index] = explode("=", $propertLine);
							//Trim?
							$index++;
						}
						
						//Loop Over Properties And Set Variables
						foreach ($properties as $property)
						{
							switch( trim( strtolower($property[0]) ) )
							{
								case 'name':
									$targetPageName = $property[1];
									break;
								case 'thumbnail':
									//Convert Wiki Markup To Image URL If 
									$tempUrl = wikiImageToUrl($property[1]);
									if($tempUrl != false)
									{
										$thumbnailUrl = $tempUrl;
									}
									break;
								case 'overview':
									$targetOverview = $property[1];
									break;
								default:
									break;
							}
						}					
					}
					
					//If No Target Name Given Automatically Generate
					if($targetPageName == "")
					{
						$targetPageHeader = p_get_first_heading($this->ns . ":" . substr($targetPage, 0, -4), false);
						if($targetPageHeader != "")
						{
							$targetPageName = $targetPageHeader;
						}
					
						//Apply Delimiter
						if($this->nameDelim != "")
						{
							$expName = explode($this->nameDelim, $targetPageName);
							
							if($this->nameDelimSide == 'after')
							{
								$targetPageName = $expName[1];
							}
							else
							{
								$targetPageName = $expName[0];
							}
							
						}
					}
					
					
					//If No Overview Was Provided Get Default
					if($targetOverview == "")
					{
						
						if( preg_match("/\n[^=]*/", $pageMarkup, $matches) )
						{
							$targetOverview = trim($matches[0]);
							$targetOverview = explode("\n", $targetOverview)[0];	//Split on new line
							//Make Sure Overview Fits In Overlay
							if(strlen($targetOverview) > $overviewMaxChars)
							{
								$targetOverview = substr($targetOverview, 0, $overviewMaxChars) . "...";
							}							
						}						
					}
					
					//If No Thumbnail Was Provided Load Default
					if($thumbnailUrl == "")
					{
						//Try To Pull Youtube Thumb
						
						//Check For MakerHub Youtube Plugin
						if( preg_match("/{{MakerHub Youtube.*?\n}}/s", $pageMarkup, $matches) )
						{
							//Split out Youtube URL Value And Take Everything Before ? (? Includes Arguments Playlist That Don't Apply To Image URL)
							$thumbnailUrl  =  "https://img.youtube.com/vi/" . explode('?', trim(substr(trim(explode('=', explode('|', $matches[0])[1])[1]), 0, -3)))[0] . "/mqdefault.jpg";
						}
						else
						{
							//Default Thumbnail Image	
							$thumbnailUrl = '/lib/plugins/makerhubincludepagetile/img/defaultThumbnail.png';
						}
					}
					
										
					/***********************************************************************************************************
					* Append Tile Data To Output
					***********************************************************************************************************/
					$template = new HTML_Template_IT(dirname(__FILE__));			//Create a new HTML_Template_IT instance and set template search path.
					$template->loadTemplatefile("template.tpl.html", true, true);		//Load Template
					$template->setVariable ('THUMBNAIL', $thumbnailUrl); 
					$template->setVariable ('TARGET_URL', 'doku.php?id=' . $this->ns . ":" . substr($targetPage, 0, -4)); 
					$template->setVariable ('OVERVIEW', substr(p_render('xhtml',p_get_instructions($targetOverview)), 4, -5)); 
					$template->setVariable ('NAME', $targetPageName); 
					
					
					$output .= $template->get();
					
					
				}
				
				//Clear Float After Last Tile
				if($this->clearFloat)
				{
					$output .="<div class='clearFloat'></div>";
				}
				
				//Close Out Div That Contains All Tiles
				if($wrap)
				{
					$output .= "</div>";					
				}
				
				
				
				//Clear Variables So That This Plugin Can Be Used Multiple Times In A Single Page
				$this->page = '';
				$this->ns = '';
				$this->nameDelim = '';
				$this->nameDelimSide = 'before';
				$this->clearFloat = true;
								
				//Pass Data The Renderer
				return array($state, $output);
				//return array($state, "Static Text!");
				
				break;
			case DOKU_LEXER_SPECIAL :
				break;
		}
		
		return array($state, $match);
    }
 
    function render($mode, &$renderer, $data) 
	{
    // $data is what the function handle return'ed.
        if($mode == 'xhtml')
		{		
			
			$renderer->doc .= $this->fullName;
			switch ($data[0]) 
			{
			  case DOKU_LEXER_ENTER : 
				//Initialize Table	
				break;
			  case DOKU_LEXER_MATCHED :
				//Add Table Elements Based On Type		
				break;
			  case DOKU_LEXER_UNMATCHED :
				//Ignore
				break;
			  case DOKU_LEXER_EXIT :
				//Close Elements	
				
				//Separate Data
				$output = $data[1];
							
				$renderer->doc .= $output;
				
				break;
			  case DOKU_LEXER_SPECIAL :
				//Ignore
				if($this->lvhDebug) $renderer->doc .= 'SPECIAL';		//Debug
				break;
			}			
            return true;
        }
        return false;
    }
}

	