<?php

namespace Contabilium;

class CbApi
{

    public function getAuth($client_id, $client_secret)
    {
        $headers = ['Content-Type:application/x-www-form-urlencoded','Accept:application/json'];
        $ch = curl_init();
        $url = 'https://rest.contabilium.com/token';
        $post = "grant_type=client_credentials&client_id=".$client_id."&client_secret=" . $client_secret;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);
        $obj = json_decode($result);
        if (empty($obj->access_token)) {
        	return null;
        } else {
	        return  $this->token = $obj->access_token;
        }
    }

    public static function getOutput($string)
    {
    	echo '<pre>';
    	var_dump($string);
    	echo '</pre>';
    }

    public static function toTextbox( $string )
    {
        echo '<textarea style="widht:100%" rows="20">';
        print_r($string);
        echo '</textarea>';
    }

    public static function getRequest($data)
    {

        $authorization = "Authorization: Bearer " . $data["token"];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $data['url']);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    return json_decode($result);

    }

    public static function postRequest($data)
    {
        $authorization = "Authorization: Bearer " . $data["token"];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $data['url']);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['data']));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    return json_decode($result);
    }

    public static function putRequest($data)
    {

	    $authorization = "Authorization: Bearer ". $data["token"];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $data['url']);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['data']));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    return json_decode($result);

    }

    public static function  deleteRequest($data)
    {
        $authorization = "Authorization: Bearer " . $data["token"];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $data['url']);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json' , $authorization ));
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $result = curl_exec($ch);
	    return json_decode($result);
    }

}




