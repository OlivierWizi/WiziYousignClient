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

<h1>Procédure Basic avec notification email venant de yousign</h1>

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
$client = new WiziSignClient($testkey, 'dev');

/**
 * Création d'une nouvelle signature envoie du fichier a faire signer
 * @param filepath
 *
 */
$client->newProcedure('testPDFPourYS.pdf');



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
$mailMessage =  " Bonjour vous devez signer votre document <tag data-tag-type=\"button\" data-tag-name=\"url\" data-tag-title=\"Access to documents\">Access to documents</tag>";

/**
 * ajout des membres et démarage de la nignature 
 * envoi du mail au personnes qui doivent signer
 */
$client->addMemberWhithMailNotif($members,$ProcName = 'Ma signature',
    $ProcDesc = 'masignature description', $mailsubject, $mailMessage, $arrayTo = array("@creator", "@members", "olivier@wizi.eu") );


```

<h1>Procedure avancée avec gestion des Webhooks</h1>
Ici nous allons pouvoir demander a yousign de nous envoyer une requete vers une url de notre server
à chaque étapes de la signature.

page php à mettre en place permettant de recuperer la requete envoyé par yousing aux étapes de signature

<h1>votredomaine.com/webhookget</h1>
<p>
dans cette exemple simple,
le script prendra le contenu envoyé par yousign et l'ecrira dans un fichier texte.
Avous d'adapter votre logique en fonction de ce que vous voudrez déclencher commes traitements.
</p>

```
<?php

$text = '************************************************************************';

file_put_contents('./request.txt', $text.PHP_EOL, FILE_APPEND);

$text = file_get_contents('php://input');

file_put_contents('./request.txt', $text.PHP_EOL, FILE_APPEND);

```

Maintenant que nous avons ceci, voici le code php perméttant de créer une procedure avec les webhook.

<h1>mondomaine.com/creersignatureavecwebhook</h1>

```

<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;

/**
 * ici votre clef d'api Yousign
 */
$testkey = 'Your_API_KEY';

/**
 * On instancie notre client
 */
$client = new WiziSignClient($testkey, 'dev');

/**
 * ici nous créons une procedure en mode avancé
 *
 */
$parameters = array(
    'name' => "Ma procedure en mode avancé",
    'description' => "Creation d'une procedure de signature en mmode avancé",
    'start' => false
);

/**
 * initialisation de la procedure
 *
 * @param $parameters
 * @param bool $notifmail
 * @return bool|string
 */
$client->AdvancedProcedureCreate($parameters,$webhook = true,$webhookMethod = 'POST',$webhookUrl = 'http://votredomaine.com/webhookget.php',$webhookHeader = 'testwebhook');

$filepath = 'testPDFPourYS.pdf';
$namefile = 'pdfaadvanceproc';

/**
 * ici on ajoute le fichier à signer avec le chemin du fichier et le nom que l'on veut en sortie
 */
$client->AdvancedProcedureAddFile($filepath, $namefile);

/**
 * on ajoute les personnes devant signer
 * pour chaques personnes devant signer il faut executer successivement
 * $client->AdvancedProcedureAddMember($firstname,$lastname,$email,$phone)
 * ET  $client->AdvancedProcedureFileObject($position,$page,$mention,$mention2,$reason);
 */

$firstname = "olivier";
$lastname = "nival";
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
$client->AdvancedProcedureAddMember($firstname, $lastname, $email, $phone);

$position = "230,499,464,589";
$page = 1;
$mention = "Read and approved";
$mention2 = "Signed by " . $firstname . " " . $lastname;
$reason = "Signed by " . $firstname . " " . $lastname . " (Yousign)";
/**
 * positionnement de la signature du membre sur le doc
 * @param $position
 * @param $page
 * @param $mention
 * @param $mention2
 * @param $reason
 * @return bool|string
 */
$client->AdvancedProcedureFileObject($position, $page, $mention, $mention2, $reason);


/**
 * on declenche le démarage de la signature les personnes pourront maintenant signer
 */
$client->AdvancedProcedurePut();

```