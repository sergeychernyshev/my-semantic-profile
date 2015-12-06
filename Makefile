rdf-api:
	svn checkout https://svn.code.sf.net/p/rdfapi-php/code/trunk/rdfapi-php/api/ rdfapi-php
	patch -p0 <patches/RAP-optional.patch
