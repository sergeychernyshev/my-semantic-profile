<?

abstract class DisplayModule
{
	abstract function getSlug();

	abstract function getName();

	abstract function displayContent($model, $personURI, $language);
}

$display_modules = array();

include_once('basic/index.php');
include_once('people/index.php');
include_once('location/index.php');
