<?php
/********************************************************************************************************************************
*
* MakerHub Previous / Next Button Plugin
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
class syntax_plugin_makerhubprevnext extends DokuWiki_Syntax_Plugin 
{

	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@labviewmakerhub.com',
                     'date'   => '2015-03-26',
                     'name'   => 'MakerHub Previous / Next Button Plugin',
                     'desc'   => 'MakerHub Previous / Next Button Plugin',
                     'url'    => ' www.github.com/makerhub');
    }	
	
	//Store Variables To Render
	
	  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{MakerHub Previous Next Button.*?(?=.*?}})',$mode,'plugin_makerhubprevnext');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_makerhubprevnext');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_makerhubprevnext');
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
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
							
					//Get Pages In Current Namespace
					$pageInfo = pageinfo();		
					$currentNamespace = $pageInfo["namespace"];					
					
					$nsFolder = DOKU_INC . "data/pages/" . str_replace(':', '/', $currentNamespace);
					
					//echo  "hello ->" . $nsFolder;
															
					$nsDirObjs = scandir($nsFolder, 0);
					
					foreach($nsDirObjs as $key=>$object)
					{
						if(pathinfo($object)['extension'] != "txt")
						{
							unset($nsDirObjs[$key]);
						}						
					} 
					
					$pageFiles =$nsDirObjs;		//All Page Files In The Current Namespace
					
					//Sort By Page Title
					$index = 0;
					foreach($pageFiles as $key=>$page)
					{
						$targetPageHeader = p_get_first_heading($currentNamespace . ":" . substr($page, 0, -4), false);
						if($targetPageHeader != "")
						{
							$pageFiles[$key] = array(trim($targetPageHeader), $pageFiles[$key]);
						}
						else
						{
							$pageFiles[$key] = array("zzz", $pageFiles[$key]);		//Force Sort To End
						}
						$index++;
					}
					
					sort($pageFiles);
					//print_r($pageFiles);
					
					//Find Current Page Index
					$index = 0;
					$currentPageIndex = -1;
					foreach($pageFiles as $page)
					{
						if(basename($pageInfo["filepath"]) == $page[1])
						{
							$currentPageIndex = $index;
							break;
						}
						$index++;
					}
					
					//echo "Index = " . $currentPageIndex;					
					
					$nextUrl = "";
					$previousUrl = "";
					
					//First Page, Previous Button Should Point To Series Overview Page
					if($currentPageIndex == 0)
					{
						$previousUrl = "/doku.php?id=" . $pageInfo["namespace"];
					}
					else
					{
						$previousUrl = "/doku.php?id=" . $pageInfo["namespace"] . ":" . substr($pageFiles[$currentPageIndex-1][1], 0, -4);
					}
					//Last Page, Next Button Should Point To Series Overview Page
					if($currentPageIndex == count($pageFiles)-1)
					{
						$nextUrl = "/doku.php?id=" . $pageInfo["namespace"];
					}
					else
					{
						$nextUrl = "/doku.php?id=" . $pageInfo["namespace"] . ':' . substr($pageFiles[$currentPageIndex+1][1], 0, -4);
					}
					
					
					//echo "Previous = " . $previousUrl . "<br />";
					//echo "Next = " . $nextUrl . "<br />";
					
					
					
					
					//echo $pageInfo["filepath"];
					//echo $pageInfo["filepath"];
					
					//print_r($pageFiles);
										
					/***********************************************************************************************************
					* Append Tile Data To Output
					***********************************************************************************************************/
					$template = new HTML_Template_IT(dirname(__FILE__));			//Create a new HTML_Template_IT instance and set template search path.
					$template->loadTemplatefile("template.tpl.html", true, false);		//Load Template
					$template->setVariable ('PREVIOUSURL', $previousUrl); 								
					$template->setVariable ('NEXTURL', $nextUrl);
					$output .= $template->get();
					
								
				//Clear Variables So That This Plugin Can Be Used Multiple Times In A Single Page
											
				
								
				//Pass Data The Renderer
				return array($state, $output);
				
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

	