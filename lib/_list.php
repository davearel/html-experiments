<html>
<head>
<title>Pointillate</title>
</head>
<body>
<?
$absolute_path = "./";
$dl = ".";
$dir = opendir($absolute_path);
while($file = readdir($dir)) {
  if (($file != "..") && ($file != ".") && ($file != "index.php") && (stristr($file,".DS_Store") == false)) {
    $list .= "<div><a href='$dl/$file'>$file</a></div>";
  }
}
echo $list; 
?>
</body>
</html>