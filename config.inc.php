<?
/**
 * Someconfiguration parameters
 */

// change this to the full URL of your installation
$baseURL = 'http://www.yoursite.com/profile/';

$defaultlang = 'en'; // this is used when literals don't define language specifically

/**
 * location of Personal Profile Document
 */
$profileDocument = $SPROOT.'/foaf.rdf';
$profileDocumentURI = $baseURL.'foaf.rdf';

/**
 * Uncomment for test cases
 */
$profileDocument = $SPROOT.'/tests/timbl.rdf';
$profileDocumentURI = $baseURL.'tests/timbl.rdf';

$googleMapsKey = '-- insert Google Maps API key here --';
