<?
/**
 * This program helps manage person's user profile using Semantic Web standards such as FOAF
 */

/**
 * Automated version generator
 * $Id$
 */
$version = '0.3';

preg_match('$'.'Rev: (\d+) $', '$Rev$', $matches);
$build = $matches[1];

$SPROOT = './';
/**
 * Configuration parameters
 */
include_once('config.inc.php');

/**
 * Uncomment to enable debugging functionality
 */
#include_once('debug.php');

include_once('global_functions.inc.php');

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
<body class="vcard" about="<?=$personURI->getURI()?>">
<div style="float:right"><a href="admin/"><img src="admin.png" alt="Click here to edit this profile" style="border: 0px"/></a></div>
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
		?><a rel="rdfs:seeAlso" href="./"><?=$defaultlang?></a><?
	}

	foreach (array_keys($model_languages) as $l)
	{
		if ($l == $lang)
		{
			?> - <b><?=$l?></b><?
		}
		else
		{
			?> - <a rel="rdfs:seeAlso" href="?lang=<?=urlencode($l)?>"><?=$l?></a><?
		}
	}
	?></div>
<?
}
?>
<h1><span class="fn" property="foaf:name"<?=xmlLang(getLiteralLanguage($personName))?>><?=($personName ? $personName->getLabel() : 'Noname')?></span> <a rel="rdfs:seeAlso" href="<?=$profileDocumentURI ?>" title="My FOAF document"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a></h1>
<p><?=$otherNamesText?></p>
<?

/**
 * Let's get person's primary pictures (img) and show it's thumbnail if it exists or just resized it to 100 hight
 */
$query = 'PREFIX foaf: <'.$foaf.'>
select ?image, ?thumbnail
where {
<'.$personURI->getURI().'> foaf:img ?image .
OPTIONAL { ?image foaf:thumbnail ?thumbnail } 
}';
#echo "$query\n";
$images = $model->sparqlQuery($query);

if ($images)
{
?>
<h2>Images</h2>
<div id="images">
<?
	foreach ($images as $image)
	{
		?><span rel="foaf:img" resource="<?=$image['?image']->getURI() ?>"><a rel="gallery1" class="photo" href="<?=$image['?image']->getURI() ?>" title="<?=($personName ? $personName->getLabel()."'s photo" : 'photo')?>"><img src="<?=($image['?thumbnail'] ? $image['?thumbnail']->getURI() : $image['?image']->getURI()) ?>" class="thumbnail" alt="<?=($personName ? $personName->getLabel()."'s photo" : 'photo')?>" rev="foaf:thumbnail" resource="<?=$image['?image']->getURI() ?>"/></a></span>
<?
	}
?></div><?
}

/**
 * Now let's prng person's links
 */

/*
 * Homepages
 */
$query = 'PREFIX foaf: <'.$foaf.'>
PREFIX dc: <'.$dc.'>
select ?homepage, ?homepagetitle
where {
<'.$personURI->getURI().'> foaf:homepage ?homepage .
OPTIONAL { ?homepage dc:title ?homepagetitle } 
}';
#echo "$query\n";
$homepages = $model->sparqlQuery($query);

foreach ($homepages as $homepage)
{
	$homepagestodisplay[$homepage['?homepage']->getURI()] = array();
}

foreach ($homepages as $homepage)
{
	if ($homepage['?homepagetitle'])
	{
		$homepagestodisplay[$homepage['?homepage']->getURI()][getLiteralLanguage($homepage['?homepagetitle'])] =
			$homepage['?homepagetitle']->getLabel();
	}
}

if (count($homepagestodisplay))
{
	?><h2>Homepages</h2>
<div id="homepages"><ul><?

	foreach ($homepagestodisplay as $homepage => $languages)
	{
		if (array_key_exists($lang, $languages))
		{
			?><li rel="foaf:homepage"><a class="url" rel="me" about="<?=$homepage ?>" property="dc:title" href="<?=$homepage ?>"<?=xmlLang($lang) ?>><?=$languages[$lang] ?></a></li>
	<?
		}
		elseif (array_key_exists($defaultlang, $languages))
		{
			?><li rel="foaf:homepage"><a class="url" rel="me" about="<?=$homepage ?>" property="dc:title" href="<?=$homepage ?>"<?=xmlLang($lang) ?>><?=$languages[$defaultlang] ?></a></li>
	<?
		}
		else	
		{
			?><li rel="foaf:homepage" href="<?=$homepage ?>"><a class="url" rel="me" href="<?=$homepage ?>"><?=$homepage ?></a></li>
	<?
		}
	
		
	}

?></ul></div>
<?
}

/*
 * Blogs
 */
$query = 'PREFIX foaf: <'.$foaf.'>
PREFIX dc: <'.$dc.'>
select ?blog, ?blogtitle
where {
<'.$personURI->getURI().'> foaf:weblog ?blog .
OPTIONAL { ?blog dc:title ?blogtitle }
}';
#echo "$query\n";
$blogs = $model->sparqlQuery($query);

foreach ($blogs as $blog)
{
	$blogstodisplay[$blog['?blog']->getURI()] = array();
}

foreach ($blogs as $blog)
{
	if ($blog['?blogtitle'])
	{
		$blogstodisplay[$blog['?blog']->getURI()][getLiteralLanguage($blog['?blogtitle'])] =
			$blog['?blogtitle']->getLabel();
	}
}

if (count($blogstodisplay))
{
	?><h2>Blogs</h2>
<div id="blogs"><ul><?

	foreach ($blogstodisplay as $blog => $languages)
	{
		if (array_key_exists($lang, $languages))
		{
			?><li rel="foaf:weblog"><a class="url" rel="me" about="<?=$blog ?>" property="dc:title" href="<?=$blog?>"<?=xmlLang($lang) ?>><?=$languages[$lang] ?></a></li>
	<?
		}
		elseif (array_key_exists($defaultlang, $languages))
		{
			?><li rel="foaf:weblog"><a class="url" rel="me" about="<?=$blog ?>" property="dc:title" href="<?=$blog ?>"<?=xmlLang($lang) ?>><?=$languages[$defaultlang] ?></a></li>
	<?
		}
		else	
		{
			?><li rel="foaf:weblog" href="<?=$blog ?>"><a class="url" rel="me" href="<?=$blog ?>"><?=$blog ?></a></li>
	<?
		}
	
		
	}

?></ul></div>
<?
}

/*
 * People person knows
 */
$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
select ?name, ?homepage, ?uri
where {
<'.$personURI->getURI().'> foaf:knows ?uri .
OPTIONAL { ?uri  foaf:homepage  ?homepage } .
OPTIONAL { ?uri  foaf:name ?name }
}';
#echo "$query\n";
$people = $model->sparqlQuery($query);
#echo var_export($people);

if ($people)
{
?>
<h2>People</h2>
<div id="people"><ul>
<?
	foreach ($people as $person)
        {
		if (is_a($person['?uri'], 'BlankNode') && !$person['?homepage'])
		{
			continue;
		}

		?><li rel="foaf:knows" resource="<?=$person['?uri']->getURI() ?>"><?
		if ($person['?homepage'])
		{
			?><span rel="foaf:homepage" resource="<?=$person['?homepage']->getURI() ?>"/><a rel="contact" href="<?=$person['?homepage']->getURI() ?>"><?
		}

		if ($person['?name'])
		{
			?><span property="foaf:name"<?=xmlLang($person['?name']->getLanguage()) ?> about="<?=$person['?uri']->getURI() ?>"><?=$person['?name']->getLabel() ?></span><?
		}
		else
		{
			?><span><?=$person['?uri']->getURI() ?></span><?
		}

		if ($person['?homepage'])
		{
			?></a><?
		}

		if (!is_a($person['?uri'], 'BlankNode'))
		{
			?> <a href="<?=$person['?uri']->getURI() ?>" title="FOAF"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a><?
		}
		?></li><?
	}
?></ul></div>
<?
}

$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
select ?lat, ?lng
where { <'.$personURI->getURI().'> foaf:based_near ?point .
?point  geo:lat  ?lat
?point  geo:long ?lng
}';
#echo "$query\n";
$locations = $model->sparqlQuery($query);
#echo var_export($locations);

if ($locations)
{
?><h2>Location</h2>
<div id="location">
<?
	$first=true;
	foreach ($locations as $location)
	{
		$markers[] = $location['?lat']->getLabel().','.$location['?lng']->getLabel();
		?>
		<span rel="foaf:based_near"><span typeof="geo:Point"<? if ($first) {?> class="geo"<? } ?>>
		<span property="geo:lat"<? if ($first) {?> class="latitude"<? } ?> style="display:none"><?=$location['?lat']->getLabel()?></span>
		<span property="geo:long"<? if ($first) {?> class="longitude"<? } ?> style="display:none"><?=$location['?lng']->getLabel()?></span>
		</span></span><?
		$first = false;
	}

?>
<div id="map" style="width: 600px; height: 400px"><img src="http://maps.google.com/staticmap?<?
	if (count($locations) < 2)
	{
		echo 'zoom=12&amp;';
	}
?>size=600x400&amp;markers=<?=implode('|', $markers); ?>%7C&amp;maptype=roadmap&amp;key=<?=$googleMapsKey?>" alt="locations map" width="600" height="400"/></div>
</div>
<?
}

?>
<div style="border-top: 1px solid silver; padding: 5px; align: center; margin-top: 20px">
<a href="http://validator.w3.org/check?uri=<?=urlencode($_SERVER['SCRIPT_URI'].'?'.$_SERVER['QUERY_STRING'])?>" title="Check XHTML + RDFa validity"><img src="http://www.w3.org/Icons/valid-xhtml-rdfa-blue" alt="Valid XHTML + RDFa" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=<?=urlencode($_SERVER['SCRIPT_URI'].'?'.$_SERVER['QUERY_STRING'])?>" title="Extract RDF from RDFa on this page"><img src="http://www.w3.org/Icons/SW/Buttons/sw-rdfa-orange.png" alt="Extract RDF from RDFa on this page" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://hcard.geekhood.net/?url=<?=urlencode($_SERVER['SCRIPT_URI'].'?'.$_SERVER['QUERY_STRING'])?>" title="Show hCard information on this page"><img src="hcard.png" alt="Show hCard information on this page" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://gmpg.org/xfn/" title="XFN Homepage"><img src="xfn-btn.gif" alt="XFN" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://microformats.org/wiki/geo" title="Geo Microformat Page"><img src="geo.png" alt="geo" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
</div>
<div style="border-top: 1px solid silver; padding: 5px; align: center; font-size: small; text-align: center">Created with <a href="http://code.google.com/p/my-semantic-profile/">My Semantic Profile</a> (v.<?=$version?> r<?=$build?>)</div>
</body>
</html>
