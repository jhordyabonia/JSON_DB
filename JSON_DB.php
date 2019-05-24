<?php
class JSON_DB{
    private $titles = null;
    private $unique = null;
    private $database = null;
    private $fileName = null;
    public function __construct($fileName = "",$titles = "",$unique = ""){ 
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
    public function insert($data){
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
                foreach($diff as $k=>$d){
                    if(array_key_exists($k,$this->titles)){
                        throw new Exception("field '$k' is requiered");
                        return false;
                    }
                }
            }
        }
        $this->database->data[]=$data;
        $this->database->rows++;
        return true;
    }
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
    public function getAll(){
        return $this->database->data;
    }
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
    public function updateLine($pos,$data){
        foreach($data as $k=>$v){
            $this->database->data[$pos]->{$k} = $v;
        }
    }
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
    public function commit(){
        $fh = fopen($this->fileName, 'w');
        $data = json_encode($this->database,JSON_PRETTY_PRINT);
        fwrite($fh, $data);
        fclose($fh);
    }
}
$db = new JSON_DB("file","id,name,opacity","id");
/*$db->insert(['id'=>1,'name'=>'none0']);
$db->insert(['id'=>2,'name'=>'none1']);
$db->insert(['id'=>3,'name'=>'none2']);
$db->insert(['id'=>4,'name'=>'test0']);
$db->insert(['id'=>5,'name'=>'test1']);
$db->insert(['id'=>6,'name'=>'test2']);*/
$db->insert(['id'=>7,'color'=>'#FF0000','name'=>'red']);
$db->update(['id'=>7],['color'=>'#0000FF','name'=>'blue']);
$db->updateLine(6,['color'=>'#00FF00','name'=>'green','opacity'=>0.1]);

/*$db->remove(['id'=>'2'])."\n";
$db->remove(['name'=>'none0'])."\n";
$db->remove(['name'=>'none'],true)."\n";
$db->remove(['name'=>'test'],true)."\n";*/

$db->commit();

//print_r($db->getAll());
print_r($db->get(['id'=>7]));
?>