<?php

$input = 'pubmed.csv';

$dir = 'data/pmc';
if (!file_exists($dir)) mkdir($dir, 0700, true);

$curl = curl_init();

curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
curl_setopt($curl, CURLOPT_VERBOSE, true);
curl_setopt($curl, CURLOPT_NOPROGRESS, false);

$csv = fopen($input, 'r');

while (($row = fgetcsv($csv)) !== false) {
  list($pmid, $pmc) = $row;
  if (!$pmc || preg_match('/\W/', $pmc)) continue;

  $file = $dir . '/' . $pmc . '.xml.gz';
  if (file_exists($file)) continue;

  $identifier = preg_replace('/^PMC/', 'oai:pubmedcentral.nih.gov:', $pmc);

  $output = gzopen($file, 'w');
  curl_setopt($curl, CURLOPT_URL, 'http://www.pubmedcentral.nih.gov/oai/oai.cgi?verb=GetRecord&metadataPrefix=pmc_fm&identifier=' . $identifier);
  curl_setopt($curl, CURLOPT_FILE, $output);
  curl_exec($curl);
  gzclose($output);
}
