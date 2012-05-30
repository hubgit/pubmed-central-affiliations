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
  'retstart' => $i,
  'retmax' => 10000,
);

$curl_multi = curl_multi_init();

$starts = range(0, $total, 10000);

foreach (array_chunk($starts, 5) as $chunk){
  // store the file resources so they can be closed when complete
  $files = array();

  // build the requests
  foreach ($chunk as $i) {
    $file = $dir . '/' . sprintf($file_template, $i);
    //if (file_exists($file)) continue;

    $params['retstart'] = $i;

    $curl = curl_init();

    $files[$i] = gzopen($file, 'w');

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?' . http_build_query($params),
      CURLOPT_FILE => $files[$i],
      CURLOPT_ENCODING => 'gzip,deflate',
      CURLOPT_VERBOSE => true,
    ));

    curl_multi_add_handle($curl_multi, $curl);
  }

  // execute the requests
  $active = false;
  do {
      curl_multi_exec($curl_multi, $active);
      usleep(100000);
  } while ($active);

  foreach ($connections as $i => $connection) {
    curl_close($connection);
    gzclose($files[$i]);
  }
}

curl_multi_close($curl_multi);
