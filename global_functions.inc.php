<?
/**
 * We're using RAP library for RDF parsing
 */
define('RDFAPI_INCLUDE_DIR', $SPROOT.'./rdfapi-php/');
include_once(RDFAPI_INCLUDE_DIR . 'RdfAPI.php');

define('MAXFILESIZE', 5000000);

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
$rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

/*
 * A list of languages with their codes
 * TODO - to automatically add languages that are listed in profile already but not in our list
 * TODO - provide some autodetection functionality for languages passed by User-agent (browser)
 */
$languageParams = array(
	'en' => array('label' => 'English'),
	'ru' => array('label' => 'Russian'),
	'fr' => array('label' => 'French'),
	'hu' => array('label' => 'Hungarian'),
	'en-UK' => array('label' => 'English (UK)')
);

// language sequence
$languageSequence = array('en', 'en-UK', 'ru', 'fr', 'hu');

/**
 * Get the model object
 */
$model = null; # using this for singleton operation

function getModel()
{
	global $profileDocument, $profileDocumentType, $model;

	if ($model != null)
	{
		return $model;
	}

	$model = ModelFactory::getDefaultModel();

	if (filesize($profileDocument) > MAXFILESIZE)
	{
		throw new Exception("[ERROR] RDF file is too big: $profileDocument (".MAXFILESIZE.")");
	}

	if ($docF = fopen($profileDocument, 'r'))
	{
		$doc = fread($docF, MAXFILESIZE);
		fclose($docF);
	}
	else
	{
		throw new Exception("[ERROR] Can't open profile document: $profileDocument");
	}

	$model->loadFromString($doc, $profileDocumentType);

	return $model;
}

/**
 * Save model (supposedly updated one)
 */
function saveModel()
{
	global $profileDocument, $model;

	return $model->saveAs($profileDocument);
}

/**
 * Get primary topic (main person)
 */
function getPrimaryPerson($model)
{
	global $foaf;

	// dirty hack - RAP is adding # at the end of base URI
	$docuri = $model->getBaseURI();
	if (substr($docuri, -1) == '#')
	{
		$docuri = substr($docuri, 0, -1);
	}

	$it = $model->findAsIterator(new Resource($docuri), new Resource($foaf.'primaryTopic'), NULL);

	if (!$it->hasNext())
	{
		return null;
	}

	$statement = $it->next();

	if ($it->hasNext())
	{
		throw new Exception("[ERROR] More then one maker of foaf:PersonalProfileDocument defined");
	}

	return $statement->getObject();
}

/**
 * getLiteralLanguage
 * Function that wraps the language call and returns default language ('en') if it's not defined
 */
function getLiteralLanguage($literal)
{
	global $defaultlang;

	if (!$literal)
	{
		return $defaultlang;
	}

	$lang = $literal->getLanguage();

	return ($lang == '' ? $defaultlang : $lang);
}

/*
 * Function to return a hash of languages used within a model (xml:lang) as keys
 */
function getModelLanguages($model)
{
	$it = $model->getStatementIterator();

	$lang = array();

	while ($it->hasNext())
	{
		$obj = $it->next()->getObject();

		if (is_a($obj, 'Literal'))
		{
			$lang[$obj->getLanguage()] = true;
		}
	}

	return $lang;
}
