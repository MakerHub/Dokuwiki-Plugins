function validateShareForm()
{
	var form = document.forms["shareForm"];
	var userName = form["name"].value;	
	var email = form["email"].value;	
	var projectName = form["projectName"].value;	
	var mainLink = form["mainLink"].value;	
	var description = form["description"].value;	
	var links = form["links"].value;	
	
	//Validate Username
	if(userName == null || userName == "")
	{
		alert("Please enter your Name");
		return false;
	}
	
	//Validate Email
	var emailRegex = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    if(!emailRegex.test(email))
	{
		alert("Please enter a valid Email address.");
		return false;
	}
	
	//Validate Project Name
	if(projectName == null || projectName == "")
	{
		alert("Please a Project Name.");
		return false;
	}
	
	//Validate Main Link
	if(mainLink == null || mainLink == "")
	{
		alert("Please provide at least one video or image link.");
		return false;
	}
	
	//Validate Description
	if(description == null || description == "")
	{
		alert("Please provide a Project Description.");
		return false;
	}
	
	//Eveerything Passed
	return true;
	
}