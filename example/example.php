<?php

require_once __DIR__ . '/../vendor/autoload.php';

$faker = \Faker\Factory::create();

$template = new \App\Entity\Template(
    1,
    'Votre voyage avec une agence locale [quote:destination_name]',
    "
Bonjour [user:first_name],

Merci d'avoir contacté un agent local pour votre voyage [quote:destination_name].

Bien cordialement,

L'équipe Evaneos.com
www.evaneos.com
");
$templateManager = new \App\TemplateManager();

try{
    $message = $templateManager->getTemplateComputed(
        $template,
        [
            'quote' => new \App\Entity\Quote($faker->randomNumber(), $faker->randomNumber(), $faker->randomNumber(), $faker->date())
        ]
    );

}catch (\InvalidArgumentException $e){
    exit($e->getMessage());
}

echo $message->subject . "\n" . $message->content;
