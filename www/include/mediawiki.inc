<?php
$mw =  pg_pconnect("dbname=wikidb user=wikiuser");
$mediawiki = 1; // set to 0 to disable

function checkPage($page) {
    global $mw,$mediawiki;
    $sql = "SELECT count(*) FROM page WHERE page_title='$page'";
    $res = pg_query($mw, $sql);
    $cnt = pg_result($res,0,0);
  //  echo "$page $cnt <br>";
  if ( ! $mediawiki) return -1;
    return $cnt;
}
?>