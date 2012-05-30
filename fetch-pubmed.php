<?php

$file_template = 'loprovpmc-%d.xml.gz';

$dir = 'data/pubmed';
if (!file_exists($dir)) mkdir($dir, 0700, true);

$xml = simplexml_load_file('loprovpmc-search.xml');

$total = (int) $xml->Count;
print "$total items\n";

for ($i = 0; $i < $total; $i += 10000) {
  $file = $dir . '/' . sprintf($file_template, $i);
  if (file_exists($file)) continue;

  $params = array(
    'db' => 'pubmed',
    'retmode' => 'xml',
    'query_key' => (string) $xml->QueryKey,
    'WebEnv' => (string) $xml->WebEnv,
    'retstart' => $i,
    'retmax' => 10000,
    );

  $url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?' . http_build_query($params);

  $output = gzopen($file, 'w');
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_FILE, $output);
  curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
  curl_setopt($curl, CURLOPT_VERBOSE, true);
  curl_setopt($curl, CURLOPT_NOPROGRESS, false);
  curl_exec($curl);
  gzclose($output);
}
