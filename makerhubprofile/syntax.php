<?php
/********************************************************************************************************************************
*
* MakerHub Profile Plugin
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
class syntax_plugin_makerhubprofile extends DokuWiki_Syntax_Plugin 
{

	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@labviewmakerhub.com',
                     'date'   => '2015-03-26',
                     'name'   => 'MakerHub Profile',
                     'desc'   => 'MakerHub Profile Plugin',
                     'url'    => ' www.github.com/makerhub');
    }	
	
	//Store Variables To Render
	protected $name = '';
	protected $thumbnail = '';
	protected $overview = '';
	protected $links = '';

	
	
	  
	  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{MakerHub Profile.*?(?=.*?}})',$mode,'plugin_makerhubprofile');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_makerhubprofile');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_makerhubprofile');
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
					case 'name':						
						$this->name = trim($value);
						break;
					case 'thumbnail':						
						$this->thumbnail = wikiImageToUrl(trim($value));
						break;	
					case 'overview':						
						$this->overview = trim($value);
						break;
					case 'links':						
						$this->links = trim($value);
						break;	
														
					
					default:
						break;
				}
				return array($state, $value);
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
							
										
					/***********************************************************************************************************
					* Append Tile Data To Output
					***********************************************************************************************************/
					$template = new HTML_Template_IT(dirname(__FILE__));			//Create a new HTML_Template_IT instance and set template search path.
					$template->loadTemplatefile("template.tpl.html", true, true);		//Load Template
					$template->setVariable ('NAME', $this->name);					
					
					if($this->thumbnail != "")
					{
						$template->setVariable ('THUMBNAIL', $this->thumbnail); 
					}
					
					if($this->overview != "")
					{
						$template->setVariable ('OVERVIEW', p_render('xhtml',p_get_instructions($this->overview)));
					}
										
					if($this->links != "")
					{						
						$template->setVariable ('LINKS', substr(p_render('xhtml',p_get_instructions($this->links)), 4, -5)); 
					}
					
					$output .= $template->get();
					
								
				//Clear Variables So That This Plugin Can Be Used Multiple Times In A Single Page
				$this->name = '';											
				
								
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

	