<?php

/*
This script sends data to the new tables.
Connect script sits outside public_html.

First is to query relationship table to get the ID for each gaugeName
*/

function get_Data($river,$dt){
    /*
    gets the data from CSV on Mycity - uses a cookie
    returns data as an array
    */

    $opts = array(
    "http"=>array(
        "method"=>"POST",    
    "header"=>"Cookie: ADD COOKIE HERE\r\n"
    )
    );

    $context = stream_context_create($opts);
    $vars = array(
        "dateformat"=>"1",
        "date"=>$dt
        );

    $url = "http://www.mycity.co.za/cities/durban-metro/water/storm-water/$river/@@samples?";
    $url.=http_build_query($vars);//,"","&");

    //echo $url."<br>";
    $response = file_get_contents($url,false,$context);
    
    $lines = explode("\r\n",$response);

    $data_Arr = array();
    $dataCol = array();
    $returnArr= array();
    
    $i=0;
    foreach ($lines as $ln){
        
        if ($i>6){
            $row = explode(",",$ln);
            $date = explode(" ",$row[0]);
            $level = $row[2];

            if ($river=="palmiet"){
                $level = $row[3];
            }
            
            $dateYMD = $date[0];

            if (sizeof($date)>2){
                $dateHMS = $date[1];
            } elseif(sizeof($data)==2){
                $dateHMS = "00:00:00";
            } else{
                continue;
            }
            //print_r($date);
            //echo "".$dateYMD." ".$dateHMS."<br>";
            $tm = date_create_from_format("Y/m/d H:i:s","".$dateYMD." ".$dateHMS);
            $UT = $tm->getTimestamp();

            $data_Arr[] = array("UT"=>$UT,"data"=>$level);
            

            
        //echo $ln."<br>";
        }
        $i++;
    }
    $dataCol[]="data";
    $returnArr[]=$data_Arr;
    $returnArr[]=$dataCol;

    
return $returnArr;
}


//include sql talk class

include_once('/path/to/sql/class.php');

$sql = new dbnfewsSQL('relationshipTable','dataTable');

$allNames = $sql->getNames();

$dDate = new DateTime();
$dt = $dDate->format("Y-m-d");

for ($i=0;$i<sizeof($allNames);$i++){
    $name = $allNames[$i];
    //echo $name."<br>";
    $nameID = $sql->getNAME_ID($name);

    if ($name=="Umgeni"){
        continue;
    }
    //get name convention right
    if ($name=="InandaDamOut"){
        $name = "umngeni-1";
    }

    $data = get_Data(mb_strtolower($name),$dt);
    $sql->insertSQL($data[0],$nameID,"column/id",$data[1]); //column referring to gauge ID
}


?>
