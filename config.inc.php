<?
error_reporting(E_ALL);

/**
 * Someconfiguration parameters
 */
$baseURL = 'http://www.sergeychernyshev.com/profile/';

/**
 * Authentication for admin interface
 */
$authMethods = array(
	'basic' => array(
		'username' => 'sergey',	// !!! change this to user name
		'password' => 'abc123'	// !!! change this to password
	),
);

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

/**
 * XML-RPC server URLs to ping
 * Assuming Pingback protocol (http://hixie.ch/specs/pingback/pingback)
 *
 * By default we ping "Ping The Semantic Web" and "Sindice", uncomment next line to override
 * (empty array will disable pinging).
 */
//$pingers = array( 'http://rpc.pingthesemanticweb.com/', 'http://sindice.com/xmlrpc/api');

