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

/*
 * A list of languages with their codes
 * TODO - to automatically add languages that are listed in profile already but not in our list
 * TODO - provide some autodetection functionality for languages passed by User-agent (browser)
 */
$languages = array(
	array('code' => 'en', 'label' => 'English'),
	array('code' => 'ru', 'label' => 'Russian'),
	array('code' => 'fr', 'label' => 'French')
);

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

	$docF = fopen($profileDocument, 'r');
	$doc = fread($docF, MAXFILESIZE);
	if (!feof($docF))
	{
		fclose($docF);
		throw new Exception("[ERROR] RDF file is too big");
	}
	fclose($docF);

	$model->loadFromString($doc, $profileDocumentType);

	return $model;
}

/**
 * Save model (supposedly updated one)
 */
function saveModel($model)
{
	global $profileDocument;

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
		throw new Exception("[ERROR] No maker of foaf:PersonalProfileDocument defined: ".$docuri);
	}

	$statement = $it->next();

	if ($it->hasNext())
	{
		throw new Exception("[ERROR] More then one maker of foaf:PersonalProfileDocument defined");
	}

	return $statement->getObject();
}
