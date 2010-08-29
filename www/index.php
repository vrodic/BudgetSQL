<html> 
<head> 
<title>Budget for Croatia</title> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
</head>
<body>
<b>Proračun</b><br>   
<form action=index.php method="GET">
    Pretraži: <input type="text" name="nameq" value="">
<form>
    
<form action=index.php method="GET">
    Po tipu: <select name="typecode">
    <option value=-1>Bez tipa</option>
    <option value=1>Informatizacija</option>
    </select>
     <input type="hidden" name="parent" value="-1">
    <input type="submit" value="Tip">
<form><br>

<?php
require("include/dbcon.inc");
function fmoney($num) {
    return number_format($num,0,".",",");
}
setlocale(LC_ALL,"en_US.utf8");
$parent = $_GET["parent"];
$codeq = $_GET["codeq"];
$nosubitem = $_GET["nosubitem"];
$parentfine = $_GET["parentfine"];
$typecode = $_GET["typecode"];
$code = $_GET["code"];
$nameq = mb_strtoupper($_GET["nameq"]);
$nameq = strtr($nameq, 'čćžšđ','ČĆŽŠĐ' ); // if anybody knows a better alternative to uppercase croatian characters, please shout 

if ($nameq) {
    $where .= " and upper(name) like '%$nameq%' ";
}

if ($codeq) {
    $where .= " and code like '$codeq%' ";
}


if ($parent == "") $parent = 0;
if ($parent >=0) {
    $where .= " and parent='$parent' ";
    
}
if ($parent == 0 || $parent == -1) {
    $order =" amount1 DESC";
} else {
    $order =" id ASC";
}

if ($parentfine) {
    $where .= "and parentfine='$parentfine' ";
} else if ((! $code && ! $codeq &&  $parent < 0) || $nosubitem){
    $where .= "and subitem='0' ";
}

    if ($code) {
        $where .= " and code='$code' ";
    }


    $fullwhere = " WHERE amount1 is not null $where ";
    
    if ($typecode > 0) {
        $fullwhere = " WHERE typecode='$typecode' and amount1 is not null $where";
    }
    $sql = "SELECT id, name, code,amount1,amount2,amount3,parent,parentfine,subitem  FROM MainItems $fullwhere ORDER BY $order";
   
    $res = pg_query($sql);
        echo $sql."<br>";
   
    

if ($parent > 0) {
        $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE id='$parent'";
        $res3 = pg_query($sql);
        $pname = pg_result($res3,0,1);
        echo "<b>$pname</b><br>";
}
if ($parentfine) {
        $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE code='$parentfine'";
        $res3 = pg_query($sql);
        $pname = pg_result($res3,0,1);
        echo "<b>$pname</b><br>";
}

if ($parent < 1) {
 $sql = "SELECT sum(amount1),sum(amount2),sum(amount3)  FROM MainItems $fullwhere ";
   
    $res2 = pg_query($sql);
    $total = fmoney(pg_result($res2,0,0));
}
    ?>
    
    
    <table>
        <?
        if ($parent < 1) {
        echo "<tr><td></td><td></td><td align=right><b>Total:</b>$total<br></td><td></td><td></td></tr>";
        }
        ?>
        
    <tr><td><b>Šifra</b></td><td><b>Naziv stavke</b></td><td align=right><b>Iznos 2010</b></td><td align=right><b>Iznos 2011</b></td><td align=right><b>Iznos 2012</b></td></b></tr>
    <?
    $cnt = 0;
    while ($row = pg_fetch_assoc($res)) {
        $cnt ++;
        if ($cnt & 1) {
            $col = "#ffffff";
        } else {
            $col = "#dddddd";
        }
         $parenta = $row['parent'];
          $codea = $row['code'];
          $parentfinea = $row['parentfine'];
          if ($parent == -1 ) {
           
          
        $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE id='$parenta'";
        $res3 = pg_query($sql);
        $pname = pg_result($res3,0,1);
        $pcode = pg_result($res3,0,2);
         $ahref = "<a href='index.php?parent=".$parenta."'>";
                $aterm = "</a>";
        echo "<tr><td bgcolor=$col>$pcode</td><td  bgcolor=$col><b>".$ahref.$pname.$aterm."</b></td></tr>";
          
          if (! $codeq) {
            $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE code='$parentfinea'";
            $res3 = pg_query($sql);
            $pname = pg_result($res3,0,1);
            $pcode = pg_result($res3,0,2);
            $ahref ="<a href='index.php?parent=-2&parentfine=".$parentfinea."'>";
            $aterm = "</a>";
            echo "<tr><td  bgcolor=$col>$pcode</td><td  bgcolor=$col><b>".$ahref.$pname.$aterm."</b></td></tr>";
          }
        }
        echo "<tr><td bgcolor=$col>$codea</td>";
        echo "<td bgcolor=$col>";
        $aterm = "";
        $ahref = "";
        if ($row['subitem'] == 0) {
        if ($parent > 0 || $parent < 0) {
            $ahref ="<a href='index.php?parent=-2&parentfine=".$row['code']."'>";
            $aterm = "</a>";
        } else if ($parent==0){
            $ahref = "<a href='index.php?parent=".$row['id']."'>";
            $aterm = "</a>";
        }
        }
        $name = $row['name'];
        $spcname= "";
        if($row['subitem'] == "1") {
            $spcname = "&nbsp;&nbsp;";
        } 
       
      echo $spcname.$ahref.$name."$aterm</td>";
      $amount1 = fmoney($row['amount1']);
      $amount2 = fmoney($row['amount2']);
      $amount3 = fmoney($row['amount3']);
  echo "<td align='right'  bgcolor=$col>".$amount1."</td>";
  echo "<td align='right' bgcolor=$col>".$amount2."</td>";
  echo "<td align='right' bgcolor=$col>".$amount3."</td>";
  echo "</tr>";
    }
    echo "</table>";


?>
</body>
</html>