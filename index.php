<?
/**
 * This program helps manage person's user profile using Semantic Web standards such as FOAF
 * $Id$
 */

$SPROOT = './';

/**
 * Uncomment to enable debugging functionality
 */
#include_once('debug.php');

include_once('global_functions.inc.php');

/**
 * Configuration parameters
 */
include_once('config.inc.php');

$model = getModel();
$personURI = getPrimaryPerson($model);

if ($personURI == null)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
<head profile="http://www.w3.org/2006/03/hcard">
	<title>Can't find person</title>
	<link type="text/css" rel="stylesheet" href="profile.css" />
	<script type="text/javascript" src="floatbox/floatbox.js"></script>
</head>
<body>
<div style="width: 300px; text-align: center; padding: 10px; border: 1px solid grey">
<p>Can't find a person for this profile.<br/>
<a href="admin/">Edit profile</a> to fix the problem.</p>
<div><a href="admin/"><img src="admin.png" alt="Click here to edit this profile" style="border: 0px"/></a></div>
</div>
</body>
</html>
<?

	exit;
}

$lang = $defaultlang;

if (array_key_exists('lang', $_REQUEST))
{
	$lang = $_REQUEST['lang'];
}

/**
 * Let's get person's name
 */
$names = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$name = $statement->getObject();
	$names[getLiteralLanguage($name)][]=$name;
}

$title = 'Profile';

$otherNamesText = '';
$personName = null;

if (array_key_exists($lang, $names) && count($names[$lang]) > 0)
{
	$personName = array_shift($names[$lang]);

	foreach ($names[$lang] as $name)
	{
		// apparently hCard allows only one fn (formatted name)
		$otherNamesText .= ' AKA <span property="foaf:name"'.xmlLang(getLiteralLanguage($name)).'>'.$name->getLabel().'</span>';
	}
}
elseif (array_key_exists($defaultlang, $names) && count($names[$defaultlang]) > 0)
{
	$personName = array_shift($names[$defaultlang]);
}

header('Vary: Accept');
#if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) 
#    header("Content-Type: application/xhtml+xml; charset=utf-8");
#else
    header("Content-Type: text/html; charset=utf-8");

require_once('URL2.php');
$pageuri = new Net_URL2($_SERVER['SCRIPT_URI']);
$profilefulluri = $pageuri->resolve($profileDocumentURI);
$resolvedPersonURI = $profilefulluri->resolve($personURI->getURI())->getURL();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
<head profile="http://www.w3.org/2006/03/hcard">
	<title><?=($personName ? $personName->getLabel() : 'Noname')?></title>
	<link rel="meta" type="application/rdf+xml" title="FOAF" href="<?=$profileDocumentURI ?>" />
	<link rel="alternate" type="application/rdf+xml" title="<?=$title ?> (RDF)" href="<?=$profileDocumentURI ?>" />
	<link type="text/css" rel="stylesheet" href="floatbox/floatbox.css" />
	<link type="text/css" rel="stylesheet" href="profile.css" />
	<script type="text/javascript" src="floatbox/floatbox.js"></script>
</head>
<body about="">
<div id="adminbutton"><a href="admin/"><img src="admin.png" alt="Click here to edit this profile" style="border: 0px"/></a></div>

<div id="qranduri">
Person URI:<br/>
<img src="http://chart.apis.google.com/chart?chs=200x200&amp;cht=qr&amp;chl=<?=urlencode($resolvedPersonURI)?>&amp;choe=UTF-8" title="Person URI: <?=$resolvedPersonURI?>" alt="QR code for <?=$resolvedPersonURI?>"/><br/>
<span style="font-weight: bold" href="<?=$resolvedPersonURI?>" rel="foaf:primaryTopic" rev="foaf:isPrimaryTopicOf"><?=$resolvedPersonURI?></span>
</div>
<?
$model_languages = getModelLanguages($model);

unset($model_languages[$defaultlang]); // we'll show default language first
unset($model_languages['']); // no need to show non-defined language (we assume it's the same as default)

/*
 * If there is more then one language present
 */
if (count($model_languages))
{
	?><div id="langnav">languages: <?
	if ($lang == $defaultlang)
	{
		?><b><?=$defaultlang?></b><?	
	}
	else
	{
		?><a rel="alternate" href="./" hreflang="<?=$defaultlang?>"><?=$defaultlang?></a><?
	}

	foreach (array_keys($model_languages) as $l)
	{
		if ($l == $lang)
		{
			?> - <b><?=$l?></b><?
		}
		else
		{
			?> - <a rel="alternate" hreflang="<?=$l?>" href="?lang=<?=urlencode($l)?>"><?=$l?></a><?
		}
	}
	?></div>
<?
}
?>
<div class="vcard" about="<?=$resolvedPersonURI?>">
<span rev="foaf:maker" rel="foaf:made" href=""/>
<h1><span class="fn" property="foaf:name"<?=xmlLang(getLiteralLanguage($personName))?>><?=($personName ? $personName->getLabel() : 'Noname')?></span> <a rel="rdfs:seeAlso" href="<?=$profileDocumentURI ?>" title="My FOAF document"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a></h1>
<?
if ($otherNamesText)
{
?><p><?=$otherNamesText?></p><?
}

// Running all display modules
include_once('modules/base.inc.php');
foreach ($display_modules as $display_module)
{

	$display_module->displayContent($model, $personURI, $lang);
}


?>
</div>
<div style="border-top: 1px solid silver; padding: 5px; align: center; margin-top: 20px">
<a href="http://validator.w3.org/check/referrer" title="Check XHTML + RDFa validity"><img src="http://www.w3.org/Icons/valid-xhtml-rdfa-blue" alt="Valid XHTML + RDFa" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=referer" title="Extract RDF from RDFa on this page"><img src="http://www.w3.org/Icons/SW/Buttons/sw-rdfa-orange.png" alt="Extract RDF from RDFa on this page" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://hcard.geekhood.net/?url=<?=urlencode($_SERVER['SCRIPT_URI'].'?'.$_SERVER['QUERY_STRING'])?>" title="Show hCard information on this page"><img src="hcard.png" alt="Show hCard information on this page" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://gmpg.org/xfn/" title="XFN Homepage"><img src="xfn-btn.gif" alt="XFN" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://microformats.org/wiki/geo" title="Geo Microformat Page"><img src="geo.png" alt="geo" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
</div>
<div style="border-top: 1px solid silver; padding: 5px; align: center; font-size: small; text-align: center">Powered by <a href="http://code.google.com/p/my-semantic-profile/">My Semantic Profile</a> (v.<?=$version?> r<?=$build?>)</div>
</body>
</html>
