<?php
$query = 'loprovpmc[filter]'; // for example

$params = array(
  'db' => 'pubmed',
  'retmode' => 'xml',
  'retmax' => 1,
  'usehistory' => 'y',
  'term' => $query,
  );

$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?' . http_build_query($params);
file_put_contents('loprovpmc-search.xml', file_get_contents($url));
