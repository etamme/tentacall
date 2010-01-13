<?

/** Function that does the dirty work of converting 
  * a rate into cost/ms then calculating the available
  * time in ms based on credit
  *
  */
function get_ms($agi,$credit,$rate_obj)
{
 $rate=$rate_obj->get_sell_rate();
 $con=$rate_obj->get_connect_fee();
 $inc=$rate_obj->get_billing_increment();

 /* multiply all values by 1000 (our precision)
  * until final calculation to avoid any floating point arithmatic
  * 
  * a millesecond is now 1, not .0001 since we have shifted
  */
 $credit =(int)($credit*1000);
 $con    =(int)($con   *1000);
 $rate   =(int)($rate  *1000);
 $inc    =(int)($inc   *1000);

 $credit=$credit-$con;
 $ms=(int)(($credit/$rate)*60000);
 
 if($remainder=$ms%$inc)
 { 
   $agi->verbose("subtracting remainder $remainder from $ms\n");
   $ms=$ms-$remainder;
   return $ms;
 }
 else
  return $ms;
}

function find_code($pattern)
{
$match=false;
 while(!$match)
 {
   if(substr($pattern,0,3)!="011" )
     return -2;

//   $agi->verbose("trying: SELECT dialcode from rate where dialcode ='$pattern'");
   $res=mysql_query("SELECT dialcode from rate where dialcode = '$pattern'");
   if(mysql_num_rows($res)>0)
   {
 //    $agi->verbose("Found pattern match $pattern");
    return $pattern;
    return 1;
   }
   else
   {
     $pattern=substr($pattern,0,-1);
     if(substr($pattern,0,3)=="011" AND strlen($pattern)<4)
     {
       //$agi->verbose("Abandon attempt at $pattern");
       return -1;/* International and less than 1 significant digit */
     }

     if(substr($pattern,0,3)!="011" AND strlen($pattern)<3)
     {
      // $agi->verbose("Abandon attempt at $pattern");
       return -1;/* Domestic and no complete areacode match */
     }
   }
 }
}

//echo find_code("01133158366700");

function set_vars($agi,$tms,$account_id,$pattern,$rate,$min,$inc,$connect_fee,$peer,$proto)
{
     $agi->exec("Set CDR(accountcode)=$account_id");/* set the account code for billing */
     $agi->exec("ResetCDR");
     $agi->exec("SET TIME=$tms");
     $agi->exec("SET ACCID=$account_id");
     $agi->exec("SET PATTERN=$pattern");
     $agi->exec("SET RATE=$rate");
     $agi->exec("SET MIN=$min");
     $agi->exec("SET INC=$inc");
     $agi->exec("SET PEER=$peer");
     $agi->exec("SET PROTO=$proto");
     $agi->exec("SET CONNECTFEE=$connect_fee");
     $total_ms=$total_inc*1000;
     $cdate=date("YmdHis");
     $agi->exec("SET CDATE=$cdate");
     $agi->verbose("getting ready to branch to ccapp context");
     return 1;
}
  function get_product($account_id)
  {
    $res=mysql_query("SELECT product_id from customer where account_id='$account_id'");
    if(mysql_num_rows($res)>0)
    {
      $row=mysql_fetch_array($res);
      return $row["product_id"];
    }
    else
    return -1;
  }


	// Gets the features for a given customer
	function get_features($account_id)
	{
		$result = mysql_query("	SELECT	
                                        f.name, cf.value
					FROM
					customer_features AS cf, feature AS f 
                                        WHERE												               
                                        cf.feature_id = f.uid
					AND cf.account_id = '$account_id'															   ");

		if(mysql_num_rows($result)<1)
                  return 0;
                 
		while($row = mysql_fetch_array($result))
		{
			$name = $row['name'];
			$value = $row['value'];
			$features[$name] = $value;	
		}
	
		return $features;
	}

/* 
* finds the the matching account id for any ani recognition feature 
* returns -1 if not found
* returns account number if found
*/
function lookup_cid($cid)
{
	$query="
	SELECT account_id
	FROM customer_features, feature
	WHERE customer_features.value = '$cid'
	AND feature.name LIKE 'ani_rec_%'
	AND feature_id = uid
	LIMIT 1 
	";

	$res=mysql_query($query);
	if(mysql_num_rows($res)<1)
		return -1;
	else
	{
		$row=mysql_fetch_array($res);
		$acc_id=$row["account_id"];
		return $acc_id;
	}
}

?>
