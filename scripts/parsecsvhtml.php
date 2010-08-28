<?
require("include/dbcon.inc");
/*
 Preparing data:
 1. Download xls
 2. Delete extra empty sheets
 3. convert to html (3.2) in gnumeric
*/

pg_query("DELETE FROM MainItems;");
pg_query("BEGIN TRANSACTION;");



echo "Opening buget.html " ;   
$html = file_get_contents("data/budget.html");
echo "OK\n";
    $dom = new domDocument; 

    /*** load the html into the object ***/ 
    $dom->loadHTML($html); 

    /*** discard white space ***/ 
    $dom->preserveWhiteSpace = false; 

   /*** the table by its tag name ***/ 
    $tables = $dom->getElementsByTagName('table'); 

    /*** get all rows from the table ***/ 
    $rows = $tables->item(0)->getElementsByTagName('tr'); 

    /*** loop over the table rows ***/
    $setroot = 0;
    $begin = 0;
    $root = 0;
    $type = "NULL";
    foreach ($rows as $row) 
    { 
        /*** get each column by tag name ***/
        
        $cols = $row->getElementsByTagName('td'); 
        /*** echo the values ***/
           $name = ""; $sifra = ""; $amount1 = ""; $amount2 = ""; $amount2 = "";
        for ($i = 0; $i < $cols->length; $i++) {
         
            $val = $cols->item($i)->nodeValue;
            switch ($i) {
                case 0:
                    $sifra = $val;
                    if (! $begin) {
                        if ($sifra == "010") {
                            $begin = 1;
                        } else {
                            break;
                        }
                        
                    }
                    $magic = substr($sifra,0,1);

                    if ($cols->item($i)->childNodes->item(0)->nodeName == "b") {
                        //if ($setroot == 0) {                
                        if ( strlen($sifra) == 3) {
                        $root = 0;
                         $setroot = 1;
                         $parentfine = "NULL";
                        }
                        $subitem = 0;
                        $type = "NULL";
                         $parentfine = "NULL";
                        $newparentfine = "'$sifra'";
                       
                    } else {
                        $subitem = 1;
                    }
                    //}
                    
                    break;
                case 1:
                    $name = $val;
                    break;
                case 27:
                    $amount1 = valtosql(str_replace(",","",$val));
                    break;
                case 28:
                    $amount2 = valtosql(str_replace(",","",$val));
                    break;
                case 29:
                    $amount3 = valtosql(str_replace(",","",$val));
                    break;
            }
        }
        if (! $begin) continue;
   
            if (strpos($name,"INFORMATIZACIJA") !== false) {
              $type = "'1'"; 
             }
        echo "$sifra $name $amount1 $amount2 $amount3";
        echo "\n";
        if ($root != 0) {
            //$setroot = 0;
        }
       
           $sql = "INSERT INTO MainItems (code,name,parent,amount1,amount2,amount3,typecode,subitem,parentfine) VALUES ('$sifra','$name','$root',$amount1,$amount2,$amount3,$type,'$subitem',$parentfine);";
        if ($newparentfine != $parentfine) {
            $parentfine = $newparentfine;
        }
     echo $sql."\n";
      pg_query($sql);
          $sql = "SELECT id from MainItems ORDER by id desc limit 1";
    $res = pg_query($sql);
    $lastid = pg_result($res,0,0);
      if ($setroot) {
        $root = $lastid;
        $setroot = 0;
      }
      
    } 
 pg_query("COMMIT;");

exit(0);
{
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