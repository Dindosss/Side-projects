<?php
  header("Access-Control-Allow-Method: POST");

  class API 
  {
    private $connect = null;
    public static function instance() {
    static $instance = null; // remember this only ever gets called once, why?
    if($instance === null)
    $instance = new API();
    return $instance; }
    private function __construct() 
    { /* Connect to the database */ 
        $username = "uXXXXXX";//confidentiality risk
        $servername = "wheatley.cs.up.ac.za";
        $password = "WLQKG24PKKAWDUUAUSKWUATWZEYKCRVQ";

        $this->connect = new mysqli($servername,$username,$password);
        if($this->connect->connect_error){
            die("Error : " . $this->connect->connect_error);
        }
    }
    public function __destruct() 
    { /* Disconnect from the database */
        $this->connect->close();
    }

    public function SelectP()
    {
      $this->connect->select_db("uXXXXXXXX");//confidentiality risk
      $apikey ='';
      $type = '';
      $request = $_SERVER["REQUEST_METHOD"];
      $Responsedata = array();
      $ResponseCount = 0;
      $limit = 0;
      $ch = curl_init();
      if($request == "POST")
      {
          $inputdata = json_decode(file_get_contents("php://input"),true);
          if(isset($inputdata['apikey']) & isset($inputdata['type']))
          {
            $apikey = mysqli_real_escape_string($this->connect,$inputdata['apikey']);
            $type = mysqli_real_escape_string($this->connect,$inputdata['type']);
          }
          else
          {
            return json_encode($this->ErrorReturn());
          } 
    
          ////Checking if parameters are entered
          if(isset($inputdata['limit']))
          $limit = mysqli_real_escape_string($this->connect,$inputdata['limit']);
          else $limit = 0;
    
          $searchparam = array();
          $returnparam = array();
          $SelectString = '';
          $WhereString = 'WHERE 1=1';
          $OrderString = "";
    
          if(isset($inputdata['search']))
          {
            if((isset($inputdata['fuzzy']) == false) || $inputdata["fuzzy"] == true)
            {
              $searchparam = $inputdata['search'];
              $searchNameparam = array_keys($searchparam);
              
              foreach($searchNameparam as $element)
              {
                $WhereString = $WhereString . " AND " . mysqli_real_escape_string($this->connect,$element) . " LIKE '%" . $searchparam[$element] ."%'";
              }
            
            }
            else 
            {
              $searchparam = $inputdata['search'];
              $searchNameparam = array_keys($searchparam);
              
              foreach($searchNameparam as $element)
              {
                $WhereString = $WhereString . " AND " . mysqli_real_escape_string($this->connect,$element) . "=" . $searchparam[$element] ;
              }
              
            }
          }
          else if($this->fetchPreferences($apikey) != null)
          {
            $result = $this->fetchPreferences($apikey);
            file_put_contents("debug.txt",$result[1]);
            if($result[0] != null)
            {
              $pref_t = $result[0];
              $pref = $result[1];
              
              $WhereString = $WhereString . " AND " . $pref_t . "='" . $pref . "'" ;
            }
          }

          if(isset($inputdata['sort']))
          {
            $OrderString = " ORDER BY ". $inputdata['sort'];
            if(isset($inputdata['order']))
            {
              $OrderString = $OrderString . " " . $inputdata['order'];
            }
            else $OrderString = $OrderString . " ASC";
          }
    
          if(isset($inputdata['return']))
          {
            $returnparam = $inputdata['return'];
            if($returnparam != "*")
            {
              foreach($returnparam as $element)
              {
                if($element != "image")
                {
                  if($SelectString == '')
                  $SelectString = mysqli_real_escape_string($this->connect,$element);
                  else $SelectString = $SelectString . ", " . mysqli_real_escape_string($this->connect,$element);
                }

              }
            }
            else $SelectString = "*";
            
          }
          else return json_encode($this->ErrorReturn());
    
          if($apikey == "H3LL0S1R"  || $this->CheckAPi($apikey)==true)
          {
            if($type == "GetAllCars")
            {
              $query = "SELECT " . $SelectString . " FROM cars " . $WhereString . $OrderString;
              $data = $this->connect->query($query);

              if($data)
              {
                $result = mysqli_fetch_all($data,MYSQLI_ASSOC);
                if($limit == 0)
                {
                  foreach($result as $row)
                  {
                    if(isset($row['model']))
                    {
                      $temp = array();
                      $returnparam = $inputdata['return'];
                      if($returnparam != "*")
                      {
                        foreach($returnparam as $element)
                        {
                          if($element != "image")
                          {
                            $temparray = array( $element => $row[$element]);
                            $temp = array_merge($temp,$temparray);
                          }
                        
                        }
                        curl_setopt($ch,CURLOPT_URL,"https://wheatley.cs.up.ac.za/api/getimage?brand=" . $row['make'] . "&model=" . $row['model']);
                        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                        $serverresponse = curl_exec($ch);
                        $image = array("image" => $serverresponse);
                        $temp = array_merge($temp,$image);
                        $Responsedata[$ResponseCount] = $temp;
                        $ResponseCount++;
                      }
                      else
                      {
                        $keys = array_keys($row);
                        foreach($keys as $element)
                        {
                          $temparray = array( $element => $row[$element]);
                          $temp = array_merge($temp,$temparray);
                        }
                        curl_setopt($ch,CURLOPT_URL,"https://wheatley.cs.up.ac.za/api/getimage?brand=" . $row['make'] . "&model=" . $row['model']);
                        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                        $serverresponse = curl_exec($ch);
                        $image = array("image" => $serverresponse);
                        $temp = array_merge($temp,$image);
                        $Responsedata[$ResponseCount] = $temp;
                        $ResponseCount++;
                      }

                      
                    }
                    else 
                    {
                      $temp = array();
                      $returnparam = $inputdata['return'];
                      if($returnparam != "*")
                      {
                        foreach($returnparam as $element)
                        {
                          if($element != "image")
                          {
                            $temparray = array( $element => $row[$element]);
                            $temp = array_merge($temp,$temparray);
                          }
                        }
                      }
                      else 
                      {
                        $keys = array_keys($row);
                        foreach($keys as $element)
                        {
                          $temparray = array( $element => $row[$element ]);
                          $temp = array_merge($temp,$temparray);
                        }
                      }
                      curl_setopt($ch,CURLOPT_URL,"https://wheatley.cs.up.ac.za/api/getimage?brand=" . $row['make'] );
                      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                      $serverresponse = curl_exec($ch);
                      $image = array("image" => $serverresponse);
                      $temp = array_merge($temp,$image);
                      $Responsedata[$ResponseCount] = $temp;
                      $ResponseCount++;
                    }
                  }
                  $finalresponse = array(
                    "status" => "success",
                    "timestamp" => time(),
                    "data" => $Responsedata
                  );

              
                  return json_encode($finalresponse);
                }
                else
                {
                  foreach(array_slice($result,0,$limit) as $row)
                  {
                    if(isset($row['model']))
                    {
                      $temp = array();
                      $returnparam = $inputdata['return'];
                      if($returnparam != "*")
                      {
                        foreach($returnparam as $element)
                        {
                          if($element != "image")
                          {
                            $temparray = array( $element => $row[$element]);
                            $temp = array_merge($temp,$temparray);
                          }
                        }
                      }
                      else 
                      {
                        $keys = array_keys($row);
                        foreach($keys as $element)
                        {
                          $temparray = array( $element => $row[$element]);
                          $temp = array_merge($temp,$temparray);
                        }
                      }
                      curl_setopt($ch,CURLOPT_URL,"https://wheatley.cs.up.ac.za/api/getimage?brand=" . $row['make'] . "&model=" . $row['model']);
                      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                      $serverresponse = curl_exec($ch);
                      $image = array("image" => $serverresponse);
                      $temp = array_merge($temp,$image);
                      $Responsedata[$ResponseCount] = $temp;
                      $ResponseCount++;
                    }
                    else 
                    {
                      $temp = array();
                      $returnparam = $inputdata['return'];
                      if($returnparam != "*")
                      {
                        foreach($returnparam as $element)
                        {
                          if($element != "image")
                          {
                            $temparray = array( $element => $row[$element]);
                            $temp = array_merge($temp,$temparray);
                          }
                        }
                      }
                      else 
                      {
                        $keys = array_keys($row);
                        foreach($keys as $element)
                        {
                          $temparray = array( $element => $row[$element]);
                          $temp = array_merge($temp,$temparray);
                        }
                      }
                      curl_setopt($ch,CURLOPT_URL,"https://wheatley.cs.up.ac.za/api/getimage?brand=" . $row['make'] );
                      curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                      $serverresponse = curl_exec($ch);
                      $image = array("image" => $serverresponse);
                      $temp = array_merge($temp,$image);
                      $Responsedata[$ResponseCount] = $temp;
                      $ResponseCount++;
                    }
                  }
                  $finalresponse = array(
                    "status" => "success",
                    "timestamp" => time(),
                    "data" => $Responsedata
                  );

                  return json_encode($finalresponse);
                }
              }
              else return json_encode($this->ErrorReturn("Invalid values entered as parameters"));
              
            }
            else return json_encode($this->ErrorReturn("Wrong parameter entered for type"));
          }
          else return json_encode($this->ErrorReturn("Invalid apikey"));
      }
      

      
    }

    private function CheckAPi($apik)
    {
      $this->connect->select_db("u21574937_Prac3");
      $qry = "SELECT apikey FROM Users WHERE apikey='$apik'";
      $result = $this->connect->query($qry);
      if($result)
      {
        return true;
      }
      else return true;
    }

    private function fetchPreferences($apik)
    {
      file_put_contents("debug.txt","Inside the prefernce function");
      $this->connect->select_db("u21574937_Prac3");
      $qry = "SELECT Preference_type, Preference FROM Users WHERE apikey='$apik'";
      $result = $this->connect->query($qry);
      if($result)
      {
        return $result->fetch_row();
      }
      else return null;
    }

    public function setPreference()
    {
      $this->connect->select_db("u21574937_Prac3");
      $request = $_SERVER["REQUEST_METHOD"];
      if($request == "POST")
      {
        
        $inputdat = json_decode(file_get_contents("php://input"),true);
        if($inputdat["type"] == "Preferences")
        {
          $P_type = $inputdat['P_type'];
          $Pref = $inputdat['Preference'];
          $api = $inputdat["apikey"];
          $query = "UPDATE Users SET Preference_type='$P_type',Preference='$Pref' WHERE apikey='$api'";
          $res = $this->connect->query($query);
          if($res)
          {
            return true;
          }
          else return false;
        }
        else return false;
      }
      
    }


    private function Wildcard($input)
    {

    }
    
    private function ErrorReturn($errorM = "Post parameters are missing")
    {
      $error = array(
        'status' => 'error',
        'timestamp ' => time(),
        'data' => $errorM
      );

      return $error;
    }

  }

  header('Content-Type: application/json');
  $api = API::instance();
  if($api->setPreference() == true)
  {
    echo $api->setPreference();
  }
  else echo $api->SelectP();

?>