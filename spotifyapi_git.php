<?php
header('Access-Control-Allow-Method: GET');

class API
{
    private $connect = null;
    public static function instance()
    {
        static $instance = null; // remember this only ever gets called once, why?
        if ($instance === null)
            $instance = new API();
        return $instance;
    }

    private function __construct()
    {
        $clientID = "XXXXXXXXXXXXXXXXXXXXXXXXXXXX"; //confidentiality risk
        $secretC = "XXXXXXXXXXXXXXXXXXXXXXXXXXXXX"; //confidentiality risk
        $auth = "grant_type=client_credentials&client_id=" . $clientID . "&client_secret=" . $secretC;
        $hd = "Content-Type: application/x-www-form-urlencoded";
        $url = "https://accounts.spotify.com/api/token";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', $auth));
        curl_setopt($curl, CURLOPT_POST, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $auth);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($curl);
        if ($e = curl_error($curl)) {
            echo $e;
        } else {
            $decode = json_decode($resp);
            $accesT = $decode->access_token;
            $rdmn = "ABCDEFGHIJKLMNOPQRSTUV";
            $rdmCh = substr($rdmn, rand(0, 23), 1);
            $searchurl = 'https://api.spotify.com/v1/search?q=' . $rdmCh . '%25track&type=track';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accesT));
            curl_setopt($ch, CURLOPT_URL, $searchurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $rsp = curl_exec($ch);
            if ($e = curl_error($ch)) {
                echo $e;
            } else {
                $tracks = json_decode($rsp);
                $items = $tracks->tracks->items;
                $name = $items[0];
                echo json_encode($name->name);
            }
        }
    }
}

header('Content-Type: application/json');
$api = API::instance();
