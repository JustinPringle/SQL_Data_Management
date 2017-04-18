<?php

/*
This script sends data to the new tables.
Connect script sits outside public_html.

First is to query relationship table to get the ID for each gaugeName
*/

function get_Data($loc,$dt){
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

    $url = "http://www.mycity.co.za/cities/durban-metro/water/storm-water/$loc/@@samples?";
    $url.=http_build_query($vars,'','&');

    //echo $url."<br>";
    $response = file_get_contents($url,false,$context);
    //echo $response;
    $lines = explode("\r\n",$response);

    
    $data_Arr = array();
    $dataCol = array();
    $returnArr= array();
    //echo sizeof($lines)."<br>";
    $i=0;
    foreach ($lines as $ln){
        //echo $i." ".sizeof($lines)."<br";
        //if ($i==sizeof($lines)-1){
         //   break;
        //}
        if ($i>6){
            $row = explode(",",$ln);
            $date = explode(" ",$row[0]);
            $level = $row[3];
                       
            $dateYMD = $date[0];

            if (sizeof($date)>2){
                $dateHMS = $date[1];
            } elseif(sizeof($data)==2){
                $dateHMS = "00:00:00";
            } else{
                continue;
            }
            
            $tm = date_create_from_format("Y/m/d H:i:s","".$dateYMD." ".$dateHMS);
            $UT = $tm->getTimestamp();

            $data_Arr[] = array("UT"=>$UT,"data"=>$level);

            
        //echo $ln."<br>";
        }
        $i++;
    }
    $dataCol = array("data");
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

    if ($name=="lalucia"){
        $name="general";
    } else if ($name=="virginia_airport"){
        $name = "virginia-airport";
    }

    $data = get_Data(mb_strtolower($name),$dt);
    $sql->insertSQL($data[0],$nameID,"column/id",$data[1]); //column that contains gauge ID
}

?>
