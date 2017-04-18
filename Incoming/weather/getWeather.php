<?php
/*
Extracts the weather from consultant dataset.
Returns a JSON object that is then added to sql database
*/

function format($arr){
    //format array to [UT,data]

    $dataArr = array();
    for ($i=0;$i<sizeof($arr);$i++){
        $dt = date_create_from_format("d-m-Y H:i:s",$arr[$i]["date"]);
        $UT = $dt->getTimestamp();

        $dataArr[] = array("UT"=>$UT,
                            "temp"=>$arr[$i]['temp'],
                            "rhum"=>$arr[$i]['rhum'],
                            
                            "srad"=>$arr[$i]['srad'],
                            "wdir"=>$arr[$i]['wdir'],
                            "wspd"=>$arr[$i]['wspd'],
                            "preci"=>$arr[$i]['rain'],
                            "slp"=>$arr[$i]['baro']);
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
        "start"=>"01-01-2014",
        "end"=>"01-01-2015"
        );

    $url = "http://www.dbnrain.co.za/dl/aws_read_db.php?";//sid=".$vars["sid"]."&start=".$vars["start"]."&end=".$vars["end"];
    $url.=http_build_query($vars,'','&');;

    
    $handle = file_get_contents($url);
    

    $json = json_decode($handle,true);

    //correct format
    $dataArr = format($json);
    
    $dataCol=array("temp","rhum","srad","wdir","wspd","preci","slp");
    $returnArr[]=$dataArr;
    $returnArr[]=$dataCol;
    
    return $returnArr;
}

// time vars
$start = new DateTime();
$end = new DateTime();

//sub some time
$start->sub(new DateInterval('PT1H'));

include_once('/path/to/sql/class/phpscript');

$sql = new dbnfewsSQL('relationshipTable','dataTable');

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
        $sql->insertSQL($data[0],$nameID,"column/id",$data[1]);
    } else{
        continue;
    }
}
?>
