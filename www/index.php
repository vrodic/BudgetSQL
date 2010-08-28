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
    <input type="submit" value="Tip">
<form><br>

<?php
require("include/dbcon.inc");
function fmoney($num) {
    return number_format($num,0,".",",");
}
setlocale(LC_ALL,"en_US.utf8");
$parent = $_GET["parent"];
$typecode = $_GET["typecode"];
$nameq = mb_strtoupper($_GET["nameq"]);
$nameq = strtr($nameq, 'čćžšđ','ČĆŽŠĐ' ); // if anybody knows a better alternative to uppercase croatian characters, please shout 

if ($parent == "") $parent = 0;
if ($nameq) {
    $where = "and upper(name) like '%$nameq%' ";
}
    $fullwhere = " WHERE parent='$parent' and amount1 is not null $where ";
    
    if ($typecode > 0) {
        $fullwhere = " WHERE typecode='$typecode' and amount1 is not null $where";
    }
    $sql = "SELECT id, name, code,amount1,amount2,amount3,parent  FROM MainItems $fullwhere ORDER BY amount1 DESC";
   
    $res = pg_query($sql);
    $sql = "SELECT sum(amount1),sum(amount2),sum(amount3)  FROM MainItems $fullwhere ";
   
    $res2 = pg_query($sql);
    $total = fmoney(pg_result($res2,0,0));
    
    //echo $sql."<br>";
  
    ?>
    
    <table>
        <tr><td></td><td align=right><b>Total:</b> <? echo $total;?><br></td><td></td><td></td></tr>
    <tr><td><b>Naziv stavke</b></td><td align=right><b>Iznos 2010</b></td><td align=right><b>Iznos 2011</b></td><td align=right><b>Iznos 2012</b></td></b></tr>
    <?
    while ($row = pg_fetch_assoc($res)) {
        
          if ($typecode >0 ) {
            $parent = $row['parent'];
          
        $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE id='$parent'";
        $res3 = pg_query($sql);
        $pname = pg_result($res3,0,1);
        echo "<tr><td><b>$pname</b></td></tr>";
        }
        echo "<tr>";        
      echo "<td><a href='index.php?parent=".$row['id']."'>".$row['name']."</a></td>";
      $amount1 = fmoney($row['amount1']);
      $amount2 = fmoney($row['amount2']);
      $amount3 = fmoney($row['amount3']);
  echo "<td align='right'>".$amount1."</td>";
  echo "<td align='right'>".$amount2."</td>";
  echo "<td align='right'>".$amount3."</td>";
  echo "</tr>";
    }
    echo "</table>";


?>
</body>
</html>