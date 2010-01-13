<?php
/**
 * rate.php  Rate encapsulation class
 * 
 * This file is designed to encapsulate the properties and
 * functions of a rate for a given product.
 * @author Eric Tamme <mail@etamme.com>
 * @version 1.0
 * @package class
 */
class rate
{

private $product_id;
private $pattern;
private $dialcode;
private $peer;
private $protocol;
private $buy_rate;
private $sell_rate;
private $connect_fee;
private $minimum_duration;
private $billing_increment;
  

/**
 * rate constructor
 * @param int $pattern the dialed string
 * @param int $product_id the product the customer has
 * @return object 
 */
public function __construct($pattern,$product_id)
{
  $this->pattern=$pattern;
  $this->product_id=$product_id;
  /* Find the rate and populate the rest of the member variables */
  if(!$this->find_rate())
    throw new Exception("Cannot find matching rate");
}
  
/**
  * returns a textual representation of all attributes
  * @return string 
  */
public function __toString()
{
  return $this->get_rate_info();
}
  
/**
  * Dubugging method, returns the first query to be executed for matching the dial string
  * @return string 
  */
public function show_query()
{
  $query="SELECT rate_id,product_id,region,dialcode,rate,minimum,increment,connect_fee,cost,peer,proto 
            FROM rate
            WHERE 
            rate.product_id = '".$this->product_id."'
            AND rate.dialcode = '".$this->pattern."'
            ";
  return $query;
}
 
/**
  * populates the internal class variables from a mysql result set
  * @param a mysql result set  
  */
private function populate_result($res)
{
  /* get the array and populate vars here */
  $row=mysql_fetch_array($res) or die(mysql_error());
  $this->dialcode=$row["dialcode"];
  $this->peer=$row["peer"];
  $this->protocol=$row["proto"];
  $this->buy_rate=$row["cost"];
  $this->sell_rate=$row["rate"];
  $this->connect_fee=$row["connect_fee"];
  $this->minimum_duration=$row["minimum"];
  $this->billing_increment=$row["increment"];
}
  
/**
  * Finds the rate for a given pattern, populates class vars by calling populate_result
  */
private function find_rate()
{
  $tmp_pattern=$this->pattern; /* set temp var so we dont obliterate our real pattern */
  
  $match=false;
  while(!$match)
  {

    /* This is a catch all for domestic, eventually we will
      * want this to be an option for a plan
      */
    if(substr($tmp_pattern,0,3)!="011" )
    {
      /* set the domestic rate lookup value */
      $tmp_pattern="DOMESTIC"; 	
    }
      
    /*
    *  $this query finds a rate for the product that the customer of product_id has
    */
    $query="SELECT rate_id,product_id,region,dialcode,rate,minimum,increment,connect_fee,cost,peer,proto 
            FROM rate
            WHERE 
            rate.product_id = '".$this->product_id."'
            AND rate.dialcode = '$tmp_pattern'
            ";
      

    $res=mysql_query($query);
    if(mysql_num_rows($res)>0)
    {
      $this->populate_result($res);
      return 1;
    }
    else
    {
      /* eliminate a digit to continue the search */
      $tmp_pattern=substr($tmp_pattern,0,-1);
      
      /* check to see if we can't eliminate any more digits */
      if(substr($tmp_pattern,0,3)=="011" AND strlen($tmp_pattern)<4)
      {
        return 0;/* International and less than 1 significant digit */
      }
      if(substr($tmp_pattern,0,3)!="011" AND strlen($tmp_pattern)<3)
      {
        return 0;/* Domestic and no complete areacode match */
      }
    }
  }
}
  
/**
  * compute the lowest possible cost (to the customer) for a given the minumum, connect fee, etc.
  * @return float 
  */
public function get_minimum_cost()
{
  return round((($this->sell_rate*($this->minimum_duration/60))+$this->connect_fee), 4);
}

/*------------------------------------- Accessors ---------------------------------------------*/

public function get_rate_info()
{
  $res ="\nProduct ID:".$this->product_id;
  $res.="\nPattern:".$this->pattern;
  $res.="\nDialcode:".$this->dialcode;
  $res.="\nPeer:".$this->peer;
  $res.="\nProtocol:".$this->protocol;
  $res.="\nBuy rate:".$this->buy_rate;
  $res.="\nSell rate:".$this->sell_rate;
  $res.="\nConnect Fee:".$this->connect_fee;
  $res.="\nMinimum Duration:".$this->minimum_duration;
  $res.="\nBilling Increment:".$this->billing_increment;
  return $res;
}

/**
  * gets the rates product_id
  * @return float 
  */
public function get_product_id()
{
  return $this->product_id;
} 

/**
  * gets the rates pattern
  * @return float 
  */
public function get_pattern()
{
  return $this->pattern;
} 

/**
  * gets the rates dialcode
  * @return float 
  */
public function get_dialcode()
{
  return $this->dialcode;
} 

/**
  * gets the rates peer (carrier)
  * @return float 
  */
public function get_peer()
{
  return $this->peer;
} 

/**
  * gets the rates protocol
  * @return float 
  */
public function get_protocol()
{
  return $this->protocol;
}

/**
  * gets the companies buy rate
  * @return float 
  */
public function get_buy_rate()
{
  return $this->buy_rate;
}

/**
  * gets the customers rate, the "sell rate"
  * @return float 
  */
public function get_sell_rate()
{
  return $this->sell_rate;
}

/**
  * gets the connect fee
  * @return float 
  */
public function get_connect_fee()
{
  return $this->connect_fee;
}

/**
  * gets the rates minimum duration
  * @return float 
  */
public function get_minimum_duration()
{
  return $this->minimum_duration;
}

/**
  * gets the rates billing increment
  * @return float 
  */
public function get_billing_increment()
{
  return $this->billing_increment;
} 

}
?>
