<?php

require 'rest_client.php';
$rest = new CR\tools\rest("https://rest.cleverreach.com/v1");
$rest->throwExceptions = true;	//default
echo "<pre>";

/**
	- Basic Information - 
	
	GET - will retrieve data
	POST - for creating new data
	PUT - Update/replace existing data
	DELETE - delete existing data

	see: https://en.wikipedia.org/wiki/Representational_state_transfer for more information
*/

echo "### Login - will retrieve Token ###\n";
try {
	/*
	try to login and receive token!
	on error script execution will be cancled
	*/
	$token = $rest->post('/login', 
		array(
			"client_id"=>'<YOUR_CUSTOMER_ID>',
			"login"=>'<YOUR_LOGIN>',
			"password"=>'<YOUR_PASSWORD>'
		)
	);
	//no error, lets use the key
	$rest->setAuthMode("jwt", $token);
	var_dump($token);

} catch (\Exception $e){
	var_dump( (string) $e );
	var_dump($rest->error);
	exit;
}


echo "### Return basic client information ###\n";
var_dump( 
	$rest->get("/clients")
);


echo "### Return all available groups ###\n";
var_dump( 
	$rest->get("/groups")
);


echo "### Create a new group ###\n";
$gotham_group = false;
try {
	$gotham_group = $rest->post("/groups", array("name"=>"Gotham Newsletter (REST)"));
	var_dump($gotham_group);
} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### Add group attributes to Gotham Newsletter ###\n";
try {
	//attributes bound to group
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"firstname", "type"=>"text"));
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"lastname", "type"=>"text"));
	$rest->post("/groups/{$gotham_group->id}/attributes", array("name"=>"gender", "type"=>"gender"));
} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### Add global attributes to Gotham Newsletter ###\n";
try {
	//global attribute
	$rest->post("/attributes", array("name"=>"is_vilain", "type"=>"number"));
	$rest->post("/attributes", array("name"=>"is_batman", "type"=>"number"));

} catch (\Exception $e){
	// echo "!!!! Batcomputer Error: {$rest->error} !!!!\n";
	echo "Field probably allready exists!\n";
}


echo "### Adding single receiver to Gotham Newsletter ###\n";
$batman = false;
try {
	$receiver = array(
		"email"				=> "bruce@wayne.com",
		"registered"		=> time(),	//current date
		"activated"			=> time(),
		"source"			=> "Batcave Computer",
		"attributes"		=> array(
								"firstname" => "Bruce",
								"lastname" => "Wayne",
								"gender" => "male"
							),
		"global_attributes"	=> array(
								"is_batman" => 1
							),
		"orders"			=> array(
									array(
										"order_id" => "xyz12345",	//required
										"product_id" => "SN12345678",	//optional
										"product" => "Batman - The Movie (DVD)", //required
										"price" => 9.99, //optional
										"currency" => "EUR", //optional
 										"amount" => 1, //optional
										"mailing_id" => "8765432", //optional
										"source" => "Batshop", //optional
									),
									array(
										"order_id" => "xyz12345",	//required
										"product" => "Batman - The Musical (CD)", //required
									)

								)


	);
	$batman = $rest->post("/groups/{$gotham_group->id}/receivers", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}


echo "### Adding Multiple receivers to Gotham Newsletter ###\n";
try {
	$receivers = array();

	$receivers[] = array(
		"email"				=> "joker@gotham.com",
		"attributes"		=> array(
								"firstname" => "unknown",
								"lastname" => "unknown",
								"gender" => "male"
							),
	);

	$receivers[] = array(
		"email"				=> "twoface@gotham.com",
		"attributes"		=> array(
								"firstname" => "harvey",
								"lastname" => "dent",
								"gender" => "male"
							),
	);

	$receivers[] = array(
		"email"				=> "poson-ivy@gotham.com",
		"attributes"		=> array(
								"firstname" => "Pamela Lillian ",
								"lastname" => "Isley",
								"gender" => "female"
							),
	);

	$rest->post("/groups/{$gotham_group->id}/receivers", $receivers);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### updating single receiver ###\n";
try {
	$receiver = array(
		"email" => "bruce@wayne.com",
		"global_attributes"	=> array("is_batman" => "2")
	);

	$rest->put("/groups/{$gotham_group->id}/receivers/bruce@wayne.com", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### update Multiple receivers in Gotham Newsletter ###\n";
try {
	$receivers = array();

	$receivers[] = array(
		"email"				=> "joker@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1"),
		"orders"			=> array(
									array(
										"order_id" => "xyz345345",	//required
										"product_id" => "CDX35434534",	//optional
										"product" => "Inhumans - The Movie (DVD)", //required
										"price" => 9.99, //optional
										"currency" => "EUR", //optional
 										"amount" => 1, //optional
										"mailing_id" => "87654321", //optional
										"source" => "Inhumans Shop", //optional

									)
								)
	);

	$receivers[] = array(
		"email"				=> "twoface@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1")
	);

	$receivers[] = array(
		"email"				=> "poson-ivy@gotham.com",
		"global_attributes"	=> array("is_vilain" => "1")
	);

	$rest->post("/groups/{$gotham_group->id}/receivers/upsert", $receivers);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}

echo "### deactivate receiver ###\n";
try {
	$rest->put("/groups/{$gotham_group->id}/receivers/poson-ivy@gotham.com/setinactive", $receiver);

} catch (\Exception $e){
	echo "!!!! Batcomputer Error: {$rest->error} !!!!";
	exit();
}
