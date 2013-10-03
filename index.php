<?php

require("jolt.php");
include("Services/Twilio.php");

$_GET['route'] = isset($_GET['route']) ? '/'.$_GET['route'] : '/';
$app = new Jolt('site',false);
$app->option('source', 'config.ini');

$client = new Services_Twilio($app->option('twilio.accountsid'), $app->option('twilio.authtoken') );
$fromNumber = $app->option('twilio.fromNumber');

$app->store('client',$client);

$app->get('/', function() use($app){
?>
	<h1>Moustachify yourself</h1>
	<p>Text a picture with the word "MOUSTACHE" to <strong><?=$app->option('twilio.fromNumber')?></strong></p>
<?php	
});

$app->post('/listener', function() use($app){
	if( strtolower($_POST['Body']) == 'moustache' ){
		if ( isset($_POST['NumMedia']) && $_POST['NumMedia'] > 0 ){
			for ($i = 0; $i < $_POST['NumMedia']; $i++){
				if (strripos($_POST['MediaContentType'.$i], 'image') === False)	continue;

				$file = sha1($_POST['MediaUrl'.$i]).'.jpg';
				file_put_contents('images/'.$file, file_get_contents($_POST['MediaUrl'.$i]));
				chmod ('images/'.$file, 01777);
				$url = $app->option('site.url').'/images/'.$file;

				$url = 'http://mustachify.me/?src='.urlencode( $url );

				$message = $app->store('client')->account->messages->sendMessage(
					$app->option('twilio.fromNumber'),
					$_POST['From'],
					'Here you go!',
					array($url)
				);
			}
		}
	}else{
		$message = $app->store('client')->account->messages->sendMessage(
			$app->option('twilio.fromNumber'),
			$_POST['From'],
			'Invalid command.'
		);		
	}
});
$app->listen();