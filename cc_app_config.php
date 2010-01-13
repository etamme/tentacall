<?
/* -------Database variables---------- */
$dbhost="127.0.0.1"; 
$dbuser="ccapp";
$dbpass="somepass";
$db="ccapp";

/* --------Context variables-----------*/
$dcontext="ccapp";
$dextension="ccdial";
$dpriority="1";

/*
*******************************************************************************
* Given the above settings, your extensions.conf should contain the following *
*******************************************************************************

[ccapp]
exten => ccdial,1,NOOP(ACCID=${ACCID} PATTERN=${PATTERN} RATE=${RATE} TIME=${TIME} PROTO=${PROTO} PEER=${PEER})
exten => ccdial,n,Dial(${PROTO}/${PEER}/${PATTERN},,L(${TIME}:20000:10000))
exten => h,1,DeadAGI(cc_app_cleanup.agi)

*/
?>
