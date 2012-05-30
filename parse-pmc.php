<?php

$input = 'pubmed.csv';
$output = 'pmc-affiliations.txt';

$dir = 'data/pmc';

$dom = new DOMDocument;

$csv = fopen($input, 'r');
$output_file = fopen($output, 'w');

$seen = array();

while (($row = fgetcsv($csv)) !== false) {
  list($pmid, $pmc) = $row;

  $file = $dir . '/' . $pmc . '.xml.gz';
  if (!file_exists($file) || !filesize($file)) continue;

  $xml = file_get_contents('compress.zlib://' . $file);
  if (!$xml) continue;

  $dom->loadXML($xml);
  $xpath = new DOMXPath($dom);
  $xpath->registerNamespace('nlm', 'http://dtd.nlm.nih.gov/2.0/xsd/archivearticle');

  $nodes = $xpath->query('//nlm:author-notes/nlm:corresp/nlm:email');
  if (!$nodes->length) continue;

  $email = $nodes->item(0)->textContent;

  if ($email && !isset($seen[$email])) {
    fwrite($output_file, $email . "\n");
    $seen[$email] = true;
  }
}
