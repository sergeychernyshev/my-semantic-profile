<?
/**
 * We're using RAP library for RDF parsing
 */
define('RDFAPI_INCLUDE_DIR', $SPROOT.'./rdfapi-php/');
include_once(RDFAPI_INCLUDE_DIR . 'RdfAPI.php');

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
 * Get the model object
 */
function getModel()
{
	global $profileDocument;

	$model = ModelFactory::getDefaultModel();
	$model->load($profileDocument);

	return $model;
}

/**
 * Get primary topic (main person)
 */
function getPrimaryPerson($model)
{
	global $foaf, $profileDocument;

	$it = $model->findAsIterator(new Resource($profileDocument), new Resource($foaf.'primaryTopic'), NULL);

	if (!$it->hasNext())
	{
		throw "[ERROR] No maker of foaf:PersonalProfileDocument defined";
	}

	$statement = $it->next();

	if ($it->hasNext())
	{
		throw "[ERROR] More then one maker of foaf:PersonalProfileDocument defined";
	}

	return $statement->getObject();
}
