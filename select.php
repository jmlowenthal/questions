<?php
// Setup initial connection details
$server = "localhost";
$user = "root";
$pwd = "";
$db = "questiondatabase";

// Create connection
$conn = new mysqli($server, $user, $pwd, $db);

// Check connection
if ($conn->connect_error){
    error_log($conn->connect_error);
    die("Connection error");
}

// Get all combinations of exam boards and subjects
$res = $conn->query("SELECT DISTINCT ExamBoard, Subject"
    . " FROM questions ORDER BY ExamBoard");
if ($res == NULL){
    error_log($conn->error);
    die("Server error");
}
?>

<head>
    <title>Specification Selection</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.1/css/materialize.min.css">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>

<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.1/js/materialize.min.js"></script>
    
    <nav>
        <div class="nav-wrapper">
            <ul class="right">
                <li title="Select a specification">
                    <a href="select.php" title="Select">
                        <i class="material-icons">view_module</i>
                    </a>
                </li>
                <li title="Find exam questions">
                    <a href="find.php" title="Find">
                        <i class="material-icons">search</i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <ul class="tabs">
            <?php
            if ($res->num_rows > 0){
                // Iterate rows for tab headings
                $last = "NOT-THIS";
                while ($row = $res->fetch_assoc()){
                    $subject = $row["ExamBoard"];
                    if ($subject != $last){
                        $last = $subject;
                        if ($subject == ""){
                            $subject = "None";
                        }
                        ?>
                        <li class="tab">
                            <a href="#<?=$subject?>">
                                <?=$subject?>
                            </a>
                        </li>
                        <?php
                    }
                }
            }
            ?>
        </ul>
        
        <div class="row" id="content">
        <?php
        if ($res->num_rows > 0){
            // Iterate rows
            $parent_row = 0;
            $res->data_seek(0);
            while ($parent = $res->fetch_assoc()){
                $board = $parent["ExamBoard"];
                $board_ = $board;
                if ($board_ == ""){
                    $board_ = "None";
                }
                
                echo "<div id='{$board_}'>";
                
                // Go back - ensure first row is included
                $res->data_seek($parent_row);
                while ($child = $res->fetch_assoc()){
                    if($child["ExamBoard"] == $board){
                        $subject = $child["Subject"];
                        ++$parent_row;
                        ?>
                        <div class="col s6 l3">
                            <div class="card">
                                <div class="card-image">
                                <?php
                                echo "<a href='find.php?"
                                    . "board={$board}&"
                                    . "subject={$subject}'><img "
                                    . "src='media/{$subject}.jpg'"
                                    . " style='width: 100%' />"
                                    . "<span class='card-title'>"
                                    . "{$subject}</span>";
                                ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    else {
                        break;
                    }
                }
                
                echo "</div>";
                
                $res->data_seek($parent_row);
            }
        }
        ?>
        </div>
    </div>
	
	<!--<div class="fixed-action-btn right">
        <a href="docs.pdf">Help?</a>
    </div>-->
</body>