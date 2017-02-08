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
	
	$board = POST("board");
	//if (!$board) $board = GET("board");
	
	$subject = POST("subject");
	//if (!$subject) $subject = GET("subject");
	
	$unit = POST("unit");
	//if (!$unit) $unit = GET("unit");
	
	$year = POST("year");
	//if (!$year) $year = GET("year");
	
	$month = POST("month");
	//if (!$month) $month = GET("month");
	
	$qnum = POST("qnum");
	//if (!$qnum) $qnum = GET("qnum");
	
	$content = POST("content");
	//if (!$content) $content = GET("content");
	
	
	if (!$board || !$subject || !$unit || !$year ||
		!$month || !$qnum || !$content){
		die("Invalid parameters");
	}
	
	$query = "INSERT INTO questions (ExamBoard, Subject, ExamUnit, ExamYear,"
		. " ExamMonth, QuestionNumber, QuestionContent) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$stmt = $conn->prepare($query);
	$stmt->bind_param("sssssss", $board, $subject, $unit, $year, $month, $qnum, $content);
	$stmt->execute();
	
	if($conn->error){
		error_log($conn->error);
	}
?>