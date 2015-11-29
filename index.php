<?php
require __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('America/New_York');

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// $log = new Monolog\Logger('name');
// $log->pushHandler(new StreamHandler('app.log', Monolog\Logger::WARNING));
// $log->addWarning('Oh heck no');

$app = new \Slim\Slim(array(
  'view' => new \Slim\Views\Twig()
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
);
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->get('/', function() use($app){
  $app->render('about.twig');
})->name('home');

$app->get('/contact', function() use($app){
  $app->render('contact.twig');
})->name('contact');

$app->get('/education', function() use($app){
  $app->render('education.twig');
})->name('education');

$app->post('/contact', function() use($app){
  $name = $app->request->post('name');
  $email = $app->request->post('email');
  $msg = $app->request->post('msg');

  if(!empty($name) && !empty($email) && !empty($msg)) {
    $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
    $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    $cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);
  } else {
    //message the user that was a problem
    $app->redirect('/contact');
  }

  $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
  // $transport->setUsername("Username");
  // $transport->setPassword("Password");

  $mailer = \Swift_Mailer::newInstance($transport);

  $message = \Swift_Message::newInstance();
  $message->setSubject('Email From Our Website');
  $message->setFrom(array(
      $cleanEmail => $cleanName
  ));
  $message->setTo(array('rodthomas@macmobile.local'));
  $message->setBody($cleanMsg);

    $result = $mailer->send($message);
    if($result > 0) {
        // send a message that says thank you.
        $app->redirect('/');
    } else {
        // send a message to the user that the message failed
        // log that there was an error
        $app->redirect('/contact');
    }
});

$app->run();
