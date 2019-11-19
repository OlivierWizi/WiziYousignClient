<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use WiziYousignClient\WiziSignClient;


$testkey = 'Your api key';

$client = new WiziSignClient($testkey);

$idfile = $client->newProcedure('testPDFPourYS.pdf');

$members = array(
    array(
        'firstname' => 'olivier',
        'lastname' => 'nival',
        'email' => 'olivier.nival@gmail.com',
        'phone' => '0652233424',
        'fileObjects' => array(
            array(
                'file' => $idfile,
                'page' => 1,
                'position' => "230,499,464,589",
                'mention' => "Read and approved",
                "mention2" =>"Signed by John Doe"

            )
        )


    )
);

// var_dump($client->getUsers());


$client->addMembersOnProcedure($members);