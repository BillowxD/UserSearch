<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL &~ E_NOTICE &~ E_DEPRECATED);
	include_once "src/defines.php";
	include_once "src/autoload.php";
	

	use API\Request;
	$req = new Request();
	if (($res = $req->getResponse()) !== false) echo json_encode($res);
	else echo json_encode(["status" => false, "error" => "Bad api request"]);