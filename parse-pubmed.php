<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$input = 'data/pubmed/loprovpmc-*.xml.gz';
$output = 'pubmed.csv';

$csv = fopen($output, 'w');

$reader = new XMLReader();

foreach (glob($input) as $file) {
	print "$file\n";
	$reader->open('compress.zlib://' . $file);

	$item = array();

	while ($reader->read()) {
		if ($reader->nodeType == XMLREADER::ELEMENT) {
			switch ($reader->localName) {
				case 'PubmedArticle':
					if ($item) {
						store($csv, $item);
						$item = array();
					}
				break;

				case 'ArticleId':
					$item[$reader->getAttribute('IdType')] = $reader->readString();
				break;

				case 'Affiliation':
					$item['affiliation'] = $reader->readString();
				break;
			}
		}
	}

	store($csv, $item);
	$reader->close($file);
}

function store($csv, $item) {
	fputcsv($csv, array($item['pubmed'], $item['pmc'], $item['doi'], $item['affiliation']));
}
