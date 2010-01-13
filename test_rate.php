<?php
require('rate.php');
$host="127.0.0.1";
$user="";
$pass="";
$db="ccapp";

$pattern="01133144385600";
$product="1";

mysql_connect($host,$user,$pass);
mysql_select_db($db);
try
{
  /* create a new rate. pass pattern and product id */
  $my_rate = new rate($pattern,$product);
  /* print out rate values */
  echo $my_rate."\n"; 
  echo "Getting individual elements\n";
//  echo $my_rate->show_query()."\n";
  echo "Minimum duration:".$my_rate->get_minimum_duration()."\n";
  echo "Sell rate:".$my_rate->get_sell_rate()."\n";
  echo "Connect fee:".$my_rate->get_connect_fee()."\n";
  echo "Minimum Cost:".$my_rate->get_minimum_cost()."\n";
}
catch(Exception $e){

  echo $e->getMessage()."\n";
}
?>
