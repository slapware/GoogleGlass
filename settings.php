<?php
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';

$file = "/tmp/glass.log";
$message = "";
$client = get_google_api_client();
// Authenticate if we're not already
if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid']) == null) {
	header('Location: ' . $base_url . '/oauth2callback.php');
	exit;
} else {
	verify_credentials(get_credentials($_SESSION['userid']));
	$client->setAccessToken(get_credentials($_SESSION['userid']));
	store_credentials($_SESSION['userid'], get_credentials($_SESSION['userid']));
}

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

$user_id = $_SESSION['userid'];
$access_token = get_credentials($user_id);
$hour = tour_get_delivery_hour($user_id);

$tod = $hour['delivery_hour'];
if (intval($tod) < 12) {
	$message = "Current hour set is " . $tod . " am";
	} else {
		$p = intval($tod) - 12;
	$message = "Current hour set is " . $p . " pm";
	}
// But first, handle POST data from the form (if there is any)
switch ($_POST['operation']) {
	case 'setDeliveryTime':	// **** setDeliveryTime ***********
		$hod = intval($_POST['element']);
		$output = print_r($_POST, true);
	    file_put_contents($file, $output, FILE_APPEND);
//		if(!empty($_POST['days'])) {
			if(isset($_POST['Monday']))
				$mon = 'Y';
			else
				$mon = 'N';
			if(isset($_POST['Tuesday']))
				$tue = 'Y';
			else
				$tue = 'N';
			if(isset($_POST['Wednesday']))
				$wed = 'Y';
			else
				$wed = 'N';
			if(isset($_POST['Thursday']))
				$thu = 'Y';
			else
				$thu = 'N';
			if(isset($_POST['Friday']))
				$fri = 'Y';
			else
				$fri = 'N';
			if(isset($_POST['Saturday']))
				$sat = 'Y';
			else
				$sat = 'N';
			if(isset($_POST['Sunday']))
				$sun = 'Y';
			else
				$sun = 'N';
//		}
		tour_update_delivery($user_id, $hod, $mon,$tue,$wed,$thu,$fri,$sat,$sun);
		$message = "Time of day has been saved !";
		break;
} // switch ($_POST['operation'])
	
?>
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Harper Events Settings</title>
  <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<!--   <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js'></script>
  <script src="./checkbox.jquery.js"></script> -->
    <!-- Le styles -->
    <link href="./static/bootstrap/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 40px;
      }

      /* Custom container */
      .container-narrow {
        margin: 0 auto;
        max-width: 700px;
      }
      .container-narrow > hr {
        margin: 30px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 60px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 72px;
        line-height: 1;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }
      
     
      #footer {
        margin-top: 120px;
        text-align: center
      }
    </style>
    <link href="./static/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="../assets/ico/favicon.png">
        </head>
<body>

    <div class="container-narrow">

      <div class="masthead">
       
        <h4 style="color:#1D7CB3">Harper Tours Glass Settings</h4>
      </div>

      <hr>
     
        <div class="container-narrow">
          
    
        </div>  
     
        <br><div class="hero">
        <div class= "row-fluid">
          <div class="span4">
            <img src="./static/images/book_01.png">
              
          </div>
                
                <div class="span8">
                   <h1>Settings</h1>
                 <p>Select Delivery Time and day(s) you would like to receive your Tour updates (EST).</p>
					    <?php if ($message != "") { ?>
					    <h4 style="color: red"> <?php echo $message; ?></h4>
					    <?php } ?>     
                  </div>
                     </div>
                       </div>
                           <hr>
                                   <div class="hero">
                                   <div class="row-fluid marketing">
                                   <div class="span6">

    
  <?php $hour = tour_get_delivery_hour($user_id);
  $tod = $hour['delivery_hour'];
   ?>  
        
    <form method="post">
    <?php if ($hour['Monday'] == 'N') { ?>
    <label class="checkbox inline">
      <input type="checkbox" id="Monday" name="Monday" value="Monday"> Monday
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
      <input type="checkbox" id="Monday" name="Monday" value="Monday" checked="checked"> Monday
     </label>
    <?php } ?>
    <hr>
    <?php if ($hour['Tuesday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Tuesday" name="Tuesday" value="Tuesday"> Tuesday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Tuesday" name="Tuesday" value="Tuesday" checked="checked"> Tuesday    
     </label>
    <?php } ?>
    <hr>
    <?php if ($hour['Wednesday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Wednesday" name="Wednesday" value="Wednesday"> Wednesday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Wednesday" name="Wednesday" value="Wednesday" checked="checked"> Wednesday    
     </label>
    <?php } ?>
    <hr>
    <?php if ($hour['Thursday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Thursday" name="Thursday" value="Thursday"> Thursday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Thursday" name="Thursday" value="Thursday" checked="checked"> Thursday    
     </label>
    <?php } ?>
    <hr>
    </div>
    <div class="span6">
    <?php if ($hour['Friday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Friday" name="Friday" value="Friday"> Friday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Friday" name="Friday" value="Friday" checked="checked"> Friday    
     </label>
    <?php } ?>
    <hr>
    <?php if ($hour['Saturday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Saturday" name="Saturday" value="Saturday"> Saturday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Saturday" name="Saturday" value="Saturday" checked="checked"> Saturday    
     </label>
    <?php } ?>
    <hr>
    <?php if ($hour['Sunday'] == 'N') { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Sunday" name="Sunday" value="Sunday"> Sunday    
     </label>
    <?php } else { ?>
    <label class="checkbox inline">
    <input type="checkbox" id="Sunday" name="Sunday" value="Sunday" checked="checked"> Sunday    
     </label>
    <?php } ?>
    <br><br><br>
    
  <select name="element"><br>
     <?php if ($tod == '1') { ?>
  	 <option selected value="1">1 am</option>
     <?php } else { ?>
   	 <option value="1">1 am</option>
     <?php } ?>
     <?php if ($tod == '2') { ?>
     <option selected value="2">2 am</opton>
     <?php } else { ?>
     <option value="2">2 am</opton>
     <?php } ?>
     <?php if ($tod == '3') { ?>
     <option selected value="3">3 am</option>
     <?php } else { ?>
     <option value="3">3 am</option>
     <?php } ?>
     <?php if ($tod == '4') { ?>
     <option selected value="4">4 am</opton>
     <?php } else { ?>
     <option value="4">4 am</opton>
     <?php } ?>
     <?php if ($tod == '5') { ?>
     <option selected value="5">5 am</option>
     <?php } else { ?>
     <option value="5">5 am</option>
     <?php } ?>
     <?php if ($tod == '6') { ?>
     <option selected value="6">6 am</opton>
     <?php } else { ?>
     <option value="6">6 am</opton>
     <?php } ?>
     <?php if ($tod == '6') { ?>
     <option selected value="7">7 am</opton>
     <?php } else { ?>
     <option value="7">7 am</opton>
     <?php } ?>
     <?php if ($tod == '8') { ?>
  	 <option selected value="8">8 am</opton>
     <?php } else { ?>
   	 <option value="8">8 am</option>
     <?php } ?>
     <?php if ($tod == '9') { ?>
     <option selected value="9">9 am</opton>
     <?php } else { ?>
     <option value="9">9 am</opton>
     <?php } ?>
     <?php if ($tod == '10') { ?>
     <option selected value="10">10 am</opton>
     <?php } else { ?>
     <option value="10">10 am</opton>
     <?php } ?>
     <?php if ($tod == '11') { ?>
  	 <option selected value="11">11 am</opton>
     <?php } else { ?>
  	 <option value="11">11 am</opton>
     <?php } ?>
     <?php if ($tod == '12') { ?>
     <option selected value="12">12 noon</opton>
     <?php } else { ?>
     <option value="12">12 noon</opton>
     <?php } ?>
     <?php if ($tod == '13') { ?>
     <option selected value="13">1 pm</opton>
     <?php } else { ?>
     <option value="13">1 pm</opton>
     <?php } ?>
     <?php if ($tod == '14') { ?>
     <option selected value="14">2 pm</opton>
     <?php } else { ?>
     <option value="14">2 pm</opton>
     <?php } ?>
     <?php if ($tod == '15') { ?>
     <option selected value="15">3 pm</opton>
     <?php } else { ?>
     <option value="15">3 pm</opton>
     <?php } ?>
     <?php if ($tod == '16') { ?>
     <option selected value="16">4 pm</opton>
     <?php } else { ?>
     <option value="16">4 pm</opton>
     <?php } ?>
     <?php if ($tod == '17') { ?>
     <option selected value="17">5 pm</opton>
     <?php } else { ?>
     <option value="17">5 pm</opton>
     <?php } ?>
     <?php if ($tod == '18') { ?>
     <option selected value="18">6 pm</opton>
     <?php } else { ?>
     <option value="18">6 pm</opton>
     <?php } ?>
     <?php if ($tod == '19') { ?>
     <option selected value="19">7 pm</opton>
     <?php } else { ?>
     <option value="19">7 pm</opton>
     <?php } ?>
     <?php if ($tod == '20') { ?>
     <option selected value="20">8 pm</opton>
     <?php } else { ?>
     <option value="20">8 pm</opton>
     <?php } ?>
     <?php if ($tod == '21') { ?>
     <option selected value="21">9 pm</opton>
     <?php } else { ?>
     <option value="21">9 pm</opton>
     <?php } ?>
     <?php if ($tod == '22') { ?>
     <option selected value="22">10 pm</opton>
     <?php } else { ?>
     <option value="22">10 pm</opton>
     <?php } ?>
     <?php if ($tod == '23') { ?>
     <option selected value="23">11 pm</opton>
     <?php } else { ?>
     <option value="23">11 pm</opton>
     <?php } ?>
     <?php if ($tod == '0') { ?>
     <option selected value="0">midnight</opton>
     <?php } else { ?>
     <option value="0">midnight</opton>
     <?php } ?>
     </select><br>
      <input type="hidden" name="subscriptionId" value="location">
      <input type="hidden" name="operation" value="setDeliveryTime">
      <button class="btn" type="submit">Set hour and day</button>
    </form>

      </div>
    </div>
  </div>

<div id="footer">
   <br><hr>
<p class="muted credit"><a href="http://www.harpercollins.com/">&copy; HarperCollins Publishers 2014</a></p>
        
      </div>
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap-transition.js"></script>
    <script src="js/bootstrap-alert.js"></script>
    <script src="js/bootstrap-modal.js"></script>
    <script src="js/bootstrap-dropdown.js"></script>
    <script src="js/bootstrap-scrollspy.js"></script>
    <script src="js/bootstrap-tab.js"></script>
    <script src="js/bootstrap-tooltip.js"></script>
    <script src="js/bootstrap-popover.js"></script>
    <script src="js/bootstrap-button.js"></script>
    <script src="js/bootstrap-collapse.js"></script>
    <script src="js/bootstrap-carousel.js"></script>
    <script src="js/bootstrap-typeahead.js"></script>
      
</html>