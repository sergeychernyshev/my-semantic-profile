<?
/**
 * This program helps manage person's user profile using Semantic Web standards such as FOAF
 */

/**
 * We're using RAP library for RDF parsing
 */
define('RDFAPI_INCLUDE_DIR', './rdfapi-php/');
include(RDFAPI_INCLUDE_DIR . 'RdfAPI.php');

/*
 * Some namespace shortcuts
 */
$foaf = 'http://xmlns.com/foaf/0.1/';

/**
 * location of Personal Profile Document
 */
$profileDocument = 'foaf.rdf';

/**
 * $model defines 
 */
$model = ModelFactory::getDefaultModel();
$model->load($profileDocument);

/**
 * Let's get primary topic
 */
$it = $model->findAsIterator(NULL, new Resource($foaf.'primaryTopic'), NULL);

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
 * Let's get person's name
 */
$names = array();

$it = $model->findAsIterator($personURI, new Resource($foaf.'name'), NULL);
while ($it->hasNext()) {
	$statement = $it->next();
	$names[] = $statement->getLabelObject();
}

if (count($names) > 0)
{
	?><h1><?=implode(' AKA ', $names)?></h1><?
}
else
{
	?><h1>Unnamed</h1><?
}
