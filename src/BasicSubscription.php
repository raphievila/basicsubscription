<?php
namespace BasicSubscription;
/*
 * A basic unofficial php component to subscribe, unsubscribe and check status
 * to mailing lists using CURL on Mailgun API. Use only if you have conflicts with
 * the official Mailgun PHP SDK.
 * 
 * For information on api request results go to:
 * https://documentation.mailgun.com/en/latest/api-mailinglists.html#examples
 * 
 */
/*
 * Author       : Rafael Vilá
 * Organization : Revolution Visual Arts
 * Version      : 1.0.0
 * Created by   : Rafael Vilá
 * Created On   : Jun 29, 2017
 * Modified On  :
 * Modified By  :
 */

class BasicSubscription {
    static private $pubkey;
    static private $secret;
    static private $domain;
    static private $api;
    static private $user;
    static private $pass;
    static private $ini;
    
    function __construct() {
        $ini = parse_ini_file("config/mg.ini", TRUE);
        $json = json_encode($ini);
        self::$ini = json_decode($json);
        self::$pubkey = self::$ini->keys->public_key;
        self::$secret = self::$ini->keys->secret_key;
        self::$domain = self::$ini->http->domain;
        self::$api = self::$ini->http->api;
        self::$user = self::$ini->api->user;
        self::$pass = self::$ini->api->password;
    }
    
    static private function SHOW_DATA() {
        return self::$ini;
    }
    
    static private function PREPARING_CURL_POST($cmd, $post) {
        $url = self::$api . $cmd;
        //return array($url, $post);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "api:" . self::$secret);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, TRUE); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

        $result = curl_exec($curl);
        curl_close($curl);
        
        return $result;
    }
    
    static private function PREPARING_CURL_GET($cmd, $post = FALSE, $useSecret = FALSE) {
        $postData = is_object($post);
        $getRequest = "";
        $key = ($useSecret) ? self::$secret : self::$pubkey;
        
        if($postData){
            foreach($post as $k => $v) {
                $gets[] = "$k=" . urlencode($v);
            }
            
            $getRequest = "?" . join("&", $gets);
        }
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::$api . $cmd . $getRequest);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "api:" . $key);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, FALSE); 

        $result = curl_exec($curl);
        curl_close($curl);
        
        return $result;
    }
    
    static private function CHECK_SUBSCRIBER($email, $list, $secret) {
        $cmd = "lists/$list@" . self::$domain . "/members/" . $email;
        
        return self::PREPARING_CURL_GET($cmd, FALSE, $secret);
    }
    
    static private function SUBSCRIBE_TO_PLAYLIST($args = FALSE) {
        if(!$args || !is_object($args)){ return FALSE; }
        $argList = array('email', 'name');
        
        foreach($argList as $arg) {
            if(!isset($args->{$arg})){ throw new Exception("Required $arg argument is undefined", 400); }
        }
        
        if(!isset($args->subscribed)) { $args->subscribed = "yes"; }
        if(!isset($args->listname)) { $args->listname = "newsletter"; }
        
        $data = array(
            "subscribed"  => "$args->subscribed",
            "address" => "$args->email",
            "name" => "$args->name",
            "upsert" => "yes"
        );
        
        if(isset($args->vars)) {
            $data['vars'] = json_encode($args->vars);
        }

        $result = self::PREPARING_CURL_POST("lists/$args->listname@" . self::$domain . "/members", $data);
        
        
        return $result;
    }
    
    /*
     * VERIFY EMAIL STRUCTURE, USING MAILGUN API
     * $email = (string) email to verify
     *
     * the mailgun api will verify if the email is a valid email
     * will return a json with result.
     */
    static public function verify_email($email) {
        $cmd = "address/validate";
        $post = array(
            "address" => $email
        );
        
        $result = self::PREPARING_CURL_GET($cmd, $post);
        
        return $result;
    }
    
    /*
     * SEND SUBSCRIPTION REQUEST TO MAILGUN
     * $args (object) requires to be an object not an array.
     * 
     * SET AS
     * $args = new stdClass(); || $args = (object) array();
     * required methods: email, name.
     * 
     * $args->email = (string) well constructed email,
     * ---> to verify inside a php you can use method verify_email_object
     * $args->name = (string) subscriber full name
     * 
     * OPTIONAL
     * $args->subscribed = (boolean) true default. wheter subscribe or unsubscribe
     * 
     * $args->vars = (array) if you need to store additional information
     * send it on vars in a key => value struturec array. IF sent through
     * ajax, send information as a encodeURI(json). In the receiver, you then
     * need to htmlspecialchars_decode(urldecode(json));
     * 
     * this code do not filter any value, you need to take actions to filter
     * them.
     * 
     * return JSON as string, received directly from MAILGUN api
     */
    static public function subscribe_me($args) {
        return self::SUBSCRIBE_TO_PLAYLIST($args);
    }
    
    /*
     * TO CHECK STATUS OF AN PROVIDED EMAIL AGAINST A MAILING LIST
     * $email (string) a well constructed email string verify before sending
     * $list (string) the name of the mailing list for example:
     * if the mailing list is news@mg.example.com, just send news, the full
     * email style address is preconfigured by the code.
     */
    static public function check_my_status($email, $list) {
        return self::CHECK_SUBSCRIBER($email, $list, TRUE);
    }

    /* SAME AS verify_email METHOD, BUT RESULTS ARE RETURN AS MAP
     * return object
     */
    static public function verify_email_object($email) {
        $result = self::verify_email($email);
        return json_decode($result);
    }

    static public function subscribe_me_object($args) {
        $args->subscribed = TRUE;
        $result = self::subscribe_me($args);
        return json_decode($result);
    }

    static public function unsubscribe_me_object($args) {
        $args->subscribed = FALSE;
        $result = self::subscribe_me($args);
        return json_decode($result);
    }
}