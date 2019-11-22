# WiziYousignClient
 Client yousign pour la nouvelle version de l'API de signature de documents yousign
 
 Utilisation : 
 
 <h1>Procédure basique</h1>
 
 ```
<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;

/**
 * ici votre clef d'api Yousign
 */
$testkey = 'YourYousign_API_KEY';

/**
 * On instancie notre client
 */
$client = new WiziSignClient($testkey,'dev');

/**
 * Création d'une nouvelle signature envoie du fichier a faire signer
 * @param filepath
 *
 */
$client->newProcedure('testPDFPourYS.pdf');

$members = array(
    array(
        'firstname' => 'olivier',
        'lastname' => 'nival',
        'email' => 'olivier.nival@gmail.com',
        'phone' => '0652233424',
        'fileObjects' => array(
            array(
                'file' => $client->getIdfile(),
                'page' => 1,
                'position' => "230,499,464,589",
                'mention' => "Read and approved",
                "mention2" =>"Signed by John Doe"

            )
        )


    )
);

/**
 * On termine la procedure de création de signature en envoyant la liste des utilisateurs , un titre a la signature, une description à la signature
 */
$client->addMembersOnProcedure($members,'encore une nouvelle signature','signature généré par le client php WiziYousignClient');


```
<h1>Procédure Avancée</h1>

```
<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;

/**
 * ici votre clef d'api Yousign
 */
$testkey = 'YourYousign_API_KEY';

/**
 * On instancie notre client
 */
$client = new WiziSignClient($testkey,'dev');

/**
 * ici nous créons une procedure en mode avancé
 * 
 */
$parameters = array(
    'name' => "Ma procedure en mode avancé",
    'description' => "Creation d'une procedure de signature en mmode avancé",
    'start'=> false
);

/**
 * initialisation de la procedure
 * 
 * @param $parameters
 * @param bool $notifmail
 * @return bool|string
 */
$client->AdvancedProcedureCreate($parameters);

$filepath = 'testPDFPourYS.pdf';
$namefile = 'pdfaadvanceproc';

/**
 * ici on ajoute le fichier à signer avec le chemin du fichier et le nom que l'on veut en sortie
 */
$client->AdvancedProcedureAddFile($filepath,$namefile);

/**
 * on ajoute les personnes devant signer
 * pour chaques personnes devant signer il faut executer successivement
 * $client->AdvancedProcedureAddMember($firstname,$lastname,$email,$phone) 
 * ET  $client->AdvancedProcedureFileObject($position,$page,$mention,$mention2,$reason);
 */

$firstname = "olivier";
$lastname  = "nival";
$email = "olivier.nival@gmail.com";
$phone = '0652233424';

/**
 * ajout du membre
 * @param $firstname
 * @param $lastname
 * @param $email
 * @param $phone
 * @return bool|string
 */
$client->AdvancedProcedureAddMember($firstname,$lastname,$email,$phone);

$position = "230,499,464,589";
$page = 1;
$mention = "Read and approved";
$mention2 = "Signed by ".$firstname." ".$lastname;
$reason = "Signed by ".$firstname." ".$lastname." (Yousign)";
/**
 * positionnement de la signature du membre sur le doc
 * @param $position
 * @param $page
 * @param $mention
 * @param $mention2
 * @param $reason
 * @return bool|string
 */
$client->AdvancedProcedureFileObject($position,$page,$mention,$mention2,$reason);


/**
 * on declenche le démarage de la signature les personnes pourront maintenant signer
 */
$client->AdvancedProcedurePut();

```

<h1>Procédure Avancée avec notification email</h1>

```

<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;

/**
 * ici votre clef d'api Yousign
 */
$testkey = 'YourYousign_API_KEY';

/**
 * On instancie notre client
 */
$client = new WiziSignClient($testkey,'dev');

/**
 * ici on ajoute le fichier à signer avec le chemin du fichier et le nom que l'on veut en sortie
 */
$client->AdvancedProcedureAddFile($filepath,$namefile);


 /**
  * Ajouter les membre avec notif mail :
  *
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

 $members = array(
     array(
       "firstname" => "Olivier",
         "lastname"=> "Nival",
         "email" => "olivier.nival@gmail.com",
         "phone" => "0652233424",
         'fileObjects' => array(
             array(
                 "file"=> $client->getIdfile(),
					"page"=> 2,
					"position"=> "230,499,464,589",
					"mention"=> "Read and approved",
				    "mention2"=> "Signed by John Doe"
             )
         )
     ),
 );


$mailsubject =  "Sujet du mail";
$mailMessage =  "Message du mail ";

/**
 * jout des membres
 */
$client->addMemberWhithMailNotif($members,$ProcName = 'Ma signature',$ProcDesc = 'masignature description', $mailsubject, $mailMessage, $arrayTo = array("@creator", "@members", "olivier@wizi.eu") );


/**
 * on declenche le démarage de la signature les personnes pourront maintenant signer
 */
$client->AdvancedProcedurePut();


```