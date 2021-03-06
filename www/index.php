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
require("include/mediawiki.inc");
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
        <a class="ui-state-default ui-corner-all" href="http://mfin.hr/hr/drzavni-proracun-2010" target="_blank">Source data</a>
        <a class="ui-state-default ui-corner-all" href="http://github.com/vrodic/BudgetSQL" target="_blank">Source code</a>        
        <a class="ui-state-default ui-corner-all" href="index.php?donate=1">Doniraj</a>        
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
	<input type="hidden" name="nosubitem" value="1">
    <input type="submit" class="root" value="Upit">
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
    <input type="submit" class="root" value="Upit">
</form>
</td> 
</td>
    
<?
setlocale(LC_ALL,"en_US.utf8");
$parent = $_GET["parent"];
$id = $_GET["id"];
$codeq = $_GET["codeq"];
$nosubitem = $_GET["nosubitem"];
$donate = $_GET["donate"];
$parentmid = $_GET["parentmid"];
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
        $val = "parent=$parent&parentfine=$parentfine&parentmid=$parentmid&typecode=$typecode&code=$code&nameq=$nameq&codeq=$codeq&orderf=1&orderv=$orderv&nosubitem=$nosubitem";
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

if ($donate) {
?>
<br>
    Komentare i sugestije šaljite <a href="mailto:vrodic@gmail.com">Vedranu Rodiću</a><br><br>
        
    Donirajte na autorov tekući račun Zagrebačke banke:<br>
Broj banke: <b>2360000-1000000013</b><br> 
Model: <b>17</b><br>
Poziv na broj: <b>3218176032</b><br>
Pod napomenu stavite: <b>Budget surfer</b><br>
<?

}

if ($nameq) {
    $where .= " and upper(name) like '%$nameq%' ";
}

if ($codeq) {
    $where .= " and code like '$codeq%' ";
}


if ($id) {
   $where .= "and id='$id' ";
   $sql = "SELECT parentfine, parentmid, parent FROM MainItems WHERE id='$id'";
   $res0 = pg_query($sql);
   $parentfine = pg_result($res0,0,0);
   $parentmid = pg_result($res0,0,1);
   $parent = pg_result($res0,0,2);
   echo "$parent $parentmid $parentfine <Br>";
   if ($parentfine) {
	$parentmid = "";
   }
   if ($parentmid || $parentfine) {
	$parent = "-2";
   }
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
}

if ($nosubitem){
    $where .= "and subitem='0' ";
}

if ($parentmid) {
    $where .= "and parentmid='$parentmid' ";
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
//  echo $sql."<br>";
   

$totalp = 0;
$totalph = "";
    

if ($parent > 0) {
        $res3 = getDetailById($parent);
        $pname = pg_result($res3,0,1);
        echo "<br><b><a href=index.php?parent=$parent>$pname</a></b><br>";
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

if ($parentmid) {
        $res3 = getDetailByCode($parentmid);
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

	$vline = "<td width=\"1\" bgcolor=\"#000000\"><img src=\"img/1-1.gif\" width=\"1\"
height=\"1\" border=\"0\" alt=\"\" /></td>";
    ?>
    
    
    <table CELLSPACING=0 CELLPADDING=0>
        <?
        if ($totalp) {
        echo "<tr><td></td>$vline<td></td><td></td><td><td></td></td>$vline<td align=right><b>Total: </b>$total&nbsp;</td>$vline<td></td>$vline<td></td></tr>";
	?>
	<tr><td colspan='12' bgcolor="#000000"><img src="img/transparent.gif" width="1" height="1" border="0"></td></tr>
	<?
        $totalph = "$vline<td>% od ukupnog </td>";
        }
        ?>
        
    <tr><td><b>Šifra&nbsp;</b></td><? echo $vline;?><td><b>Naziv stavke&nbsp;</b></td><td><b></b></td><? echo $totalph.$vline;?><td align=right><b>
    <?
    echo "<a href='index.php?parent=$parent&parentfine=$parentfine&parentmid=$parentmid&typecode=$typecode&code=$code&nameq=$nameq&codeq=$codeq&orderf=1&orderv=$orderv&nosubitem=$nosubitem'>
    &nbsp;Iznos 2010&nbsp;</a>";
    ?>
    </b></td><? echo $vline;?><td align=right><b>&nbsp;Iznos 2011&nbsp;</b></td><? echo $vline;?><td align=right><b>&nbsp;Iznos 2012&nbsp;</b></td></b></tr>
    <tr><td colspan='12' bgcolor="#000000"><img src="img/transparent.gif" width="1" height="1" border="0"></td></tr>
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
		echo "<tr><td bgcolor=$col>$pcode</td>$vline<td  bgcolor=$col><b>".$ahref.$pname.$aterm."</b></td></tr>";
          
		if (! $codeq) {
			$res3 = getDetailByCode($parentfinea);
			$pname = pg_result($res3,0,1);
			$pcode = pg_result($res3,0,2);
			$ahref ="<a href='index.php?parent=-2&parentfine=".$parentfinea."'>";
			$aterm = "</a>";
			echo "<tr><td  bgcolor=$col>$pcode</td>$vline<td  bgcolor=$col><b>".$ahref.$pname.$aterm."</b></td></tr>";
		}
        }
        echo "<tr><td bgcolor=$col>$codea</td>$vline";
        echo "<td bgcolor=$col>&nbsp;";
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
            $ahref ="<a href='index.php?parent=-2&parentmid=".$row['code']."'>";
            $aterm = "</a>";
        }
        
        $name = $row['name'];
        $spcname= "";
        if($row['subitem'] == "1" && $parent > 0 ) {
            
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
      $id0 = $row['id'];
      $img = "edit_empty.png";
      if (checkPage("ID_$id0")) {
	$img = "edit_filled.png";
      }
      echo "<td bgcolor=$col>&nbsp;<a href=index.php?id=$id0><img src='img/$img'  border='0'></a>&nbsp;</td>";
      
      if ($totalp) {
        $percent = ($row['amount1']/$totaluf)*100;
        $js .= "$(\"#pbar$cnt\").progressbar({
			value: $percent
		});
                $(\"#pbar$cnt\").height(20);
                ";
        echo "$vline<td bgcolor=$col><div id='pbar$cnt'></div>".number_format($percent,2)."</td>";  
      }
      
  echo "$vline<td align='right'  bgcolor=$col>&nbsp;".$amount1."&nbsp;</td>";
  echo "$vline<td align='right' bgcolor=$col>&nbsp;".$amount2."&nbsp;</td>";
  echo "$vline<td align='right' bgcolor=$col>&nbsp;".$amount3."&nbsp;</td>";
  echo "</tr>";
    }
    echo "</table>";

if ($id && $mediawiki) {
?>

<iframe src ="/mediawiki/index.php?title=ID_<? echo $id; ?>" width="100%" height="800">
  <p>Your browser does not support iframes.</p>
</iframe>
<?
}

?>
    	
	<script type="text/javascript">
	$(function() {
            <? echo $js;?>
		
	});
	</script>

</body>
</html>