<?php

/*************************************************************************************
* Links
**************************************************************************************/

function internalLinkToRelativeUrl($value)
{	
	return "/doku.php?id=" . substr(trim($value), 2, -2);
}

function wikiImageToUrl($wikiImage)
{
	//Test To See If Image Is In Wiki Format, If Not Just Return The Entire String
	if(preg_match("/:[^?}|&]+/", $wikiImage, $url))
	{
		return "/lib/exe/fetch.php?media=" . substr(trim($url[0]), 1);				
	}		
	
	return false;
}


?>