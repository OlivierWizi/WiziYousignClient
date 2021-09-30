<?php


namespace WiziYousignClient;


class WiziSignClient
{
    private $apikey;
    private $apiBaseUrl;
    private $apiBaseUrlWslash;
    private $idfile;
    private $idAdvProc;
    private $member;
    private $fileobject;

    /**
     * WiziSignClient constructor.
     * @param $apikey
     * @param $mode
     */
    public function __construct($apikey,$mode)
    {
        $this->setApikey($apikey);
        if($mode == 'prod'){
            $this->apiBaseUrl = 'https://api.yousign.com/';
            $this->apiBaseUrlWslash = 'https://api.yousign.com';
        }else{
            $this->apiBaseUrl = 'https://staging-api.yousign.com/';
            $this->apiBaseUrlWslash = 'https://staging-api.yousign.com';
        }
    }

    /**
     * @return string
     */
    public static function world()
    {
        return "Client pour l'api Yousign";
    }

    /**
     * @param $apikey
     */
    public function setApikey($apikey){
        $this->apikey = $apikey;
    }

    /**
     * @return mixed
     */
    public function getApikey(){
        return $this->apikey;
    }

    /**
     * @param $apikey
     */
    public function setMember($member){
        $this->member = $member;
    }

    /**
     * @return mixed
     */
    public function getMember(){
        return $this->member;
    }

    /**
     * @return mixed
     */
    public function getIdfile(){
        return $this->idfile;
    }

    /**
     * @param $idfile
     */
    public function setIdfile($idfile){
        $this->idfile = $idfile;
    }

    /**
     * permet de recup le fichier signé sur yousign
     * @param $fileid
     * @param $mode
     * @return bool|string
     */
    public function downloadSignedFile($fileid,$mode){
        $curl = curl_init();
        if($mode == 'binary'){
            $urlstr =  $this->apiBaseUrlWslash.$fileid."/download?alt=media";
        }else{
            $urlstr =  $this->apiBaseUrlWslash.$fileid."/download";
        }


        curl_setopt_array($curl, array(
            CURLOPT_URL => $urlstr,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * @param $post
     * @param $action
     * @param $method
     * @return mixed|string
     */
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

    /**
     * @return mixed|string
     */
    public function getUsers(){
        $users = $this->api_request(array(),'users','GET');

        return $users;
    }

    /**
     * @param $filepath
     * @return $this
     */
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

    /**
     * @param $members
     * @param $titresignature
     * @param $description
     * @return bool|string
     */
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

    /**
     * @param $parameters
     * @param bool $notifmail
     * @param bool $webhook
     * @param string $webhookMethod
     * @param string $webhookUrl
     * @param string $webhookHeader
     * @return bool|string
     */
    public function AdvancedProcedureCreate($parameters,$webhook = false,$webhookMethod = '',$webhookUrl = '',$webhookHeader = ''){
        /*
         *
            {
                "name": "My procedure",
                "description": "Description of my procedure with advanced mode",
                "start" : false
            }
         */
        $conf = array();

        if($webhook == true){
            $conf["webhook"] = array(
                "member.finished" => array(
                    array(
                        "url" => $webhookUrl,
                        "method" => $webhookMethod,
                        "headers" => array(
                            "X-Custom-Header" => $webhookHeader
                        )
                    )
                ),
                "member.started" => array(
                    array(
                        "url" => $webhookUrl,
                        "method" => $webhookMethod,
                        "headers" => array(
                            "X-Custom-Header" => $webhookHeader
                        )
                    )
                )
            );
            $parameters['config'] = $conf;
        }

        $curl = curl_init();

        $params = json_encode($parameters,true);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $rtab = json_decode($response,true);


            $this->idAdvProc = $rtab['id'];
            return $response;
        }
    }

    /**
     * @param $filepath
     * @param $namefile
     * @return bool|string
     */
    public function AdvancedProcedureAddFile($filepathOrFileContent,$namefile,$filecontent = false){

        /*
         {
            "name": "Name of my signable file.pdf",
            "content": "JVBERi0xLjUKJb/3ov4KNiA [...] VPRgo=",
            "procedure": "/procedures/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
         */

        if($filecontent == false){
            $data = file_get_contents($filepathOrFileContent);
            $b64Doc = base64_encode($data);
        }else{
            $b64Doc = base64_encode($filepathOrFileContent);
        }


        $parameters = array(
            'name' => $namefile,
            'content' => $b64Doc,
            'procedure' => $this->idAdvProc
        );


        $curl = curl_init();
        $params = json_encode($parameters,true);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."files",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $rtab = json_decode($response,true);
            $this->idfile = $rtab['id'];
            return $response;
        }
    }

    /**
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $phone
     * @param $otp Receive Security Code by email or sms
     * @return bool|string
     */
    public function AdvancedProcedureAddMember($firstname,$lastname,$email,$phone, $otp="email"){

        /*
             {
                "firstname": "John",
                "lastname": "Doe",
                "email": "john.doe@yousign.fr",
                "phone": "+33612345678",
                "procedure": "/procedures/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
            }
         */

        $member = array(
            "firstname" => $firstname,
            "lastname" => $lastname,
            "email" => $email,
            "phone" => $phone,
            "procedure" => $this->idAdvProc,
            "operationCustomModes" => [ $otp ]
        );

        $curl = curl_init();

        $param = json_encode($member,true);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."members",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $rtab = json_decode($response,true);
            $this->member = $rtab['id'];
            return $response;
        }
    }

    /**
     * @param $position
     * @param $page
     * @param $mention
     * @param $mention2
     * @param $reason
     * @return bool|string
     */
    public function AdvancedProcedureFileObject($position,$page,$mention,$mention2,$reason){
        /*
            {
                "file": "/files/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "member": "/members/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "position": "230,499,464,589",
                "page": 2,
                "mention": "Read and approved",
                "mention2": "Signed by John Doe",
                "reason": "Signed by John Doe (Yousign)"
            }

         */
        $parameter = array(
            "file"=> $this->idfile,
            "member"=> $this->member,
            "position"=> $position,
            "page"=> $page,
            "mention"=> $mention,
            "mention2"=> $mention2,
            "reason"=> $reason
        );

        $param = json_encode($parameter,true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."file_objects",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $rtab = json_decode($response,true);
            $this->fileobject = $rtab['id'];
            return $response;
        }

    }

    /**
     * @return bool|string
     */
    public function AdvancedProcedurePut(){
        /*
            {
               "start": true
            }
         */

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrlWslash."".$this->idAdvProc,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS =>"{\n   \"start\": true\n}",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }

    }

    /**
     * @param array $members
     * @param string $ProcName
     * @param string $ProcDesc
     * @param $mailsubject
     * @param $mailMessage
     * @param array $arrayTo
     * @return bool|string
     */
    public function addMemberWhithMailNotif($members = array(),$ProcName = '',$ProcDesc = '', $mailsubject, $mailMessage, $arrayTo = array("@creator", "@members") ){
        $curl = curl_init();

        /*
         * param 1 an array of members
         [
        {
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@yousign.fr",
            "phone": "+33612345678",
            "fileObjects": [
                {
                    "file": "/files/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                    "page": 2,
                    "position": "230,499,464,589",
                    "mention": "Read and approved",
                    "mention2": "Signed by John Doe"
                }
            ]
        }
    ]
         */
        $conf = array();

        $conf["email"] =
            array(
                "member.started" => array(
                    array(
                        "subject" => $mailsubject,
                        "message" => "Hello",
                        "to" => array("@member")
                    )
                ),
                "procedure.started" => array(
                    array(
                        "subject" => $mailsubject,
                        "message" => $mailMessage,
                        "to" => $arrayTo
                    )
                )
            )

        ;

        $body = array(
            "name" => $ProcName,
            "description" => $ProcDesc,
            "members" => $members,
            "config" => $conf

        );

        $param = json_encode($body,true);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."procedures",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * @param $filepath
     * @param $namefile
     * @return bool|string
     */
    public function AdvancedProcedureAddAttachement($filepath,$namefile){
        /*
            {
                "name": "Name of my attachment.pdf",
                "content": "JVBERi0xLjUKJb/3ov4KICA[...]VPRgo=",
                "procedure": "/procedures/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "type": "attachment"
            }
         */

        $data = file_get_contents($filepath);
        $b64Doc = base64_encode($data);

        $parameters = array(
            'name' => $namefile,
            'content' => $b64Doc,
            'procedure' => $this->idAdvProc,
            "type"=> "attachment"
        );

        $param = json_encode($parameters,true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiBaseUrl."files",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $param,
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$this->getApikey(),
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }


}
