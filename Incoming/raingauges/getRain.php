<?php

function format($arr){
    //format array to [UT,data]

    $dataArr = array();
    for ($i=0;$i<sizeof($arr);$i++){
        $dt = date_create_from_format("d-m-Y H:i:s",$arr[$i]["date"]);
        $UT = $dt->getTimestamp();

        $dataArr[] = array("UT"=>$UT,
                            "data"=>$arr[$i]['value']);
    }

    return $dataArr;
}
function get_Data($sid,$start,$end){
    /*
    Takes in station id and start and end dates and returns decoded json object
    */

    //station
    //$sid = 'ballito';

    $dataCol = array();
    $returnArr= array();

    $vars = array(
        "sid"=>$sid,
        "start"=>$start->format('d-m-Y H:i:s'),
        "end"=>$end->format('d-m-Y H:i:s')
    );

    $varsAll = array(
        "sid"=>$sid,
        "start"=>"",
        "end"=>""
        );

    // Create HTTP stream context
    $url = 'http://www.dbnrain.co.za/dl/rain_read_db.php?';
    $url.= http_build_query($vars,'','&');
    // Make POST request
    
    $response = file_get_contents($url);
    //echo $response."<br>";
    $json = json_decode($response,true);

    //correct format
    $dataArr = format($json);
    
    $dataCol[]="data";
    $returnArr[]=$dataArr;
    $returnArr[]=$dataCol;

    return $returnArr;
}

//include sql talk class

include_once('/path/to/sql/class/file');

$sql = new dbnfewsSQL('raingauges','raingauge_data');

$allNames = $sql->getNames();

$start = new DateTime();
$end = new DateTime();

//sub some time
$start->sub(new DateInterval('PT1H'));

for ($i=0;$i<sizeof($allNames);$i++){
    $name = $allNames[$i];
    echo $name."<br>";
    $nameID = $sql->getNAME_ID($name);

    $data = get_Data(mb_strtolower($name),$start,$end);
    
    if (sizeof($data[0])>0){
        $sql->insertSQL($data[0],$nameID,"raingauge_id",$data[1]);
    } else{
        continue;
    }
}
?>
