<?php // $Id$
class RDFXMLModule extends EditModule
{
	function getName()
	{
		return "RDF/XML";
	}

	function getSlug()
	{
		return "rdfxml";
	}

	function displayForm($model, $personURI, $language)
	{
		$rdf = $model->writeRdfToString();

		/*
		 * URI
		 */
		?><div id="<?php echo $this->getSlug()?>_rdfxml">
		<textarea name="<?php echo $this->getSlug()?>_triples" style="width: 100%" rows="25"><?php echo htmlspecialchars($rdf)?></textarea>
		</div>
<?php
	}

	function saveChanges(&$model, $personURI, $language)
	{
		global $profileDocumentType;

		$rdf = $_REQUEST[$this->getSlug().'_triples'];


		$newmodel = ModelFactory::getDefaultModel();
		$newmodel->addNamespace('foaf', $foaf);
		$newmodel->addNamespace('dc', $dc);
		$newmodel->addNamespace('rdf', $rdf);

		$newmodel->loadFromString($rdf, $profileDocumentType);

		$model->close();
		$model = $newmodel;

		return true;
	}
}

$modules[] = new RDFXMLModule();
