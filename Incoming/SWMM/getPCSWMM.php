<?php
/*
Extracts the weather from consultant dataset.
Returns a JSON object that is then added to sql database
*/

function get_Data($tb,$array){
    /*
    send json decoded array to sql database
    */
    
    
    // hashable array linking names to tables    

    //hashable
    $table = $tb;
    $returnArr = array();
    // loop over array and generate sql query
    //print_r($array);
    //echo $array[$tb]."<br>";
    $dataArr = array();
    for ($i=0;$i<sizeof($array);$i++){
        //echo $array[$i]["date"]."<br>";       
        $date = date_create_from_format('Y-m-d H:i:s',$array[$i]["date"]);
        $UT = $date->getTimestamp();
    
        $dataArr[] = array("UT"=>$UT,"data"=>$array[$i][$tb]);
    }
        //must skip date
        /*foreach (array_keys($array[$i]) as $col){
            //echo $col."<br>";
            if ($col=="date"){
                continue;            
            }else{
        //$col = $sid;
            $value = $array[$i][$col];

            $dataArr["data"]=$value;
        }
        }
        
    }*/
    //print_r($dataArr);
    //echo "<br>";
    $returnArr[] = $dataArr;
    $returnArr[] = array("data");

    return $returnArr;
    


};

function getCSV($sid){
    /*
    Takes in swmm table and returns a formatted array
    */

    //first get the headers and then loop over rows
    
    $csvFile = "/path/to/csv/".$sid.".csv";
    //echo $csvFile."<br>";
    //open csv file and get col headers
    $data=array();
    
    $key = array();
    $row = 0;
    if (($handle = fopen("$csvFile",'r'))!=False){
        while (($levels=fgetcsv($handle)) !=False ){
            $temp_data = array();
            if($row==0){        
                $colNames = $levels;
                $key[] = "date";
                for ($j=1;$j<sizeof($colNames);$j++){
                    $temp = str_replace([' ','&','/','-','(',')'],['_','','','','_','_'],$colNames[$j]); //fix weird things
                    $key[] = strtolower($temp); 
                }       
            }
            else if ($row>1){
                $temp_data[] = $levels[0];                
                //assumes order remains same
                for ($j=1;$j<sizeof($colNames);$j++){                    
                    $temp_data[] = $levels[$j];
                    
                }
                //echo sizeof($key)." ".sizeof($temp_data)."<br>";
                $data[] = array_combine($key,$temp_data);                
            }
            $row++;
        }
    }
    
    return $data;
}


//get regions
$regions = "/path/to/dat/file/containing/region/names/regions.dat";
$regionArr = file($regions);
//include sql talk
include_once('/path/to/sql/class.php');

//loop over regions and get right table
for ($j=0;$j<sizeof($regionArr);$j++){
    $r = $regionArr[$j];
    //echo "$r<br>";
    $FCHC = split("_",$r);
    
    if ($FCHC[0]=="HydraulicPCSWMMFC"){
        $sqlTable = "hydraulic_forcast_data";
    } else {
        $sqlTable = "hydraulic_hindcast_data";
    }

    $sql = new dbnfewsSQL('hydraulic_instrument',$sqlTable);

    $allNames = $sql->getNames();
    $data = getCSV(trim($r));
    $keys = array_keys($data);
    
    for ($i=0;$i<sizeof($allNames);$i++){ //loop over time first

    //for ($k=0;$k<sizeof($data);$k++){

    
        $name = $allNames[$i];
        //echo $name."<br>";
        //if key -- or in this case station name, in array then send to sql table
        if (array_key_exists($name,$data[0])){
            $nameID = $sql->getNAME_ID($name);

            $dataArr = get_Data(mb_strtolower($name),$data);            
    
            $sql->insertSQL($dataArr[0],$nameID,"hydraulic_id",$dataArr[1]);
        } else{
            continue;
        }
    
    }
    
}
?>
