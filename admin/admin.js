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

/*
 * Let's go through the document and create an array of new_entries_* form nodes
 */
var newEntriesForms = new Array();
var newEntriesSubFormClones = new Array();

function initNewEntries()
{
	var slug;

	newEntriesForms = getElementsByClassPattern('new_entries_(.+)', document, 'div');

	for (slug in newEntriesForms)
	{
		subform = newEntriesForms[slug];
		newEntriesSubFormClones[slug] = subform.getElementsByTagName('div')[0].cloneNode(true);

		var addbutton = document.createElement('a');
		addbutton.setAttribute('href', '#');
		addbutton.setAttribute('module', slug);
		addbutton.onclick = function() {
			addNewEntries(this.getAttribute('module'));
			return false;
		}
		addbutton.appendChild(document.createTextNode('+1 more ...'));

		subform.parentNode.insertBefore(addbutton, subform.nextSibling);
	}
}

function addNewEntries(slug)
{
	newEntriesForms[slug].appendChild(newEntriesSubFormClones[slug].cloneNode(true));
}

/*
 * Adapted from http://www.dustindiaz.com/getelementsbyclass/ to use regex match
 *
 * Pattern can have groups first of which is converted to array index
 * if no groups match, then numeric indices are used.
 */
function getElementsByClassPattern(searchClassPattern, node, tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(?:^|\\\\s)"+searchClassPattern+"(?:\\\\s|$)");

	for (i = 0, j = 0; i < elsLen; i++) {
		var matches = pattern.exec(els[i].className);
		if ( matches ) {
			if (matches[1])
			{
				classElements[matches[1]] = els[i];
			}
			else
			{
				classElements[j] = els[i];
				j++;
			}
		}
	}
	return classElements;
}
