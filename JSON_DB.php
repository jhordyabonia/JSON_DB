<?php

/**
 *  @class JSON_DB
 *  @description:  Interface to storage  and maneger the data a file format *.json
 *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
 */

class JSON_DB{
    /* array $titles: list of titles for fields required*/
    private $titles = null;

    /* string $unique: column name with unique value*/
    private $unique = null;

    /**
     * Object $database: 
     * struct{
     *      "name": "file",
     *       "data": [],
     *      "rows": 0
     *   }
     */
    private $database = null;
    /* string $fileName: path to file if is empty, assign random name*/

    private $fileName = null;

    /**
     *  @method __contruct
     *  @param string $fileName: path to file if is empty, assign random name
     *  @param string $titles: list of titles for fields required separate by commas
     *  @param string $unique: column name with unique value
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function __construct(String $fileName = "",String $titles = "",String $unique = ""){ 
        $this->unique = $unique;
        if(empty($fileName))
            $fileName = "json_db-".rand();
        if(!empty($titles))
            $this->titles = explode(",",$titles);
        $this->fileName = "{$fileName}.json";
        $raw = null;
        if(file_exists($this->fileName)){
            $raw = file_get_contents($this->fileName);
        }        
        $this->database = json_decode($raw);
        if(!$this->database){
            $this->database = new StdClass();
            $this->database->name = $fileName;
            $this->database->data = [];
            $this->database->rows = 0;
            $this->commit();
        }else{
            $this->database->data = (Array) $this->database->data;
        }
    }

    /**
     *  @method insert: intert data to database
     *  @param iterable $data: path to file if is empty, assign random name
     *  @return boolean TRUE if the data was inserted else FALSE
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function insert(iterable $data){
        $out = false;
        if($this->unique){
            $unique = [$this->unique => $data[$this->unique]];
            if(!empty($this->get($unique)))
                return false;
        }
        if($this->database->rows!=0){
            if(!$this->titles)
                $this->titles = (Array)current($this->database->data);  
            $diff = array_diff_key($this->titles,(Array)$data); 
            if($diff){
                foreach($diff as $d){
                    if(array_key_exists($d,$this->titles)){
                        throw new Exception("field '$d' is requiered");
                        return false;
                    }
                }
            }
        }
        $this->database->data[]=$data;
        $this->database->rows++;
        return true;
    }

    /**
     *  @method get find and return list of dataset that supply all the conditions required
     *  @param iterable $filter: fields to find and coincidence
     *  @param boolean $active: if value is true it work like statement SQL LIKE with  symbols %%
     *  @return array list of dataset that supply all the conditions required
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function get($filter,$active = false){
        $out = [];
        foreach($this->database->data as $data){
            foreach($data as $kd=>$d){
                foreach($filter as $kf=>$f){
                    if($active){
                        if($kf == $kd && is_int(strpos("$d","$f"))){
                            $out[] = $data;
                        }
                    }else{
                        if($kf == $kd && $d == $f){
                            $out[] = $data;
                        }
                    }
                }
            }
        }
        return $out;
    }

    /**
     *  @method getAll 
     *  @param iterable $filter: fields to find and coincidence
     *  @return array: All list of dataset that is in database
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function getAll(){
        return $this->database->data;
    }

    /**
     *  @method remove: delete dataset that supply all the conditions required
     *  @param iterable $filter: fields to find and coincidence
     *  @param boolean $active: if value is true it work like statement SQL LIKE with  symbols %%
     *  @return int: number of rows deleted
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function remove($filter,$active=false){
        $out = 0;
        foreach($this->database->data as $id=>$data){
            foreach($data as $kd=>$d){
                foreach($filter as $kf=>$f){
                    if($active){
                        if($kf == $kd && is_int(strpos("$d","$f"))){
                            unset($this->database->data[$id]);
                            $out++;
                        }
                    }else{
                        if($kf == $kd && $d==$f){
                            unset($this->database->data[$id]);
                            $out++;
                        }
                    }
                }
            }
        }
        $this->database->rows-=$out;
        return $out;
    }

    /**
     *  @method updateLine: update the fields on row $pos
     *  @param int $pos: row number to update
     *  @param iterble $data: $data to insert/update
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function updateLine($pos,$data){
        foreach($data as $k=>$v){
            $this->database->data[$pos]->{$k} = $v;
        }
    }

    /**
     *  @method update 
     *  @param iterable $where: fields to find and coincidence
     *  @param iterable $new_data: data to insert/update
     *  @param boolean $active: if value is true it work like statement SQL LIKE with  symbols %%
     *  @return int: returns the number of affected rows
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function update($where,$new_data,$active=false){
        $out = 0;
        foreach($this->database->data as $id=>$data){
            foreach($data as $kd=>$d){
                foreach($where as $kf=>$f){
                    if($active){
                        if($kf == $kd && is_int(strpos("$d","$f"))){
                            $this->updateLine($id,$new_data);
                            $out++;
                        }
                    }else{
                        if($kf == $kd && $d == $f){
                            $this->updateLine($id,$new_data);
                            $out++;
                        }
                    }
                }
            }
        }
        return $out;
    }

    /**
     *  @method update 
     *  @return int: returns the number of storaged rows
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function rows(){
        return $this->database->rows;
    }


    /**
     *  @method commit: to do permanent the changes
     *  @author Jhordy Abnia G. <jhordy.abonia@gmail.com>
     */
    public function commit(){
        $fh = fopen($this->fileName, 'w');
        $data = json_encode($this->database,JSON_PRETTY_PRINT);
        fwrite($fh, $data);
        fclose($fh);
    }
}