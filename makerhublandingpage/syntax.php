<?php
/********************************************************************************************************************************
*
* MakerHub Library Landing Page Template
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

require_once "lib/plugins/makerhubcommon.php";
 

/********************************************************************************************************************************
* All DokuWiki plugins to extend the parser/rendering mechanism
* need to inherit from this class
********************************************************************************************************************************/
class syntax_plugin_makerhublandingpage extends DokuWiki_Syntax_Plugin 
{

	//Return Plugin Info
	function getInfo() 
	{
        return array('author' => 'Sam Kristoff',
                     'email'  => 'admin@labviewmakerhub.com',
                     'date'   => '2015-01-19',
                     'name'   => 'MakerHub Library Landing Page',
                     'desc'   => 'MakerHub Library Landing Page Template',
                     'url'    => ' www.github.com/makerhub');
    }	
	
	//Store Variables To Render
	protected $name = '';
	protected $youtubeId = "";
	protected $logo ='';
	protected $tagLine = '';
	protected $description = '';
	protected $gettingStartedUrl = '';
	protected $downloadUrl = '';
	protected $forumsUrl = '';
	protected $tutorialsUrl = '';
	protected $faqUrl = '';
	protected $projectsUrl = '';
	  
    function getType() { return 'protected'; }
    function getSort() { return 32; }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern('{{MakerHub Landing Page.*?(?=.*?}})',$mode,'plugin_makerhublandingpage');
		
		//Add Internal Pattern Match For Product Page Elements	
		$this->Lexer->addPattern('\|.*?(?=.*?)\n','plugin_makerhublandingpage');
    }
	
    function postConnect() {
      $this->Lexer->addExitPattern('}}','plugin_makerhublandingpage');
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
						$this->name = substr(p_render('xhtml',p_get_instructions($value)), 4, -5);
						break;
					case 'youtube id':						
						$this->youtubeId = trim($value);
						break;
					case 'logo':
						$this->logo = wikiImageToUrl(trim($value));
						break;
					case 'tag line':						
						$this->tagLine = substr(p_render('xhtml',p_get_instructions($value)), 4, -5);
						break;
					case 'description':						
						$this->description = substr(p_render('xhtml',p_get_instructions($value)), 4, -5);
						break;
					case 'getting started url':						
						$this->gettingStartedUrl = internalLinkToRelativeUrl($value);
						break;	
					case 'download url':						
						$this->downloadUrl = $value;
						break;	
					case 'forums url':						
						$this->forumsUrl = $value;
						break;	
					case 'tutorials url':						
						$this->tutorialsUrl = internalLinkToRelativeUrl($value);
						break;	
					case 'faq url':						
						$this->faqUrl = internalLinkToRelativeUrl($value);
						break;	
					case 'projects url':						
						$this->projectsUrl = internalLinkToRelativeUrl($value);
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
				$logoHTML = 'DEFAULT LOGO';
				
				if($this->logo != '')
				{
					$logoHTML = "<img src='" . $this->logo . "'>";
				}
				
				if($this->youtubeId != '')
				{
					$logoHTML = "<iframe src='//www.youtube.com/embed/" . $this->youtubeId . "' frameborder='0' allowfullscreen></iframe>";
				}

				
				$template = new HTML_Template_IT(dirname(__FILE__));			//Create a new HTML_Template_IT instance and set template search path.
				$template->loadTemplatefile("template.tpl.html", true, true);		//Load Template
				$template->setVariable ('NAME', $this->name); 
				$template->setVariable ('LOGO', $logoHTML); 
				$template->setVariable ('TAG_LINE', $this->tagLine); 
				$template->setVariable ('DESCRIPTION', $this->description); 
				$template->setVariable ('GETTINGSTARTED_URL', $this->gettingStartedUrl); 
				$template->setVariable ('DOWNLOAD_URL', $this->downloadUrl); 
				$template->setVariable ('FORUMS_URL', $this->forumsUrl); 
				$template->setVariable ('TUTORIALS_URL', $this->tutorialsUrl); 
				$template->setVariable ('FAQ_URL', $this->faqUrl); 
				$template->setVariable ('PROJECTS_URL', $this->projectsUrl); 
				
				$output = $template->get();
				
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
				$instTemplate = $data[1];
							
				$renderer->doc .= $instTemplate;
				
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
