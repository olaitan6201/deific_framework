<?php
namespace Controllers;

use PDOException;

class Crud{
    private $db;
    var $error;
    
    public function __construct()
    {
        $this->db = db();
    }

    // Insert Data to Database
    public function insert(String $table, String $columns)
    {
        $cols = explode(',', $columns);
        $req = $_REQUEST;
        $data = [];
        $len = count($cols) - 1;
        $stmt = "INSERT into $table set ";
        
        foreach($cols as $key => $col){
            $col = trim($col);

            $data[$col] = isset($req[$col])?$req[$col]:'';

            if($key == $len)
            $stmt .= $col . ' = :' . $col;
            
            else
            $stmt .= $col . ' = :' . $col . ', ';
        }

        $this->db->data = $data;

        $this->db->query = $stmt;
            
        try{
            $this->db->execute();

            return response()->custom(1);
        }
        catch(PDOException $e){
            $this->error = $e->getMessage();
            return response()->custom(0, $this->error);
        }
    }

    //Update Data in Database
    public function updateData(String $tblName='', String $cols='', Array $values = [], String $refId='', String $refVal='')
    {
        $cols = explode(', ', $cols);

        if(!is_array($values)){
            $res = array(
                'status'    =>  0,
                'message'   =>  'Values are expected as an array params'
            );
        }
        elseif(count($cols) !== count($values))
        {
            $res = array(
                'status'    =>  0,
                'message'   =>  'Number of Data and values supplied does not match'
            );
        }else{
            $stmt = "UPDATE ".$tblName." set ";

            $data = array();
            
            for($i=0; $i<count($cols); $i++)
            {
                $data[':data'.$i] = $values[$i];
            }
            
            $this->db->data = $data;
            // exit(print_r($this->db->data));
            
            for($i=0; $i<count($cols); $i++)
            {
                if($i < (count($cols)-1)) :
                    $stmt.= $cols[$i]." = :data".$i.", ";
                else :
                    $stmt.= $cols[$i]." = :data".$i;
                endif;
            }

            $stmt.= ' where '.$refId.' = "'.$refVal.'"';
            // exit($stmt);
            $this->db->query = $stmt;

            if($this->db->execute())
            {
                $res = array(
                    'status'    =>  1,
                    'message'   =>  'Data updated successfully'
                );   
            }else{
                $res = array(
                    'status'    =>  0,
                    'message'   =>  'Unable to update data, check your connection'
                );
            }
        }

        return $res;   
    }

    ///Fetch Data From Database
    public function fetchData(
        String $type='single', String $tblName='', 
        ?String $joins='', ?String $tblRef='', ?String $joinsRef='', ?String $joinsRefType='', 
        ?String $fId='', $fVal, 
        ?String $orderRef='', ?String $orderType='DESC', $lim = 0
    ){
        $res = '';
        if(strpos($tblRef, ',')){
            $res = array(
                'status'    =>  0,
                'message'   =>  'Table join ref expects one param'
            );
        }
        elseif(
            ($joinsRefType == 'single' AND strpos(',', $joinsRef) == true) 
            OR 
            ($joinsRefType == 'multi' AND strpos(',', $joinsRef) == false)
        ){
            $res = array(
                'status'    =>  0,
                'message'   =>  'Joins params does not match with type'
            );
        }else{
            $stmt = 'SELECT * from '.$tblName.' ';
            
            if(!empty($joins)){
                $joins = explode(', ', $joins);

                if($joinsRefType == 'single')
                {
                    for($i=0; $i<=(count($joins)-1); $i++)
                    {
                        $stmt.= 'INNER JOIN '.$joins[$i].' on '.$tblName.'.'.$tblRef.' = '.$joins[$i].'.'.$joinsRef.' ';
                    }
                }
                elseif($joinsRefType == 'multi')
                {
                    $joinsRef = explode(', ', $joinsRef);

                    for($i=0; $i<=(count($joins)-1); $i++)
                    {
                        $stmt.= 'INNER JOIN '.$joins[$i].' on '.$tblName.'.'.$tblRef.' = '.$joins[$i].'.'.$joinsRef.' ';
                    }
                }
            }

            if(!empty($fId)) $stmt.=' WHERE '.$tblName.'.'.$fId.'= "'.$fVal.'" ';

            if(!empty($orderRef)) $stmt.='ORDER BY '.$tblName.'.'.$orderRef.' '.$orderType.' ';

            if(!empty($lim)) $stmt.='LIMIT '.$lim;

        }

        // exit($stmt);
        $this->db->query = $stmt;
        if(empty($res)) :
            if($type == 'single') : return $this->db->fetch();
            elseif($type == 'multi') : return $this->db->fetchAll();
            else : return ['status'=>0, 'message'=>'Fetch type can only be single or multi'];
            endif;
        else :
            return $res;
        endif;
    }



    ///Check Data in Database
    public function checkData($tblName='', $checkIds='', $checkVals=[], ?String $checkOpera = '=', ?String $checkDelim = 'OR')
    {

        $checkIds = explode(', ', $checkIds);
        
        $res = [];

        if(!is_array($checkVals)){
            $res = array(
                'status'    =>  0,
                'message'   =>  'Check Values are expected as an array params'
            );
        }
        elseif(count($checkIds) !== count($checkVals))
        {
            $res = array(
                'status'    =>  0,
                'message'   =>  'Number of Check IDs and Check Values supplied does not match'
            );
        }else{
            $stmt = 'SELECT * from '.$tblName.' where ';

            for($i=0; $i<(count($checkIds)); $i++)
            {
                if($i === 0)
                {
                    $stmt .= $checkIds[$i].' '.$checkOpera.' "'.$checkVals[$i].'" ';
                }else{
                    $stmt .= $checkDelim.' '.$checkIds[$i].' '.$checkOpera.' "'.$checkVals[$i].'" ';
                }
            }

            $this->db->query = $stmt;

            if($this->db->rowCount() > 0)
            {
                $res = array(
                    'status'    =>  0,
                    'message'   =>  'Data already exist'
                );
            }else{
                $res = array(
                    'status'    =>  1,
                    'message'   =>  'Data not found'
                );
            }
        }

        return $res;
    }

    public function uploadFile($ref, $new_name, $target, $mimes='', $maxSize)
    {
        if(!empty($ref['name']))
        {
            $mimes = explode(', ', $mimes);

            $extension = pathinfo($ref['name'], PATHINFO_EXTENSION);

            $isType = false;
            $isSize = false;

            $fileSize = trim(str_replace('mb', '', strtolower($maxSize)));
            
            if(in_array($extension, $mimes)){
                $isType = true;
            }

            if(!strpos(strtolower($maxSize), 'mb')){
                $res = array(
                    'status'    =>  0,
                    'message'   =>  'Max file size is expected in MB'
                );
            }
            elseif($ref['size'] > (intval($fileSize)*1024*1024)){
                $res = array(
                    'status'    =>  0,
                    'message'   =>  'Invalid file size'
                );                    
            }
            elseif(!$isType)
            {
                $res = array(
                    'status'    =>  0,
                    'message'   =>  'Invalid file format'
                );
            }
            else{
                $new_name = $new_name . '.' . $extension;

                $_source_path = $ref['tmp_name'];

                $target_path = $target . $new_name;

                move_uploaded_file($_source_path, $target_path);
                
                $res = $new_name;
            }
        }else{
            $res = '';
        }

        return $res;
    }


    public function fetchDataSum($tblName='', $ref='', $fId='', $fVal, $fOpera)
    {
        $stmt = 'SELECT sum('.$ref.') as data_sum from '.$tblName.' ';

        if(!empty($fId)) $stmt.='where '.$fId.' '.$fOpera.' "'.$fVal.'"';


        $this->db->query = $stmt;

        return $this->db->fetch()->data_sum;
    }

    public function fetchDataCount($tblName='', ?String $ref='*', ?String $fId='', ?String $fVal='', ?String $fOpera = '=')
    {
        $stmt = 'SELECT count('.$ref.') as data_count from '.$tblName.' ';

        if(!empty($fId)) $stmt.='where '.$fId.' '.$fOpera.' "'.$fVal.'"';

        $this->db->query = $stmt;

        return $this->db->fetch()->data_count;
    }


    function deleteData($tblName='', $fIds='', $fVals='', $fOperas='', $fDelims='')
    {
        if(!is_string($fIds))
        {
            $res = array(
                'status'    =>  0,
                'message'   => 'Table reference ids are expected as a string'
            );
        }
        elseif(!is_string($fVals))
        {
            $res = array(
                'status'    =>  0,
                'message'   => 'Table reference values are expected as an array'
            );
        }
        else{
            $ref = explode(', ', $fIds);
            $fVals = explode(', ', $fVals);
            $opera = explode(', ', $fOperas);
            $delim = explode(', ', $fDelims);

            if(count($ref) !== count($fVals))
            {
                $res = array(
                    'status'    =>  0,
                    'message'   => 'Number of Table reference ids and values does not match'
                );
            }else{
                $stmt = 'DELETE from '.$tblName.' where ';

                for($i=0; $i<=(count($ref)-1); $i++)
                {
                    if($i < (count($ref)-1))
                    {
                        if(count($opera) == 1)
                        {
                            $stmt .= $ref[$i].' '.$opera[0].' "'.$fVals[$i].'" '.$delim[0].' ';
                        }else{
                            $stmt .= $ref[$i].' '.$opera[$i].' "'.$fVals[$i].'" '.$delim[$i].' ';
                        }
                    }else{
                        if(count($opera) == 1)
                        {
                            $stmt .= $ref[$i].' '.$opera[0].' "'.$fVals[$i].'"';
                        }else{
                            $stmt .= $ref[$i].' '.$opera[$i].' "'.$fVals[$i].'"';
                        }
                    }
                }
                // exit($stmt);
                $this->db->query = $stmt;

                if($this->db->execute()){
                    $res = array(
                        'status'    =>  1,
                        'message'   =>  'Data deleted successfully'
                    );
                }else{
                    $res = array(
                        'status'    =>  0,
                        'message'   =>  'Unable to delete selected data'
                    );
                }
            }

        }
        return $res;
    }


    function emptyTable(string $tblRef)
    {
        $this->db->query = "TRUNCATE table ".$tblRef;

        if($this->db->execute()){
            $res = array(
                'status'    =>  1,
                'message'   =>  'Table cleared successfully'
            );
        }else{
            $res = array(
                'status'    =>  0,
                'message'   =>  'Unable to clear table data'
            );
        }

        return $res;
    }
}