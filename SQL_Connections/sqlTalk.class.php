<?php
/*
class to talk to relationship database set up on DBNFEWS.

The construct binds an sql connection to the class.
This is used to talk to the database.

funcs:

getName(): 
    - accesses relationship tables.
    - returns the reference name - i.e. how consultants store/send variables

getName_ID():
    - gets the ID from the relationship tables.
    - this is used as a reference in data tables.

insertSQL():
    - takes data array in certain format
        - the keys are [UT, var1,...etc].
        - dataCol holds all keys that are required to send to sql tables - should relate to columns in the tables.
    - checks for duplicate keys in update-ref
*/


class dbnfewsSQL{

    private $_tableName;
    private $_dataTable;
    private $_con;
    private $_connctuser="USER";
    private $_connctpassword="PASSWORD";
    private $_connctdatabase="DATABASE";
    private $_conncthost="localhost";    

    public function __construct($table,$dataTable){
        $this->_tableName=$table;
        $this->_dataTable=$dataTable;
        $conn = new mysqli($this->_conncthost, $this->_connctuser, $this->_connctpassword, $this->_connctdatabase);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $this->_con = $conn;
    }

    public function getNAMES($nameCol=False){
        //Get all names in relationship table.
        //returns array with names

        $nameARR=array();
        if ($nameCol){
            $query = "SELECT name from ".$this->_tableName;
            $col = 'name';
        } else{
            $query = "SELECT reference from ".$this->_tableName;
            $col = 'reference';
        }
        $result = $this->_con->query($query);

        for ($i=0;$i<$result->num_rows;$i++){
            $row = $result->fetch_assoc();
            $nameARR[] = $row[$col];
        }

        return $nameARR;
    }

    public function getNAME_ID($station,$nameCol=False){
        //This method queries the "ID" table and 
        //returns the id of a particular station name.        
        if ($nameCol){
            $query = "SELECT id from ".$this->_tableName." WHERE name = '".$station."'"; 
        } else{
            $query = "SELECT id from ".$this->_tableName." WHERE reference = '".$station."'";
        }       
        $result = $this->_con->query($query);
        
        $id = $result->fetch_assoc();
        
        return $id['id'];

    }

    public function insertSQL($array,$id,$id_Col,$dataCols){
        //Insert the data into the data table

        for ($i=0;$i<sizeof($array);$i++){
        
            $UT = $array[$i]["UT"];
            $updateCheck = "$UT-$id";         

            $query = "INSERT INTO ".$this->_dataTable." (UT";
            //create a variable string to insert into the query
            $vals = " VALUES (".$UT;
            $toUpdate = " ON DUPLICATE KEY UPDATE ";

            //query for each time step
            for ($j=0;$j<sizeof($dataCols);$j++){
                //loop over the columns
                $var = $dataCols[$j];
                //append
                $query.=",".$var;
                $vals.=",".$array[$i][$var]."";//because they are floats
                if ($j==sizeof($dataCols)-1){
                    $toUpdate.="$var = ".$array[$i][$var];
                } else{
                    $toUpdate.="$var = ".$array[$i][$var].",";
                }
            }

            $vals.=",$id,'$updateCheck')";
            $query.=",$id_Col,update_ref)".$vals;
            $query.=$toUpdate;
                 
        // send query and catch if column doesn't exist -> adding column
            //$result = $this->_con->query($query);
        //$result = $conn->query($query);
        echo $query."<br>";
    }
    }
}
?>
