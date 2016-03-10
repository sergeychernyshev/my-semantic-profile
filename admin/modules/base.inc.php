<?php

abstract class EditModule
{
	abstract function getSlug();

	abstract function getName();

	function displayForm($model, $personURI, $language)
	{
		echo "This module is under construction";
	}

	function saveChanges($model, $personURI, $language)
	{
		return true;
	}
}

$modules = array();

include_once('basic/index.php');
#include_once('pictures/index.php');
include_once('people/index.php');
include_once('location/index.php');
include_once('accounts/index.php');
include_once('interests/index.php');
include_once('triples/index.php');
