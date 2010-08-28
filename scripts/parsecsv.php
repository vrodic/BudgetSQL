<?
require("include/dbcon.inc");
/*
 Preparing data:
 1. Download xls
 2. convert to txt in gnumeric, delimiter is |, quotations disables
 3. remove first couple of empty lines
 4. remove newlines on the first header line in text Proračun za 2010, Projekcija proračuna...
*/

pg_query("DELETE FROM MainItems;");
pg_query("BEGIN TRANSACTION;");
$fd = fopen ("data/budget.txt", "r");
 $string1 = fgets($fd, 65536);
 $header = explode("|",$string1);

    for ($i = 0; $i < sizeof($header) ; $i++) {
       //echo $header[$i] ." $i\t";
       $hashheader[trim($header[$i])] = $i;
    }
    echo "\n";
    $string1 = fgets($fd, 65536); // looks like additional header data. ignore for now.
    $root = 0;
   while (!feof ($fd)) {
     $string1 = str_replace("\"","",fgets($fd, 65536));
      $els = explode("|",$string1);
      $sifra = $els[$hashheader["Šifra"]];
      
   
      $naziv = $els[$hashheader["Naziv"]];
      $amount1 =  valtosql($els[$hashheader["Proračun za 2010."]]);
      $amount2 =  valtosql($els[$hashheader["Projekcija proračuna za 2011."]]);
      $amount3 =  valtosql($els[$hashheader["Projekcija proračuna za 2012."]]);


      $magic = substr($sifra,0,1);
      if ($magic == "A") {
        $root = 0;
      } 
      $type = "NULL";
      if (strpos($naziv,"INFORMATIZACIJA") !== false) {
        $type = "'1'"; 
      }
      $sql = "INSERT INTO MainItems (code,name,parent,amount1,amount2,amount3,typecode) VALUES ('$sifra','$naziv','$root',$amount1,$amount2,$amount3,$type);";
     echo $sql."\n";
      pg_query($sql);
          $sql = "SELECT id from MainItems ORDER by id desc limit 1";
    $res = pg_query($sql);
    $lastid = pg_result($res,0,0);
      if ($magic == "A" || $magic == "K") {
        $root = $lastid;
      }
      
   }
   pg_query("COMMIT;");
?>