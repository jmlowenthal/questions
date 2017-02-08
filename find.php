<!DOCTYPE html>
<?php
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
	
	// Receive GET parameters
	$topics  = (array)GET("topics");
	$years   = (array)GET("years");
	$months  = (array)GET("months");
	$units   = (array)GET("units");
	$subject = GET("subject");
	$board   = GET("board");
	
	$subject = is_array($subject) ? $subject[0] : $subject;
	$board = is_array($board) ? $board[0] : $board;
	
	function array_params($conn, $query, $params){
		$stmt = $conn->prepare($query);
		if (!$stmt){
			error_log($conn->error);
			error_log("\tQuery: " . $query);
			error_log("\tParams: " . var_export($params, true));
			die("Statement error");
		}
		
		if (count($params) < 1) return $stmt;
		
		$types = "";
		$bind_params = array(& $types);
		foreach ($params as &$p){
			$bind_params[] = &$p;
			switch(gettype($p)){
				case "integer":
					$types .= "i";
					break;
				case "float":
				case "double":
					$types .= "d";
					break;
				case "string":
					$types .= "s";
					break;
				default:
					$types .= "b";
					break;
			}
		}
		
		call_user_func_array(array($stmt, "bind_param"), $bind_params);
		return $stmt;
	}
	
	function _dump($value){
		echo "<pre>";
		var_dump($value);
		echo "</pre>";
	}
?>

<head>
	<title>Exam questions</title>

	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.1/css/materialize.min.css">
	<link rel="stylesheet" href="css/katex.min.css"></link>
	<link rel="stylesheet" href="css/style.css">
	
	<script src="js/showdown.min.js"></script>
	<script src="js/katex.min.js"></script>
	<script src="js/auto-render.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.1/js/materialize.min.js"></script>
	
	<script>
		$(function(){
			var converter = new showdown.Converter();
			$(".content").each(function(){
				$(this).html(converter.makeHtml($(this).html()));
				renderMath(this);
			})
		});
		
		function renderMath(dom){
			renderMathInElement(dom, {
					delimiters: [
						{left: "$$", right: "$$", display: true},
						{left: "\\[", right: "\\]", display: true},
						{left: "$", right: "$", display: false},
						{left: "\\(", right: "\\)", display: false}
					]
				});
		}
		
		function hide(dom){
			var parent = $(dom).parents(".question");
			parent.addClass("no-print");
			parent.fadeTo(100, 0.5);
			$(dom).parent().children("a").show();
			$(dom).hide();
		}
		
		function unhide(dom){
			var parent = $(dom).parents(".question");
			parent.removeClass("no-print");
			parent.fadeTo(100, 1);
			$(dom).parent().children("a").show();
			$(dom).hide();
		}
		
		<?php if($_SERVER["REMOTE_ADDR"] == "::1") { ?>
		function addtag(dom){
			var parent = $(dom).parents(".question");
			$("#qid").val(parent.attr("id"));
			$("label[for='qid']").addClass("active");
			$("#tag-modal").openModal();
		}
		
		function _addtag(){
			var modal = $("#tag-modal");
			var qid = modal.find("#qid").val();
			var tag_div = modal.find("#tag");
			var tag = tag_div.val();
			$.post("post-tag.php", { qid: qid, tag: tag },
				function(data){
					tag_div.val("");
					tag_div.parent().find("label").removeClass("active");
					
					if (data) alert(data);
					else {
						// Assume success
						var tags = $("#" + qid).find("#tags");
						if (tags.text() == "none") tags.text(tag + " ");
						else tags.append(", " + tag);
					}
				}
			);
		}
		<?php } ?>
	</script>
	
	<?php if($_SERVER["REMOTE_ADDR"] == "::1") { ?>
	<div id="tag-modal" class="modal bottom-sheet">
		<div class="row modal-content">
			<div class="input-field col s3 m2">
				<input type="number" id="qid">
				<label for="qid">Question ID</label>
			</div>
			<div class="input-field col s9 m10">
				<input type="text" id="tag">
				<label for="tag">Tag</label>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#!" onclick="_addtag()" class="modal-action modal-close btn-flat">Add</a>
		</div>
	</div>
	<?php } ?>
	
	<div class="container">
		<div class="no-print" id="header">
			<form>
				<?php
					function checkboxes($field, $name, $selected){
						global $subject, $board, $conn;
						
						$query = "SELECT DISTINCT {$field} FROM questions WHERE 1";
						$params = [];
						if ($subject){
							$query .= " AND Subject = ?";
							$params[] = $subject;
						}
						if ($board){
							$query .= " AND ExamBoard = ?";
							$params[] = $board;
						}
						$query .= " ORDER BY {$field}";
						
						$stmt = array_params($conn, $query, $params);
						$stmt->execute();
						
						$res = $stmt->get_result();
						if ($res->num_rows > 0){
							while ($row = $res->fetch_assoc()){
								$value = $row[$field];
								$checked = "";
								if (in_array($value, $selected)){
									$checked = " checked";
								}
								?>
								<div class="col">
									<input type='checkbox' name='<?=$name?>'
										value='<?=$value?>' id="<?=$value?>"
										<?=$checked?> />
									<label for="<?=$value?>">
										<?=$value?>
									</label>
								</div>
								<?php
							}
						}
					}
				?>
				
				<h5>Years</h5>
				<div class="row">
					<?php checkboxes("ExamYear", "years[]", $years); ?>
				</div>
				
				<h5>Months</h5>
				<div class="row">
					<?php checkboxes("ExamMonth", "months[]", $months); ?>
				</div>
				
				<h5>Units</h5>
				<div class="row">
					<?php checkboxes("ExamUnit", "units[]", $units); ?>
				</div>
				
				<h5>Topics</h5>
				<div class="row">
					<?php
					$query = "SELECT DISTINCT Tag FROM topictags "
						. "NATURAL JOIN questions WHERE 1";
					$params = [];
					
					if ($subject){
						$query .= " AND Subject = ?";
						$params[] = $subject;
					}
					if ($board){
						$query .= " AND ExamBoard = ?";
						$params[] = $board;
					}
					
					if (!empty($units)){
						$query .= " AND questions.ExamUnit IN ("
							. implode(", ", array_fill(0, count($units), "?")) . ")";
						$params = array_merge($params, $units);
					}
					
					if (!empty($years)){
						$query .= " AND questions.ExamYear IN ("
							. implode(", ", array_fill(0, count($years), "?")) . ")";
						$params = array_merge($params, $years);
					}
					if (!empty($months)){
						$query .= " AND questions.ExamMonth IN ("
							. implode(", ", array_fill(0, count($months), "?")) . ")";
						$params = array_merge($params, $months);
					}
					
					$query .= " ORDER BY Tag";
					
					$stmt = array_params($conn, $query, $params);
					$stmt->execute();
					
					$res = $stmt->get_result();
					if ($res->num_rows > 0){
						echo "<div class=\"col m11\"><div class=\"row\">";
						while ($row = $res->fetch_assoc()){
							$value = $row["Tag"];
							$checked = "";
							if (in_array($value, $topics)){
								$checked = " checked";
							}
							?>
							<div class="col">
								<input type="checkbox" name="topics[]"
									value="<?=$value?>" id="<?=$value?>"
									<?=$checked?> />
								<label for="<?=$value?>">
									<?=$value?>
								</label>
							</div>
							<?php
						}
						echo "</div></div>";
					}
					?>
				</div>
					
				<input type="hidden" name="subject"
					value="<?=$subject?>">
				<input type="hidden" name="board"
					value="<?=$board?>">
				
				<div align="right" style="border-bottom: 1px solid #9e9e9e; padding-bottom: 20px; margin-bottom: 20px">
					<button class="btn">Search
						<i class="material-icons right">send</i>
					</button>
				</div>
			</div>
			
			<div id="results">
				<?php
				$query = "SELECT * FROM questions ";
				$params = [];
				
				if (!empty($topics)){
					$query .= "NATURAL JOIN topictags WHERE topictags.Tag IN ("
						. implode(", ", array_fill(0, count($topics), "?")) . ")";
					$params = array_merge($params, $topics);
				}
				else
				{
					$query .= "WHERE 1";
				}
				
				if (!empty($years)){
					$query .= " AND questions.ExamYear IN ("
						. implode(", ", array_fill(0, count($years), "?")) . ")";
					$params = array_merge($params, $years);
				}
				
				if (!empty($months)){
					$query .= " AND questions.ExamMonth IN ("
						. implode(", ", array_fill(0, count($months), "?")) . ")";
					$params = array_merge($params, $months);
				}
				
				if (!empty($units)){
					$query .= " AND questions.ExamUnit IN ("
						. implode(", ", array_fill(0, count($units), "?")) . ")";
					$params = array_merge($params, $units);
				}
				
				// Check for subject and exam board
				if ($subject){
					$query .= " AND Subject = ?";
					$params[] = $subject;
				}
				if ($board){
					$query .= " AND ExamBoard = ?";
					$params[] = $board;
				}
			
				// Remove copies of each question caused by multiple tags
				// per question identifier
				$query .= " GROUP BY QuestionID";
				
				if (!empty($topics)){
					// Order by relevance
					$query .= " ORDER BY (SELECT COUNT(Tag) "
						. "FROM topictags WHERE Tag IN ("
						. implode(", ", array_fill(0, count($topics), "?"))
						. ") AND QuestionID = questions.QuestionID) DESC";
					$params = array_merge($params, $topics);
				}
				else {
					$query .= " ORDER BY QuestionNumber";
				}
				
				$stmt = array_params($conn, $query, $params);
				$stmt->execute();
				
				// Get table of results
				$res = $stmt->get_result();
				if ($res == NULL){
					error_log($conn->error);
					die("Server error");
				}
				
				while($row = $res->fetch_assoc()){
					if (!$row){
						die("No question found");
					}
					
					$id = $row["QuestionID"];
					$board = $row["ExamBoard"];
					$subject = $row["Subject"];
					$unit = $row["ExamUnit"];
					$year = $row["ExamYear"];
					$month = $row["ExamMonth"];
					$qnum = $row["QuestionNumber"];
					$content = $row["QuestionContent"];
					?>
					
					<div id="<?=$id?>" class="question">
						<small><?=$board?> <?=$subject?> <?=$unit?></small>
						<small class="right"><?=$month?> <?=$year?></small>
						<div class="row">
							<div class="col s1">
								<p><?=$qnum?>.</p>
							</div>
							<div class="col s11">
								<div class="content"><?=$content?></div>
								<div class="no-print right">
									<small>
										<a href="docs/<?=$board?>/<?=$subject?>/<?=$unit?>-<?=$year?>-<?=$month?>.pdf"
											target="__blank">mark scheme</a>
									</small>
								</div>
								<div class="no-print left">
									<small>
										<a href="#!" onclick="hide(this)">hide</a>
										<a href="#!" onclick="unhide(this)" hidden>show</a>
									</small>
								</div>
							</div>
						</div>
						
						<?php
						if($_SERVER["REMOTE_ADDR"] == "::1") {
							$topics = $conn->query("SELECT * FROM topictags WHERE QuestionID = {$id}");
							?>
							<small class="no-print">
								Tags:
								<?php
								echo "<div id='tags' style='display: inline-block'>";
								if ($topics->num_rows > 0){
									$tlist = [];
									while ($topic = $topics->fetch_assoc()){
										$tlist[] = $topic["Tag"];
									}
									echo implode(", ", $tlist);
								}
								else echo "none";
								echo "</div>";
								?>
								
								<a href="#!" onclick="addtag(this)">+</a>
							</small>
						<?php } ?>
					</div>
					
				<?php } ?>
			</div>
		</form>
	</div>
	
	<div class="fixed-action-btn no-print" style="bottom: 45px; right: 24px;">
		<a class="btn-floating btn-large red" onclick="window.print()">
			<i class="material-icons">print</i>
		</a>
	</div>
</body>