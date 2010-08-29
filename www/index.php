<html> 
<head> 
<title>Budget for Croatia</title> 
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> 
    <script type="text/javascript" src="include/js/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="include/js/jquery-ui-1.8.4.custom.min.js"></script>
    <link rel="stylesheet" type="text/css" href="include/css/ui-lightness/jquery-ui-1.8.4.custom.css" /> 
</head>
<body>
<script type="text/javascript">
	$(function() {
    
		$("a, input:submit", ".root").button();
		
	});
</script>

<?php
require("include/dbcon.inc");
function fmoney($num) {
    return number_format($num,0,".",",");
}

function getDetailByCode($code) {
     $sql = "SELECT id, name, parent,amount1,amount2,amount3 FROM MainItems WHERE code='$code'";
    return pg_query($sql);
}
function getDetailById($id) {
     $sql = "SELECT id, name, code,amount1,amount2,amount3 FROM MainItems WHERE id='$id'";
    return pg_query($sql);
}

?>


<div class="root">
	<a class="ui-state-default ui-corner-all" href="index.php">Root</a>
        <a class="ui-state-default ui-corner-all" href="index.php?interesting=1">Zanimljivo</a>

</div>
<table>
    <tr>
        <td>
<form id="search" action=index.php method="GET">
    Pretraži: <input type="text" name="nameq" value="">
      <input type="hidden" name="parent" value="-1">
</form>
        </td>
<td>
<form action=index.php method="GET">
    Po tipu: <select name="typecode">
    <option value=-1>Bez tipa</option>
    <option value=1>Informatizacija</option>
    </select>
     <input type="hidden" name="parent" value="-1">
    <input type="submit" class="root" value="Tip">
</form>
</td>
<td>
   <form action=index.php method="GET">
    Po šifri: <select name="code">
    <?
    $sql = "SELECT distinct name, code FROM MainItems where subitem=1 order by code;";
    $res = pg_query($sql);
     while ($row = pg_fetch_assoc($res)) {
        $cname = $row['name'];
        if (strlen($cname) > 35 ) $cname = substr($cname,0,32)."...";
        $ccode = $row['code'];
        echo " <option value=$ccode>$cname</option>";
     }
     
    ?>
   
    </select>
     <input type="hidden" name="parent" value="-1">
    <input type="submit" class="root" value="Tip">
</form>
</td> 
</td>
    
<?
setlocale(LC_ALL,"en_US.utf8");
$parent = $_GET["parent"];
$codeq = $_GET["codeq"];
$nosubitem = $_GET["nosubitem"];
$parentfine = $_GET["parentfine"];
$typecode = $_GET["typecode"];
$orderf = $_GET["orderf"];
$orderv = $_GET["orderv"];
$code = $_GET["code"];
$interesting = $_GET["interesting"];
$zname = pg_escape_string($_POST["zname"]);
$nameq = mb_strtoupper($_GET["nameq"]);
$nameq = strtr($nameq, 'čćžšđ','ČĆŽŠĐ' ); // if anybody knows a better alternative to uppercase croatian characters, please shout 
?>
<td>
<form id="addint" action=index.php method="POST">
    Ime:<input type="text" name="zname" value="">
    <input type="submit" class="root" value="+ Zanimljivo">
   <input type="hidden" name="params"
        value="<?
        $val = "parent=$parent&parentfine=$parentfine&typecode=$typecode&code=$code&nameq=$nameq&codeq=$codeq&orderf=1&orderv=$orderv";
        $val = str_replace(" ", "+", $val);
        echo $val;
        ?>">
</form>
</td>
    </tr></table>
<?
if ($zname) {
    $zparams = pg_escape_string($_POST['params']);
    $sql = "INSERT INTO Interesting (name, params) VALUES('$zname','$zparams')";
    //echo $sql;
    pg_query($sql);
    $interesting = 1;
}
if ($interesting) {
    $sql = "SELECT name, params FROM interesting order by clickcnt";
    $res = pg_query($sql); 
    echo "<table>";
    while ($row = pg_fetch_assoc($res)) {
        $name = $row['name'];
        $params = $row['params'];
        echo "<tr><td><a href=index.php?$params>$name</a></td><td></td></tr>";
    }
    echo "</table>";
    
}

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

if ($orderf) {
    if (!$orderv) $orderv = "DESC";
    $order = " amount$orderf $orderv";
    if ($orderv == "DESC") {
        $orderv = "ASC";
    } else {
        $orderv = "DESC";
    }
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
       // echo $sql."<br>";
   

$totalp = 0;
$totalph = "";
    

if ($parent > 0) {
        $res3 = getDetailById($parent);
        $pname = pg_result($res3,0,1);
        echo "<br><b>$pname</b><br>";
        $totaluf = pg_result($res3,0,3);
        $total = fmoney($totaluf);
        $totalp = 1;
}
if ($parentfine) {
        $res3 = getDetailByCode($parentfine);
        $pname = pg_result($res3,0,1);
        $pparent = pg_result($res3,0,2);
        if ($parent < 0) {
            $res4 = getDetailById($pparent);

            $ppname = pg_result($res4,0,1);
            echo "<b><a href=index.php?parent=$pparent>$ppname</a></b><br>";
        }
        echo "<b>$pname</b><br>";
}

if ($parent < 1 && ! $parentfine) {
    $totalp = 1;
 $sql = "SELECT sum(amount1),sum(amount2),sum(amount3)  FROM MainItems $fullwhere ";
   
    $res2 = pg_query($sql);
    $totaluf = pg_result($res2,0,0);
    $total = fmoney($totaluf);
}
    ?>
    
    
    <table>
        <?
        if ($totalp) {
        echo "<tr><td></td><td></td><td></td><td align=right><b>Total: </b>$total<br></td><td></td><td></td></tr>";
        $totalph = "<td>% od ukupnog </td>";
        }
        ?>
        
    <tr><td><b>Šifra</b></td><td><b>Naziv stavke</b></td><? echo $totalph;?><td align=right><b>
    <?
    echo "<a href='index.php?parent=$parent&parentfine=$parentfine&typecode=$typecode&code=$code&nameq=$ &codeq=$codeq&orderf=1&orderv=$orderv'>
    Iznos 2010</a>";
    ?>
    </b></td><td align=right><b>Iznos 2011</b></td><td align=right><b>Iznos 2012</b></td></b></tr>
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
          $magic = substr($codea,0,1);
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
            $res3 = getDetailByCode($parentfinea);
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
        if (($row['subitem'] == 0 && ($magic == "A" || $magic == "K")) || $parent == 0) {
        if ($parent > 0 || $parent < 0) {
            $ahref ="<a href='index.php?parent=-2&parentfine=".$row['code']."'>";
            $aterm = "</a>";
        } else if ($parent==0){
            $ahref = "<a href='index.php?parent=".$row['id']."'>";
            $aterm = "</a>";
        }
        }
        if ($row['subitem'] == 0 && $magic != "A" && $magic !="K" && $parent != 0) {
            
            $ahref = "<b>";
            $aterm = "</b>";
        }
        
        $name = $row['name'];
        $spcname= "";
        if($row['subitem'] == "1") {
            
            if ($orderf) {
                $res3 = getDetailByCode($parentfinea);
                $pname = pg_result($res3,0,1);
                $name = "$name &nbsp;&nbsp; $pname";
            }
            $spcname = "&nbsp;&nbsp;";
            
        } 
       
      echo $spcname.$ahref.$name."$aterm</td>";
      $amount1 = fmoney($row['amount1']);
      $amount2 = fmoney($row['amount2']);
      $amount3 = fmoney($row['amount3']);
      if ($totalp) {
        $percent = ($row['amount1']/$totaluf)*100;
        $js .= "$(\"#pbar$cnt\").progressbar({
			value: $percent
		});
                $(\"#pbar$cnt\").height(20);
                ";
        echo "<td><div id='pbar$cnt'></div>".number_format($percent,2)."</td>";  
      }
      
  echo "<td align='right'  bgcolor=$col>".$amount1."</td>";
  echo "<td align='right' bgcolor=$col>".$amount2."</td>";
  echo "<td align='right' bgcolor=$col>".$amount3."</td>";
  echo "</tr>";
    }
    echo "</table>";


?>
    	
	<script type="text/javascript">
	$(function() {
            <? echo $js;?>
		
	});
	</script>

</body>
</html>