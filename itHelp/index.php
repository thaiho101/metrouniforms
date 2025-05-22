<?php
ob_start();
session_start(); // Start the session

// require_once("./config.php");
// $servername = DB_SERVER;
// $username = DB_USERNAME;
// $password = DB_PASSWORD;
// $db_name = DB_NAME;

// $conn = new mysqli($servername, $username, $password, $db_name);

?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset="UTF-8">
    <title> <?php echo $group; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="/tools/Logo/UT.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
<!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>

</head>
<body>
<div id='headNav'>
	<div id='menuNav'>
		<div class='menu1 menuButton'>
			<a href="../" class='menuSrcStyle dashMenuLink'>Dashboard
			</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu2 menuButton'>
			<a href="../" class='menuSrcStyle'>Manual</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu3 menuButton'>
			<a href="./" class='menuSrcStyle'>Setting</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu4 menuButton'>
			<a href="./" class='menuSrcStyle'>IT Help</a>
		</div>
	</div>
	<div id='workCompanyDiv'>
		<div class='workCompany'>
			<a href="../" class='workCompanyTitle'>
				<div><h1>Metro Uniforms</h1></div>
				<div class='metroLogo'></div>
			</a>
		</div>

	</div>
</div>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['itHelpDesks'])) {
        $itHelpDeskChosen = 'chosenMenu';
        $liveChatChosen = '';
        $itHelpDesksInvisible = '';
        $liveChatInvisible = 'invisible';
    } else if (isset($_GET['liveChat'])){
        $liveChatChosen = 'chosenMenu';
        $itHelpDeskChosen = '';
        $itHelpDesksInvisible = 'invisible';
        $liveChatInvisible = '';
    } else {
        $itHelpDeskChosen = 'chosenMenu';
        $liveChatChosen = '';
        $liveChatInvisible = 'invisible';
    }
}
?>
<div id='bodyNav'>
    <div id='contactMenu'>
        <div class='firstMenuContactDiv'>
            <div class='contact'>
                <form action="" method='get' >
                    <button type='submit' name='itHelpDesks' class='menuContactButton <?php echo $itHelpDeskChosen;?>'>IT Help Desks</button>
                </form>
            </div>
        </div>

        <div class='secondMenuContactDiv'>
            <div class='contact'>
                <form action="" method='get' >
                    <button type='submit' name='liveChat' class='menuContactButton <?php echo $liveChatChosen;?>'>Live Chat</button>
                </form>
            </div>
            <!-- <div class='contact'>Live Chat</div> -->
        </div>
    </div>



    <div class='spaceBetween'></div>

    <div id='contactContent'>
        <div class='ContentDiv'>
            <div class='<?php echo $itHelpDesksInvisible;?>'>
                <div class='mainTitle'>IT Help Desk</div>
                <div class='contactInformation'>
                    <div class='inputInfo'>
                        <div>Name:</div>
                        <input type="text" class='inputStyle' value='Henry'>
                    </div>
                    <div class='inputInfo'>
                        <div>Hot-Line: </div>
                        <input type="text" class='inputStyle' value='(+1)-###-6789'>
                    </div>
                    <div class='inputInfo'>
                        <div>Email: </div>
                        <input type="text" class='inputStyle' value='warehouse@crowndigi.com'>
                    </div>
                </div>
            </div>

            <div class='<?php echo $liveChatInvisible;?>'>
                <div class='mainTitle'>Live Chat</div>
                <div class=''>
                </div>
                <div>
                    <div>Hi there! What would you like me to help?</div>
                    <form action="" method='post'>
                        <input type="text">
                        <button type='submit'>Send</button>
                    </form>
                </div>
            </div>
        </div>

        
    </div>
</div>

<script src='script.js'></script>
</body>
</html>
<?php
ob_end_flush();
?>