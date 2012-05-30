<?php

$dir = 'data/pubmed';
if (!file_exists($dir)) mkdir($dir, 0700, true);

$file_template = 'loprovpmc-%d.xml.gz';

$xml = simplexml_load_file('loprovpmc-search.xml');

$total = (int) $xml->Count;
print "$total items\n";

$params = array(
  'db' => 'pubmed',
  'retmode' => 'xml',
  'query_key' => (string) $xml->QueryKey,
  'WebEnv' => (string) $xml->WebEnv,
  'retmax' => 10000,
);

$curl_multi = curl_multi_init();

$starts = range(0, $total, 10000);

foreach (array_chunk($starts, 5) as $chunk){
  // store the curl and file resources so they can be closed when complete
  $connections = array();
  $files = array();

  // build the requests
  foreach ($chunk as $i) {
    $file = $dir . '/' . sprintf($file_template, $i);
    if (file_exists($file)) continue;

    $params['retstart'] = $i;

    $curl = curl_init();

    $output = gzopen($file, 'w');

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?' . http_build_query($params),
      CURLOPT_FILE => $output,
      CURLOPT_ENCODING => 'gzip,deflate',
      CURLOPT_VERBOSE => true,
    ));

    $connections[$i] = $curl;
    $files[$i] = $output;

    curl_multi_add_handle($curl_multi, $curl);
  }

  // execute the requests
  $active = null;
  do {
    $mrc = curl_multi_exec($curl_multi, $active);
  } while ($mrc == CURLM_CALL_MULTI_PERFORM);

  while ($active && $mrc == CURLM_OK) {
    if (curl_multi_select($curl_multi) != -1) {
      do {
          $mrc = curl_multi_exec($curl_multi, $active);
      } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }
  }

  foreach ($connections as $i => $curl) {
    curl_multi_remove_handle($curl_multi, $curl);
    curl_close($curl);
    gzclose($files[$i]);
  }
}

curl_multi_close($curl_multi);
