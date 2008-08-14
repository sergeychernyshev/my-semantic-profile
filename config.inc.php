<?
/**
 * Someconfiguration parameters
 */
#$baseURL = $_SERVER["SCRIPT_NAME"]; // change this if you rewritten a URL to something else
$baseURL = '/profile/';

$defaultLanguage = 'en'; // this is used when literals don't define language specifically

/**
 * location of Personal Profile Document
 */
$profileDocument = $SPROOT.'../sergey.rdf';
$profileDocumentURI = '/sergey.rdf';
$profileDocumentType = 'rdf';

/**
 * Uncomment for test cases
 */
#$profileDocument = 'tests/timbl.rdf';
#$profileDocumentURI = '/profile/tests/timbl.rdf';

$googleMapsKey = 'ABQIAAAAq_i4aTseMGLic8bgu1NQHRSEs_qikIHa8VCb2-5R0mAlXQZKPRRLkUrkPAVXUBMadjFafv4_Xrmr0g';

/*
 * Default language
 */
$defaultlang = 'en';
