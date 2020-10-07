<?php


namespace WiziYousignClient;


use Exceptions\CurlException;
use Exceptions\ViolationsException;

class WiziSignClient
{
    private $apikey;
    private $apiBaseUrl;
    private $idfile;
    private $idAdvProc;
    private $member;
    private $fileobject;

    /**
     * WiziSignClient constructor.
     * @param $apikey
     * @param $mode
     */
    public function __construct($apikey, $mode)
    {
        $this->setApikey($apikey);
        if ($mode === 'prod') {
            $this->apiBaseUrl = 'https://api.yousign.com/';
        } else {
            $this->apiBaseUrl = 'https://staging-api.yousign.com/';
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
    public function setApikey($apikey)
    {
        $this->apikey = $apikey;
    }

    /**
     * @return mixed
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * @param $member
     */
    public function setMember($member)
    {
        $this->member = $member;
    }

    /**
     * @return mixed
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return mixed
     */
    public function getIdfile()
    {
        return $this->idfile;
    }

    /**
     * @param $idfile
     */
    public function setIdfile($idfile)
    {
        $this->idfile = $idfile;
    }

    /**
     * permet de recup le fichier signé sur yousign
     * @param $fileid
     * @param $mode
     * @return array|string
     * @throws CurlException
     */
    public function downloadSignedFile($fileid, $mode)
    {

        $urlstr = $fileid . "/download";
        if ($mode === 'binary') {
            $urlstr .="?alt=media";
        }

        return $this->api_request('GET', $urlstr);
    }

    /**
     * @param string $method
     * @param string $action
     * @param array|string|null $post Data to send with request
     * @return array|string Response of call
     *
     * @throws CurlException
     */
    public function api_request($method, $action = '', $post = null)
    {
        header('Content-Type: application/json'); // Specify the type of data

        if (strpos($action, '/') === 0) {
            $action = substr($action, 1);
        }

        $ch = curl_init($this->apiBaseUrl . $action); // Initialise cURL
        $authorization = "Authorization: Bearer " . $this->getApikey(); // Prepare the authorisation token
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization)); // Inject the token into the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (($method === 'POST' || $method === 'PUT') && !is_null($post)) {
            if (!is_string($post)) {
                $post = json_encode($post); // Encode the data array into a JSON string
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); // Set the posted fields
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
        $result = curl_exec($ch); // Execute the cURL statement
        $err = curl_error($ch);
        curl_close($ch); // Close the cURL connection

        if ($err) {
            throw new CurlException("cURL Error #:" . $err);
        }

        return json_decode($result, true);
    }

    /**
     * @return array|string
     *
     * @throws CurlException
     */
    public function getUsers()
    {
        return $this->api_request('GET', 'users');
    }

    /**
     * @param WiziSignFile|string $file File or filepath
     * @return $this
     *
     * @throws CurlException
     */
    public function newProcedure($file)
    {
        if (is_string($file)) {
            $file = new WiziSignFile($file);
        }

        $post = array(
            'name' => $file->getFilename(),
            'content' => $file->getBase64()
        );

        $response = $this->api_request('POST', 'files', $post);

        $this->idfile = $response['id'];

        return $this;
    }

    /**
     * @param $members
     * @param $titresignature
     * @param $description
     * @return array|string
     *
     * @throws CurlException
     */
    public function addMembersOnProcedure($members, $titresignature, $description)
    {
        $post = array(
            'name' => $titresignature,
            'description' => $description,
            'members' => $members
        );

        return $this->api_request('POST', 'procedures', $post);
    }

    /**
     * @param $parameters
     * @param bool $webhook
     * @param string $webhookMethod
     * @param string $webhookUrl
     * @param string $webhookHeader
     * @return array|string
     * @throws CurlException
     */
    public function AdvancedProcedureCreate($parameters, $webhook = false, $webhookMethod = '', $webhookUrl = '', $webhookHeader = '')
    {
        /*
         *
            {
                "name": "My procedure",
                "description": "Description of my procedure with advanced mode",
                "start" : false
            }
         */
        $conf = array();

        if ($webhook) {
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

        $response = $this->api_request('POST', 'procedures', $parameters);
        $this->idAdvProc = $response['id'];

        return $response;
    }

    /**
     * @param WiziSignFile|string $file File or filepath
     * @return array|string
     * @throws CurlException
     */
    public function AdvancedProcedureAddFile($file)
    {
        /*
         {
            "name": "Name of my signable file.pdf",
            "content": "JVBERi0xLjUKJb/3ov4KNiA [...] VPRgo=",
            "procedure": "/procedures/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
}
         */

        if (is_string($file)) {
            $file = new WiziSignFile($file);
        }

        $parameters = array(
            'name' => $file->getFilename(),
            'content' => $file->getBase64(),
            'procedure' => $this->idAdvProc
        );

        $response = $this->api_request('POST', 'files', $parameters);
        $this->idfile = $response['id'];

        return $response;
    }

    /**
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param $phone
     * @return array|string
     *
     * @throws CurlException
     * @throws ViolationsException
     */
    public function AdvancedProcedureAddMember($firstname, $lastname, $email, $phone)
    {

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
            "procedure" => $this->idAdvProc
        );

        $response = $this->api_request('POST', 'members', $member);

        if (array_key_exists('violations', $response)) {
            throw new ViolationsException(json_encode($response));

        }

        $this->member = $response['id'];

        return $response;
    }

    /**
     * @param $position
     * @param $page
     * @param $mention
     * @param null|string $mention2
     * @param null|string $reason
     * @return array|string
     * @throws CurlException
     */
    public function AdvancedProcedureFileObject($position, $page, $mention, $mention2 = null, $reason = null)
    {
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
            "file" => $this->idfile,
            "member" => $this->member,
            "position" => $position,
            "page" => $page,
            "mention" => $mention
        );

        if (!is_null($mention2)) {
            $parameter["mention2"] = $mention2;
        }

        if (!is_null($reason)) {
            $parameter["reason"] = $reason;
        }

        $response = $this->api_request('POST', 'file_objects', $parameter);
        $this->fileobject = $response['id'];

        return $response;
    }

    /**
     * @return array|string
     * @throws CurlException
     */
    public function AdvancedProcedurePut()
    {
        /*
            {
               "start": true
            }
         */

        $params = array(
            "start" => true
        );

        return $this->api_request('PUT', $this->idAdvProc, $params);

    }

    /**
     * @param array $members
     * @param string $ProcName
     * @param string $ProcDesc
     * @param $mailsubject
     * @param $mailMessage
     * @param array $arrayTo
     *
     * @return array|string
     *
     * @throws CurlException
     */
    public function addMemberWhithMailNotif($mailsubject, $mailMessage, $members = array(), $ProcName = '', $ProcDesc = '', $arrayTo = array("@creator", "@members"))
    {
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
            );

        $body = array(
            "name" => $ProcName,
            "description" => $ProcDesc,
            "members" => $members,
            "config" => $conf

        );

        return $this->api_request('POST', 'procedures', $body);
    }

    /**
     * @param $file
     * @return array|string
     *
     * @throws CurlException
     */
    public function AdvancedProcedureAddAttachement($file)
    {
        /*
            {
                "name": "Name of my attachment.pdf",
                "content": "JVBERi0xLjUKJb/3ov4KICA[...]VPRgo=",
                "procedure": "/procedures/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "type": "attachment"
            }
         */

        if (is_string($file)) {
            $file = new WiziSignFile($file);
        }

        $parameters = array(
            'name' => $file->getFilename(),
            'content' => $file->getBase64(),
            'procedure' => $this->idAdvProc,
            "type" => "attachment"
        );

        return $this->api_request('POST', 'files', $parameters);
    }


}
