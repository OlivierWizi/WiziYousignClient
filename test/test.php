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

