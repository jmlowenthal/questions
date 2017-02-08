<?php
	if($_SERVER["REMOTE_ADDR"] != "::1") die("Access denied");

	function POST($parameter){
		if (isset($_POST[$parameter])) return $_POST[$parameter];
		return NULL;
	}
	
	function GET($parameter){
		if (isset($_GET[$parameter])) return $_GET[$parameter];
		return NULL;
	}
	
	$server = "localhost";
	$user = "root";
	$pwd = "";
	$db = "questiondatabase";
	
	// Connect to database
	$conn = new mysqli($server, $user, $pwd, $db);
	if ($conn->connect_error) {
		error_log($conn->connect_error);
		die("Connection error");
	}
	
	$qid = POST("qid");
	$tag = POST("tag");
	
	if (!$qid || !$tag) die("Invalid parameters");
	
	$query = "INSERT INTO topictags (QuestionID, Tag) VALUES (?, ?)";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("ss", $qid, $tag);
	$stmt->execute();
	
	if($conn->error){
		error_log($conn->error);
	}
?>