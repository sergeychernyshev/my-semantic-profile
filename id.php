<?php
/**
 * This script uses 303 redirects to resolve resource IDs
 */
$SPROOT = './';

/**
 * Configuration parameters
 */
include_once('config.inc.php');

/**
 * If we were called with the URI of the object (probable URL-rewritten in .htaccess) then do appropriate 303 redirect
 */
$destinations = array(
		'application/rdf+xml' => $profileDocumentURI,
//		'text/rdf+n3' => '/sergey.n3',
//		'application/turtle' => '/sergey.n3',
//		'application/rdf+n3' => '/sergey.n3'
	);

// http://ptlis.net/source/php-content-negotiation/#v1.0.2
include_once('content_negotiation.inc.php');
$mimes = content_negotiation::mime_all_negotiation();

foreach ($mimes['type'] as $mime)
{
	if (isset($destinations[$mime]))
	{
		$destination = $destinations[$mime];
		break;
	}
}

if (!isset($destination))
{
	$destination = $baseURL;
}

header('Vary: Accept');
header("Location: $destination", true, 303);
