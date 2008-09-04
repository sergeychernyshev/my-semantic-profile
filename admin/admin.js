function switchLanguage(langval, module, defaultlang, personURI)
{
	var lang;

	if (langval.indexOf('-') == 0)
	{
		lang = prompt('Enter language code');
	}
	else
	{
		lang = langval;
	}

	if (!lang)
	{
		return;
	}

	var newlocation = './';

	var sep = '?';

	if (lang != defaultlang)
	{
		newlocation+='?lang='+lang;
		sep = '&';
	}

	if (module)
	{
		newlocation += sep+'module='+module;
		sep = '&';
	}

	if (personURI)
	{
		newlocation += sep+'personURI='+personURI;
		sep = '&';
	}

	document.location = newlocation;
}
