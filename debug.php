<?
/**
 * This file contains various debugging tools and variables
 */

#printStatement($statement);
function printStatement($statement)
{
	echo "<div><h3>Triple:</h3>";
	echo "<p>Subject: ".$statement->getLabelSubject()."</p>";
	echo "<p>Predicate: ".$statement->getLabelPredicate()."</p>";
	echo "<p>Object: ".$statement->getLabelObject()."</p>";
	echo "<div>";
}

