<?php
/**
 * Automated version generator
 */
$version = '0.4.3';

$build = "0b1d5c978871d96df5671db15fea40e74c196c3e";

/*
 * default value for this variable - can be overriden in config.inc.php
 */
$profileDocumentType = 'rdf';
$defaultlang = 'en'; // this is used when literals don't define language specifically

/**
 * We're using RAP library for RDF parsing
 */
define('RDFAPI_INCLUDE_DIR', $SPROOT.'./rdfapi-php/');
include_once(RDFAPI_INCLUDE_DIR . 'RdfAPI.php');

define('MAXFILESIZE', 5000000);

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
$rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
$rdfs = 'http://www.w3.org/2000/01/rdf-schema#';
$iana = 'http://www.iana.org/assignments/relation/';
$awol = 'http://bblfish.net/work/atom-owl/2006-06-06/#';
$geo = 'http://www.w3.org/2003/01/geo/wgs84_pos#';

/**
 * Get the model object
 */
$model = null; # using this for singleton operation

function getModel()
{
	global $profileDocument, $profileDocumentType, $model, $foaf, $dc, $rdf, $rdfs, $iana, $awol;

	if ($model != null)
	{
		return $model;
	}

	$model = ModelFactory::getDefaultModel('');
	$model->addNamespace('rdf', $rdf);
	$model->addNamespace('rdfs', $rdfs);
	$model->addNamespace('foaf', $foaf);
	$model->addNamespace('dc', $dc);
	$model->addNamespace('iana', $iana);
	$model->addNamespace('awol', $awol);

	if (filesize($profileDocument) > MAXFILESIZE)
	{
		throw new Exception("[ERROR] RDF file is too big: $profileDocument (".MAXFILESIZE.")");
	}

	if ($docF = fopen($profileDocument, 'r'))
	{
		$doc = fread($docF, MAXFILESIZE);
		fclose($docF);
	}
	else
	{
		throw new Exception("[ERROR] Can't open profile document: $profileDocument");
	}

	$model->loadFromString($doc, $profileDocumentType);

	return $model;
}

/**
 * Save model (supposedly updated one)
 */
function saveModel()
{
	global $profileDocument, $model;

	$model = dedupModel($model);

	return $model->saveAs($profileDocument);
}

/**
 * Update model with PersonalProfileDocument and other related triples based on configuration
 */
function updateProfileData()
{
	global $personURI, $baseURL, $model, $rdf, $foaf, $iana, $awol;

	$docURI = new Resource("");
	$model->addWithoutDuplicates(new Statement($docURI, new Resource($rdf.'type'), new Resource($foaf.'PersonalProfileDocument')));
	$model->addWithoutDuplicates(new Statement($docURI, new Resource($foaf.'maker'), $personURI));
	$model->addWithoutDuplicates(new Statement($docURI, new Resource($foaf.'primaryTopic'), $personURI));

	$baseURI = new Resource($baseURL);
	$model->addWithoutDuplicates(new Statement($docURI, new Resource($iana.'alternate'), $baseURI));
	$model->addWithoutDuplicates(new Statement($baseURI, new Resource($awol.'type'), new Literal('text/html')));

	return true;
}

/**
 * Makes sure that model contains no duplicates
 */
function dedupModel($model)
{
	global $foaf, $dc, $rdf, $rdfs, $iana, $awol;

	$emptymodel = new MemModel('');
	$emptymodel->addNamespace('rdf', $rdf);
	$emptymodel->addNamespace('rdfs', $rdfs);
	$emptymodel->addNamespace('foaf', $foaf);
	$emptymodel->addNamespace('dc', $dc);
	$emptymodel->addNamespace('iana', $iana);
	$emptymodel->addNamespace('awol', $awol);

	return $emptymodel->unite($model);
}

/**
 * Get primary topic (main person)
 */
function getPrimaryPerson($model)
{
	global $foaf;

	// dirty hack - RAP is adding # at the end of base URI
	$docuri = $model->getBaseURI();
	if (substr($docuri, -1) == '#')
	{
		$docuri = substr($docuri, 0, -1);
	}

	$it = $model->findAsIterator(new Resource($docuri), new Resource($foaf.'primaryTopic'), NULL);

	if (!$it->hasNext())
	{
		return null;
	}

	$statement = $it->next();

	if ($it->hasNext())
	{
		throw new Exception("[ERROR] More then one maker of foaf:PersonalProfileDocument defined");
	}

	return $statement->getObject();
}

/**
 * getLiteralLanguage
 * Function that wraps the language call and returns default language ('en') if it's not defined
 */
function getLiteralLanguage($literal)
{
	global $defaultlang;

	if (!$literal)
	{
		return $defaultlang;
	}

	$lang = $literal->getLanguage();

	return ($lang == '' ? $defaultlang : $lang);
}

/*
 * Function to return a hash of languages used within a model (xml:lang) as keys
 */
function getModelLanguages($model)
{
	$it = $model->getStatementIterator();

	$lang = array();

	while ($it->hasNext())
	{
		$obj = $it->next()->getObject();

		if (is_a($obj, 'Literal'))
		{
			$lang[$obj->getLanguage()] = true;
		}
	}

	return $lang;
}

/*
 * A list of languages with their codes (all ISO 639-1 two-letter codes)
 * TODO - provide some autodetection functionality for languages passed by User-agent (browser)
 */
$languageParams = array(
	'aa' => array('label' => 'Afar'),
	'ab' => array('label' => 'Abkhazian'),
	'af' => array('label' => 'Afrikaans'),
	'ak' => array('label' => 'Akan'),
	'sq' => array('label' => 'Albanian'),
	'am' => array('label' => 'Amharic'),
	'ar' => array('label' => 'Arabic'),
	'an' => array('label' => 'Aragonese'),
	'hy' => array('label' => 'Armenian'),
	'as' => array('label' => 'Assamese'),
	'av' => array('label' => 'Avaric'),
	'ae' => array('label' => 'Avestan'),
	'ay' => array('label' => 'Aymara'),
	'az' => array('label' => 'Azerbaijani'),
	'ba' => array('label' => 'Bashkir'),
	'bm' => array('label' => 'Bambara'),
	'eu' => array('label' => 'Basque'),
	'be' => array('label' => 'Belarusian'),
	'bn' => array('label' => 'Bengali'),
	'bh' => array('label' => 'Bihari'),
	'bi' => array('label' => 'Bislama'),
	'bs' => array('label' => 'Bosnian'),
	'br' => array('label' => 'Breton'),
	'bg' => array('label' => 'Bulgarian'),
	'my' => array('label' => 'Burmese'),
	'ca' => array('label' => 'Catalan'),
	'ch' => array('label' => 'Chamorro'),
	'ce' => array('label' => 'Chechen'),
	'zh' => array('label' => 'Chinese'),
	'cu' => array('label' => 'Church Slavic'),
	'cv' => array('label' => 'Chuvash'),
	'kw' => array('label' => 'Cornish'),
	'co' => array('label' => 'Corsican'),
	'cr' => array('label' => 'Cree'),
	'cs' => array('label' => 'Czech'),
	'da' => array('label' => 'Danish'),
	'dv' => array('label' => 'Divehi'),
	'nl' => array('label' => 'Dutch'),
	'dz' => array('label' => 'Dzongkha'),
	'en' => array('label' => 'English'),
	'eo' => array('label' => 'Esperanto'),
	'et' => array('label' => 'Estonian'),
	'ee' => array('label' => 'Ewe'),
	'fo' => array('label' => 'Faroese'),
	'fj' => array('label' => 'Fijian'),
	'fi' => array('label' => 'Finnish'),
	'fr' => array('label' => 'French'),
	'fy' => array('label' => 'Western Frisian'),
	'ff' => array('label' => 'Fulah'),
	'ka' => array('label' => 'Georgian'),
	'de' => array('label' => 'German'),
	'gd' => array('label' => 'Gaelic'),
	'ga' => array('label' => 'Irish'),
	'gl' => array('label' => 'Galician'),
	'gv' => array('label' => 'Manx'),
	'el' => array('label' => 'Greek'),
	'gn' => array('label' => 'Guarani'),
	'gu' => array('label' => 'Gujarati'),
	'ht' => array('label' => 'Haitian'),
	'ha' => array('label' => 'Hausa'),
	'he' => array('label' => 'Hebrew'),
	'hz' => array('label' => 'Herero'),
	'hi' => array('label' => 'Hindi'),
	'ho' => array('label' => 'Hiri Motu'),
	'hr' => array('label' => 'Croatian'),
	'hu' => array('label' => 'Hungarian'),
	'ig' => array('label' => 'Igbo'),
	'is' => array('label' => 'Icelandic'),
	'io' => array('label' => 'Ido'),
	'ii' => array('label' => 'Sichuan Yi'),
	'iu' => array('label' => 'Inuktitut'),
	'ie' => array('label' => 'Interlingue'),
	'ia' => array('label' => 'Interlingua'),
	'id' => array('label' => 'Indonesian'),
	'ik' => array('label' => 'Inupiaq'),
	'it' => array('label' => 'Italian'),
	'jv' => array('label' => 'Javanese'),
	'ja' => array('label' => 'Japanese'),
	'kl' => array('label' => 'Kalaallisut'),
	'kn' => array('label' => 'Kannada'),
	'ks' => array('label' => 'Kashmiri'),
	'kr' => array('label' => 'Kanuri'),
	'kk' => array('label' => 'Kazakh'),
	'km' => array('label' => 'Central Khmer'),
	'ki' => array('label' => 'Kikuyu'),
	'rw' => array('label' => 'Kinyarwanda'),
	'ky' => array('label' => 'Kirghiz'),
	'kv' => array('label' => 'Komi'),
	'kg' => array('label' => 'Kongo'),
	'ko' => array('label' => 'Korean'),
	'kj' => array('label' => 'Kuanyama'),
	'ku' => array('label' => 'Kurdish'),
	'lo' => array('label' => 'Lao'),
	'la' => array('label' => 'Latin'),
	'lv' => array('label' => 'Latvian'),
	'li' => array('label' => 'Limburgan'),
	'ln' => array('label' => 'Lingala'),
	'lt' => array('label' => 'Lithuanian'),
	'lb' => array('label' => 'Luxembourgish'),
	'lu' => array('label' => 'Luba-Katanga'),
	'lg' => array('label' => 'Ganda'),
	'mk' => array('label' => 'Macedonian'),
	'mh' => array('label' => 'Marshallese'),
	'ml' => array('label' => 'Malayalam'),
	'mi' => array('label' => 'Maori'),
	'mr' => array('label' => 'Marathi'),
	'ms' => array('label' => 'Malay'),
	'mg' => array('label' => 'Malagasy'),
	'mt' => array('label' => 'Maltese'),
	'mo' => array('label' => 'Moldavian'),
	'mn' => array('label' => 'Mongolian'),
	'na' => array('label' => 'Nauru'),
	'nv' => array('label' => 'Navajo'),
	'nr' => array('label' => 'Ndebele, South'),
	'nd' => array('label' => 'Ndebele, North'),
	'ng' => array('label' => 'Ndonga'),
	'ne' => array('label' => 'Nepali'),
	'nn' => array('label' => 'Norwegian Nynorsk'),
	'nb' => array('label' => 'Bokmål, Norwegian'),
	'no' => array('label' => 'Norwegian'),
	'ny' => array('label' => 'Chichewa'),
	'oc' => array('label' => 'Occitan (post 1500)'),
	'oj' => array('label' => 'Ojibwa'),
	'or' => array('label' => 'Oriya'),
	'om' => array('label' => 'Oromo'),
	'os' => array('label' => 'Ossetian'),
	'pa' => array('label' => 'Panjabi'),
	'fa' => array('label' => 'Persian'),
	'pi' => array('label' => 'Pali'),
	'pl' => array('label' => 'Polish'),
	'pt' => array('label' => 'Portuguese'),
	'ps' => array('label' => 'Pushto'),
	'qu' => array('label' => 'Quechua'),
	'rm' => array('label' => 'Romansh'),
	'ro' => array('label' => 'Romanian'),
	'rn' => array('label' => 'Rundi'),
	'ru' => array('label' => 'Russian'),
	'sg' => array('label' => 'Sango'),
	'sa' => array('label' => 'Sanskrit'),
	'si' => array('label' => 'Sinhala'),
	'sk' => array('label' => 'Slovak'),
	'sl' => array('label' => 'Slovenian'),
	'se' => array('label' => 'Northern Sami'),
	'sm' => array('label' => 'Samoan'),
	'sn' => array('label' => 'Shona'),
	'sd' => array('label' => 'Sindhi'),
	'so' => array('label' => 'Somali'),
	'st' => array('label' => 'Sotho, Southern'),
	'es' => array('label' => 'Spanish'),
	'sc' => array('label' => 'Sardinian'),
	'sr' => array('label' => 'Serbian'),
	'ss' => array('label' => 'Swati'),
	'su' => array('label' => 'Sundanese'),
	'sw' => array('label' => 'Swahili'),
	'sv' => array('label' => 'Swedish'),
	'ty' => array('label' => 'Tahitian'),
	'ta' => array('label' => 'Tamil'),
	'tt' => array('label' => 'Tatar'),
	'te' => array('label' => 'Telugu'),
	'tg' => array('label' => 'Tajik'),
	'tl' => array('label' => 'Tagalog'),
	'th' => array('label' => 'Thai'),
	'bo' => array('label' => 'Tibetan'),
	'ti' => array('label' => 'Tigrinya'),
	'to' => array('label' => 'Tonga (Tonga Islands)'),
	'tn' => array('label' => 'Tswana'),
	'ts' => array('label' => 'Tsonga'),
	'tk' => array('label' => 'Turkmen'),
	'tr' => array('label' => 'Turkish'),
	'tw' => array('label' => 'Twi'),
	'ug' => array('label' => 'Uighur'),
	'uk' => array('label' => 'Ukrainian'),
	'ur' => array('label' => 'Urdu'),
	'uz' => array('label' => 'Uzbek'),
	've' => array('label' => 'Venda'),
	'vi' => array('label' => 'Vietnamese'),
	'vo' => array('label' => 'Volapük'),
	'cy' => array('label' => 'Welsh'),
	'wa' => array('label' => 'Walloon'),
	'wo' => array('label' => 'Wolof'),
	'xh' => array('label' => 'Xhosa'),
	'yi' => array('label' => 'Yiddish'),
	'yo' => array('label' => 'Yoruba'),
	'za' => array('label' => 'Zhuang'),
	'zu' => array('label' => 'Zulu'),
);

// language sequence
$languageSequence = array('ab', 'aa', 'af', 'ak', 'sq', 'am', 'ar', 'an', 'hy', 'as', 'av', 'ae', 'ay', 'az', 'bm', 'ba', 'eu', 'be', 'bn', 'bh', 'bi', 'nb', 'bs', 'br', 'bg', 'my', 'ca', 'km', 'ch', 'ce', 'ny', 'zh', 'cu', 'cv', 'kw', 'co', 'cr', 'hr', 'cs', 'da', 'dv', 'nl', 'dz', 'en', 'eo', 'et', 'ee', 'fo', 'fj', 'fi', 'fr', 'ff', 'gd', 'gl', 'lg', 'ka', 'de', 'el', 'gn', 'gu', 'ht', 'ha', 'he', 'hz', 'hi', 'ho', 'hu', 'is', 'io', 'ig', 'id', 'ia', 'ie', 'iu', 'ik', 'ga', 'it', 'ja', 'jv', 'kl', 'kn', 'kr', 'ks', 'kk', 'ki', 'rw', 'ky', 'kv', 'kg', 'ko', 'kj', 'ku', 'lo', 'la', 'lv', 'li', 'ln', 'lt', 'lu', 'lb', 'mk', 'mg', 'ms', 'ml', 'mt', 'gv', 'mi', 'mr', 'mh', 'mo', 'mn', 'na', 'nv', 'nd', 'nr', 'ng', 'ne', 'se', 'no', 'nn', 'oc', 'oj', 'or', 'om', 'os', 'pi', 'pa', 'fa', 'pl', 'pt', 'ps', 'qu', 'ro', 'rm', 'rn', 'ru', 'sm', 'sg', 'sa', 'sc', 'sr', 'sn', 'ii', 'sd', 'si', 'sk', 'sl', 'so', 'st', 'es', 'su', 'sw', 'ss', 'sv', 'tl', 'ty', 'tg', 'ta', 'tt', 'te', 'th', 'bo', 'ti', 'to', 'ts', 'tn', 'tr', 'tk', 'tw', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'cy', 'fy', 'wo', 'xh', 'yi', 'yo', 'za', 'zu');
