<?
error_reporting(E_ALL);

/**
 * Someconfiguration parameters
 */
$baseURL = 'http://www.sergeychernyshev.com/profile/';

/**
 * location of Personal Profile Document
 */
$profileDocument = $SPROOT.'../sergey.rdf';
$profileDocumentURI = '/sergey.rdf';

/**
 * Uncomment for test cases
 */
if (strpos($_SERVER['REQUEST_URI'], '/timbl/') !== false )
{
	$profileDocument = $SPROOT.'tests/timbl.rdf';
	$profileDocumentURI = $baseURL.'tests/timbl.rdf';
}
if (strpos($_SERVER['REQUEST_URI'], '/cygri/') !== false )
{
	$profileDocument = $SPROOT.'tests/cygri.rdf';
	$profileDocumentURI = $baseURL.'tests/cygri.rdf';
}
if (strpos($_SERVER['REQUEST_URI'], '/tomheath/') !== false )
{
	$profileDocument = $SPROOT.'tests/tomheath.rdf';
	$profileDocumentURI = $baseURL.'tests/tomheath.rdf';
}
if (strpos($_SERVER['REQUEST_URI'], '/marco/') !== false )
{
	$profileDocument = $SPROOT.'tests/marco.rdf';
	$profileDocumentURI = $baseURL.'tests/marco.rdf';
}
if (strpos($_SERVER['REQUEST_URI'], '/danbri/') !== false )
{
	$profileDocument = $SPROOT.'tests/danbri.rdf';
	$profileDocumentURI = $baseURL.'tests/danbri.rdf';
}

$googleMapsKey = 'ABQIAAAAq_i4aTseMGLic8bgu1NQHRSEs_qikIHa8VCb2-5R0mAlXQZKPRRLkUrkPAVXUBMadjFafv4_Xrmr0g';
