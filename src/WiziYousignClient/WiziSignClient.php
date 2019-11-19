<?php


namespace WiziYousignClient;


class WiziSignClient
{
    private $apikey;
    private $apiBaseUrl;
    private $idfile;

    public function __construct($apikey,$mode)
    {
        $this->setApikey($apikey);
        if(mode == 'prod'){
            $this->apiBaseUrl = 'https://api.yousign.com/';
        }else{
            $this->apiBaseUrl = 'https://staging-api.yousign.com/';
        }
    }

    public static function world()
    {
        return "Client pour l'api Yousign";
    }

    public function setApikey($apikey){
        $this->apikey = $apikey;
    }

    public function getApikey(){
        return $this->apikey;
    }

    public function getIdfile(){
        return $this->idfile;
    }

    public function setIdfile($idfile){
        $this->idfile = $idfile;
    }

    public function api_request( $post,$action,$method) {

        header('Content-Type: application/json'); // Specify the type of data
        $ch = curl_init($this->apiBaseUrl.$action); // Initialise cURL
        $post = json_encode($post); // Encode the data array into a JSON string
        $authorization = "Authorization: Bearer ".$this->getApikey(); // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        $err = curl_error($ch);
        curl_close($ch); // Close the cURL connection

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($result);
        }

         // Return the received data

    }

    public function getUsers(){
        $users = $this->api_request(array(),'users','GET');

        return $users;
    }

    public function newProcedure($filepath){
        $curl = curl_init();

        $data = file_get_contents($filepath);
        $b64Doc = base64_encode($data);

        $post = array(
            'name' => 'test.pdf',
            'content' => $b64Doc
        );
        $p = json_encode($post);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."files",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$p,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $rtab = json_decode($response,true);

        $this->idfile = $rtab['id'];
        return $this;

    }

    public function addMembersOnProcedure($members,$titresignature,$description){
        $post2 = array(
            'name' => $titresignature,
            'description' => $description,
            'members'=> $members
        );

        $p2 = json_encode($post2,true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $p2,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return $response;
    }

}