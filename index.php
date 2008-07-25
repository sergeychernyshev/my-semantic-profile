<?
/**
 * This program helps manage person's user profile using Semantic Web standards such as FOAF
 */

/**
 * Someconfiguration parameters
 */
#$baseURL = $_SERVER["SCRIPT_NAME"]; // change this if you rewritten a URL to something else
$baseURL = '/profile/';

$defaultLanguage = 'en'; // this is used when literals don't define language specifically

/**
 * Automated version generator
 * $Id$
 */
$version = '0.2';
$build = '$Rev$';

/**
 * We're using RAP library for RDF parsing
 */
define('RDFAPI_INCLUDE_DIR', './rdfapi-php/');
include(RDFAPI_INCLUDE_DIR . 'RdfAPI.php');

#printStatement($statement);
function printStatement($statement)
{
	echo "<div><h3>Triple:</h3>";
	echo "<p>Subject: ".$statement->getLabelSubject()."</p>";
	echo "<p>Predicate: ".$statement->getLabelPredicate()."</p>";
	echo "<p>Object: ".$statement->getLabelObject()."</p>";
	echo "<div>";
}

# helper function to insert language tab
function xmlLang($lang)
{
	if ($lang)
	{
		return ' xml:lang="'.$lang.'"';
	}
	else
	{
		return '';
	}
}

/*
 * Some namespace shortcuts
 */
$foaf = 'http://xmlns.com/foaf/0.1/';
$dc = 'http://purl.org/dc/elements/1.1/';

/**
 * location of Personal Profile Document
 */
$profileDocument = '/home/sergey/www/sites/sergeychernyshev.com/sergey.rdf';
$profileDocumentURI = '/sergey.rdf';

#phpinfo(); exit;

/**
 * $model defines 
 */
$model = ModelFactory::getDefaultModel();
$model->load($profileDocument);

/**
 * Let's get primary topic
 */
$it = $model->findAsIterator(new Resource($profileDocument), new Resource($foaf.'primaryTopic'), NULL);

$numberOfResults = 0;

while ($it->hasNext()) {
	$numberOfResults += 1;

	$statement = $it->next();

	$personURI = $statement->getObject();

}

if ($numberOfResults === 0)
{
	echo "[ERROR] No maker of foaf:PersonalProfileDocument defined";
	return;
}
elseif ($numberOfResults > 1)
{
	echo "[ERROR] More then one maker of foaf:PersonalProfileDocument defined";
	return;
}

/**
 * If we were called with the URI of the object (probable URL-rewritten in .htaccess) then do appropriate 303 redirect
 */
if ($_SERVER["SCRIPT_URI"] == $personURI->getLabel())
{
	$destinations = array(
			'application/rdf+xml' => $profileDocumentURI,
	//		'text/rdf+n3' => '/sergey.n3',
	//		'application/turtle' => '/sergey.n3',
	//		'application/rdf+n3' => '/sergey.n3'
		);

	// http://ptlis.net/source/php-content-negotiation/#v1.0.2
	include 'content_negotiation.inc.php';
	$mimes = content_negotiation::mime_all_negotiation();

	foreach ($mimes['type'] as $mime)
	{
		if (isset($destinations[$mime]))
		{
			$destination = $destinations[$mime];
			break;
		}
	}

	if (!isset($destination))
	{
		$destination = $baseURL;
	}

	header('Vary: Accept');
	header("Location: $destination", true, 303);

	exit;
}

/**
 * Let's get person's name
 */
$names = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$names[] = $statement->getObject();
}

$title = 'Profile';

$namesText = '';
$personName = '';

if (count($names) > 0)
{
	$personName = $names[0]->getLabel();
	$title = $personName."'s profile";

	$first = true;

	foreach ($names as $name)
	{
		// apparently hCard allows only one fn (formatted name)
		$namesText .= ($first ? '' : ' AKA ').'<span'.($first ? ' class="fn"' : '').' property="foaf:name"'.xmlLang($name->getLanguage()).'>'.$name->getLabel().'</span>';
		$first = false;
	}
}
else
{
	$namesText = 'Unnamed';
}

header('Vary: Accept');
if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) 
    header("Content-Type: application/xhtml+xml; charset=utf-8");
else
    header("Content-Type: text/html; charset=utf-8");

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:foaf="http://xmlns.com/foaf/0.1/" xmlns:dc="http://purl.org/dc/elements/1.1/">
<head profile='http://www.w3.org/2006/03/hcard'>
	<title><?=$title ?></title>
	<link rel="meta" type="application/rdf+xml" title="FOAF" href="<?=$profileDocumentURI ?>" />
	<link rel="alternate" type="application/rdf+xml" title="<?=$title ?> (RDF)" href="<?=$profileDocumentURI ?>" />
	<link type="text/css" rel="stylesheet" href="floatbox/floatbox.css" />
	<link type="text/css" rel="stylesheet" href="profile.css" />
</head>
<body class="vcard" about="<?=$personURI->getLabel()?>">
<h1><?=$namesText?> <a href="<?=$profileDocumentURI ?>" title="My FOAF document"><img src="foaf.png" alt="FOAF" style="border: 0px"/></a></h1>
<?

/**
 * Let's get person's primary pictures (img) and show it's thumbnail if it exists or just resized it to 100 hight
 */
$images = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'img'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$images[] = $statement->getObject();
}

if (count($images) > 0)
{
	?><h2>Images</h2>
<div id="images">
<?

	foreach ($images as $imageResource)
	{
		$it = $model->findAsIterator($imageResource, new Resource($foaf.'thumbnail'), NULL);
		if ($it->hasNext()) {
			$statement = $it->next();
			?><a rel="foaf:img" class="photo" href="<?=$imageResource->getURI() ?>"><img src="<?=$statement->getObject()->getURI() ?>" class="thumbnail" alt="<?=($personName ? "$personName's photo" : 'photo')?>" rev="foaf:thumbnail" resource="<?=$imageResource->getURI() ?>"/></a>
<?
		}
		else
		{
			?><a rel="foaf:img" class="photo" href="<?=$imageResource->getURI() ?>"><img src="<?=$imageResource->getURI() ?>" class="thumbnail" alt="<?=($personName ? "$personName's photo" : 'photo')?>"/></a>
<?
		}
	}
?></div><?
}

/**
 * Now let's prng person's links
 */
$homepages = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'homepage'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$homepageResource = $statement->getObject();

	$homepages[] = $statement->getObject();
}

$blogs = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'weblog'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$blogResource = $statement->getObject();

	$blogs[] = $statement->getObject();
}

if (count($homepages) > 0 || count($blogs))
{
	?><h2>Sites</h2>
<div id="sites"><ul><?

	# homepages
	foreach ($homepages as $homepageResource)
	{
		$it = $model->findAsIterator($homepageResource, new Resource($dc.'title'), NULL);
		if ($it->hasNext()) {
			$statement = $it->next();
			$homepagetitle = $statement->getObject();
			?><li rel="foaf:homepage"><a class="url" rel="me" about="<?=$homepageResource->getURI() ?>" property="dc:title" href="<?=$homepageResource->getURI() ?>"<?=xmlLang($homepagetitle->getLanguage()) ?>><?=$homepagetitle->getLabel() ?></a></li>
	<?
		}
		else
		{
			?><li rel="foaf:homepage"><a class="url" rel="me" href="<?=$homepageResource->getURI() ?>"><?=$homepageResource->getURI() ?></a></li>
	<?
		}
	}

?></ul></div>

<h2>Blogs</h2>
<div id="blogs"><ul><?

	# blogs
	foreach ($blogs as $blogResource)
	{
		$it = $model->findAsIterator($blogResource, new Resource($dc.'title'), NULL);
		if ($it->hasNext()) {
			$statement = $it->next();
			$blogtitle = $statement->getObject();
			?><li rel="foaf:blog"><a class="url" href="<?=$blogResource->getURI() ?>" about="<?=$homepageResource->getURI() ?>" property="dc:title"<?=xmlLang($blogtitle->getLanguage()) ?>><?=$blogtitle->getLabel() ?></a></li>
	<?
		}
		else
		{
			?><li rel="foaf:blog"><a class="url" href="<?=$blogResource->getURI() ?>"><?=$blogResource->getURI() ?></a></li>
	<?
		}
	}

?></ul></div>
<?
}
?>
<div style="border-top: 1px solid silver; padding: 5px; align: center">
<a href="http://validator.w3.org/check?uri=<?=urlencode($_SERVER["SCRIPT_URI"])?>"><img src="http://www.w3.org/Icons/valid-xhtml-rdfa-blue" alt="Valid XHTML + RDFa" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://www.w3.org/2007/08/pyRdfa/extract?uri=<?=urlencode($_SERVER["SCRIPT_URI"])?>"><img src="http://www.w3.org/Icons/SW/Buttons/sw-rdfa-orange.png" alt="Show RDFa on this page"  style="margin: 0px 5px 0px 5px; border: 0px"/></a>
<a href="http://hcard.geekhood.net/?url=<?=urlencode($_SERVER["SCRIPT_URI"])?>"><img src="hcard.png" alt="Show hCard on this page" style="margin: 0px 5px 0px 5px; border: 0px"/></a>
</div>
<div style="border-top: 1px solid silver; padding: 5px; align: center; font-size: small; text-align: center">Created with MySemanticProfile v.<?=$version?> (build <?=$build?>)</div>
</body>
</html>
