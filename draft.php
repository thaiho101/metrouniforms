<?php
require_once("./config.php");
$servername = DB_SERVER;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$db_name = DB_NAME;

$conn = new mysqli($servername, $username, $password, $db_name);
//=========================Information Add Needed=============================================
$companyName	= 'UT SouthWestern';
$group 			= 'PFAS';
$dataTable		= 'ut_southwestern_pfas01152025';

$voucher 		= '$200';
$discount 		= '20%';
$taxAccept 		= 'FREE';
$topAndBottomColor 	= 'Navy';
$jacketVestSweaterColor	= 'Black|White|Grey|Navy';
$notCover 		= 'Accessory|Shoes|Undershirt|etc.';
$embroidery 	= 'LOGO $11 - LeftChest|LeftSleeve';
$effectiveDate 	= 'Jan 15, 2025';
$endDate 		= 'Jan 31, 2025';

$voucherStartDate 	= new Datetime('2025-01-15 00:00:00');
$voucherLastDate 	= new Datetime('2025-01-31 23:59:59');

$csvFileGenerate 	= 'PFAS_Status.csv';
$fileLocation 		= 'PFAS';
//============================================================================================
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cvs_File']))
{
	header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $csvFileGenerate . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Employee ID', 'First Name', 'Last Name', 'Status']); // CSV header

    $query_sql = "SELECT * FROM {$dataTable}";
    $result = $conn->query($query_sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['employee_id'], $row['first_name'], $row['last_name'], $row['status']]);
        }
    }
    fclose($output);
    exit();
}
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

<!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
<!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>

</head>
<body>
<div class='addEmp'>
	<form method='post'>
		<label for='employeeID' type='text' class='addSectionTextStyle'>New Employee ID:</label>
		<input type="text" name="employeeID" class='boxInput' required>
		<label for='firstName' type='text' class='addSectionTextStyle'>First Name:</label>
		<input type="text" name="firstName" class='boxInput' required>
		<label for='lastName' type='text' class='addSectionTextStyle'>Last Name:</label>
		<input type="text" name="lastName" class='boxInput' required>
		<BUTTON type='submit' name='add' class='addButton'>ADD</BUTTON>
	</form>
</div>
<br>
<?php


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add']))
{
	$emID = $_POST['employeeID'];
	$fName = $_POST['firstName'];
	$lName = $_POST['lastName'];

	$stmt = $conn->prepare("INSERT INTO {$dataTable} (employee_id, first_name, last_name) VALUES (?, ?, ?)");
	$stmt->bind_param('sss', $emID, $fName, $lName);
	$stmt->execute();
	$stmt->close();
	header("Location: http://192.168.0.90/tools/". $fileLocation . "/");
    // exit();
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $em_id = $_POST['emp_id'];
    $status = 'Done';
    $update_sql = "UPDATE {$dataTable} SET status = '$status' 
    				WHERE employee_id='$em_id'";
    $conn->query($update_sql);



	$statusChange = "INSERT INTO status_track (employee_id, status, latest_change)
						VALUES ('$em_id', '$status', NOW())";
	$conn->query($statusChange);	
    
    
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['processing_status'])) {
    $em_id = $_POST['emp_id'];
    $status = 'Processing';
    $update_sql = "UPDATE {$dataTable} SET status='$status' WHERE employee_id='$em_id'";
    $conn->query($update_sql);

    $statusChange = "INSERT INTO status_track (employee_id, status, latest_change)
    						VALUES ('$em_id', '$status', NOW())";
    $conn->query($statusChange);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refresh_status']))
{
	$em_id = $_POST['emp_id'];
	$status = '';
	$update_sql = "UPDATE {$dataTable} SET status='$status' WHERE employee_id='$em_id'";
	$conn->query($update_sql);

	$statusChange = "INSERT INTO status_track (employee_id, status, latest_change)
    						VALUES ('$em_id', 'Refresh', NOW())";
    $conn->query($statusChange);

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
echo $_POST['emp_id'];




$query_sql = "SELECT * FROM {$dataTable} ORDER BY first_name ASC";

$result = $conn->query($query_sql);

$employee_count = 1;
if($result->num_rows > 0)
{
	echo "<div class='tableContainer'>
			<table border='1.0'>
				<thead class='theadBorder'>
					<th colspan='8' class='cellBorder titleTextStyle'>{$companyName}</th>
				</thead>
				<thead class='sticky1 theadBorder'>
					<th colspan='8' class='cellBorder titleTextStyle'>{$group}</th>
				</thead>
				<thead class='sticky theadBorder'>

					<th class='cellBorder theadBorder'>No.</th>
					<th class='cellBorder expandWidthCell theadBorder'>Employee ID</th>
					<th class='cellBorder expandWidthCell theadBorder'>First Name</th>
					<th class='cellBorder expandWidthCell theadBorder'>Last Name</th>
					<th class='cellBorder expandWidthCell theadBorder'>Status</th>
					<th colspan='3' class='cellBorder expandWidthCell theadBorder'>Action</th>
				</thead>

				<tbody>";

				while ($row = $result->fetch_assoc())
				{	
					$highlightRowStatusDone = $row['status'] == 'Done' ? 'highlightDone' : 'noHighLight';
					$highlightRowStatusProcess = $row['status'] == 'Processing' ? 'highlightProcess' : 'noHighLight';
					$status = $row['update_status'] = 'Done' ? 'Done' : "Not Done";
					echo "<tr class='transparentCell cellBorder $highlightRowStatusDone $highlightRowStatusProcess'>
								<td class='cellBorder centerCell'>" . $employee_count . "</td>
								<td class='cellBorder'>" . $row['employee_id'] . "</td>
								<td class='cellBorder'>" . $row['first_name'] . "</td>
								<td class='cellBorder'>" . $row['last_name'] . "</td>
								<td class='cellBorder'>" . $row['status'] . "</td>
								<td class='cellBorder'>
									<form method='post'>
										<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
										<button type='submit' name='processing_status' class='cellBorder processButtonStyle' value='Processing'>Process</button>
									</form>
								</td>
								<td class='cellBorder'>
									<form method='post'>
										<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
										<button type='submit' name='update_status' class='cellBorder buttonStyle doneButtonStyle' value='" . $status . "'>Done</button>
									</form>
								</td>
								<td class='cellBorder'>
									<form method='post'>
										<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
										<button type='submit' name='refresh_status' class='cellBorder buttonStyle refreshButtonStyle' value=''>Refresh</button>
									</form>
								</td>
						</tr>";
						$employee_count++;
				}
				"</tbody>
				<tfoot>
				</tfood>
			</table>
		</div>";
}


//Count for showing the status
$totalEmp = $employee_count - 1;
$sumQuery = "SELECT COUNT(if(u.`status` = 'Done', 1, NULL)) AS TotalDone
			FROM {$dataTable} u";

$result = $conn->query($sumQuery);
if($result->num_rows > 0)
{
	while ($row = $result->fetch_assoc())
	{
		$totalComplete = $row['TotalDone'];
	}

	$totalIncomplete = $totalEmp - $totalComplete;
}

?>


	<div class='statusTableContainer'>
		<table border='1.0' class='statusTableStyle'>
				<thead>
					<th colspan='2' class='empStatusStyle'><i class="fa fa-file-text-o"></i>Completion Summary</th>
				</thead>
				<tbody>
					<tr class='statusStyle'>
						<td class='totalTextStyle'>Status</td>
						<td class='percentComplete'>
							<div class='progressBar'>
								<!-- Using script below to dynamic the divs creating based on customer quantity data -->
							</div>

								<script>
									const totalEmp = <?php echo $totalEmp; ?>;
									const totalComplete = <?php echo $totalComplete; ?>;
									const progressBar = document.querySelector('.progressBar');
									const toolTip = 

									progressBar.innerHTML = "";

									for(let i = 0; i < totalEmp; i++) {
										const div = document.createElement('div');
										if (i < totalComplete) {
											div.classList.add('percenPortionCompleted');
										} else {
											// div.classList.remove('completed');
										}
										progressBar.appendChild(div);
									}
								</script>

						</td>
					</tr>
					<tr class='statusStyle'>
						<td class='totalTextStyle'>Total </td>
						<td class='statusEmp'><?php echo $totalEmp ?> employees</td>
					</tr>
					<tr class='statusStyle'>
						<td class='doneTextStyle'>Completed </td>
						<td class='statusEmp empComplete'><?php echo $totalComplete ?> employees</td>
					</tr>
					<tr class='statusStyle'>
						<td class='notDoneTextStyle'>Pending</td>
						<td class='statusEmp empIncomplete'><?php echo $totalIncomplete ?> employees</td>
					</tr>
					<tr>
						<td class='textSize'>Generate CSV file</td>
						<td>
							<form method='post'>
								<button type='submit' name='cvs_File' class='generateButton'><i class="fa fa-cloud-download"></i></button>
							</form>
						</td>
					</tr>
					<tr>
						<td class='textSize'>Progress Report</td>
						<td>
							<button onclick="document.getElementById('myDialog').showModal()" class='generateButton'><i class='fas fa-exchange-alt'></i></button>
						</td>
					</tr>
				</tbody>
		</table>
	</div>



	<div class='policyTableContainer'>
		<table border='1.0' class='policyTableStyle'>
			<thead>
				<th colspan='2' class='cellBorder policyStyle'>Voucher Policy</th>
			</thead>
			<tbody>
				<tr>
					<td class='cellBorder blackText'>Voucher</td>
					<td class='cellBorder infoText'><?php echo $voucher?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Discount</td>
					<td class='cellBorder infoText'><?php echo $discount?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Tax</td>
					<td class='cellBorder infoText'><?php echo $taxAccept?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Tops|Bottoms</td>
					<td class='cellBorder infoText'><?php echo $topAndBottomColor?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Jackets|Vest|Sweater</td>
					<td class='cellBorder infoText'><?php echo $jacketVestSweaterColor?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Not Cover</td>
					<td class='cellBorder infoText'><?php echo $notCover?></td>
				</tr>

				<tr>
					<td class='cellBorder blackText'>Embroidery</td>
					<td class='cellBorder infoText'><?php echo $embroidery?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>Effective Date</td>
					<td class='cellBorder infoText effectiveDateStyle'><?php echo $effectiveDate?></td>
				</tr>
				<tr>
					<td class='cellBorder blackText'>End Date</td>
					<td class='cellBorder infoText lastDateStyle'><?php echo $endDate?></td>
				</tr>
				
			</tbody>
		</table>
	</div>
	
<?php
//=================Time Configure============== 
	//Voucher Start date initialized in the top
	//Voucher Last date initialized in the top
	$currentDate = new Datetime();
	

	if($currentDate < $voucherStartDate)
	{
		$voucherStatus = 'Not Started';
		$voucherStatusColor = 'orange';
		$remainingDate = 'N/A';
	} else {
		$interval = $voucherLastDate->diff($currentDate);
		// $remainingDate = $voucherLastDate <= $currentDate ? '-' . $interval->format('%a') : $interval->format('%a');
		// // echo $remainingDate;

		// $voucherStatus = $remainingDate <= 0 ? 'Expired' : 'Valid';
		// $voucherStatusColor = $voucherStatus == 'Valid' ? 'green' : 'red';

		if ($voucherLastDate < $currentDate) {
			$remainingDate = '-' . $interval->format('%a');
			$voucherStatus = 'Expired';
			$voucherStatusColor = 'red';
		} else {
			$remainingDate = $interval->format('%a');
			$voucherStatus = 'Valid';
			$voucherStatusColor = 'green';
		}
	}
?>

<div class="voucherContainer">
	<table border='1'>
		<th colspan="2" class='voucherTableStyle'>Voucher</th>
		<tr>
			<td class='expandWidthCell totalTextStyle'>Status</td>	
			<td class='expandWidthCell <?php echo $voucherStatusColor?>'><?php echo $voucherStatus; ?></td>
		</tr>
		<tr>
			<td class='totalTextStyle'>Days left</td>
			<td><?php echo $remainingDate . ' dates'?></td>
		</tr>
	</table>
	
</div>
<!-- <div class='csvStyle'>
	<form method='post'>
		<button type='submit' name='cvs_File' class='generateButton'>Generate CSV file</button>
	</form>
</div> -->


<!-- <div class='modalSection'> -->
<!-- <button onclick="document.getElementById('myDialog').showModal()">Status Changes</button> -->
<!-- </div> -->
<dialog id="myDialog">
  <h2 class="statusChangeSummary">Status Change Summary</h2>
  	<?php
  	$query_sql = "SELECT U.employee_id, U.first_name, U.last_name, S.`status`, DATE_Format(S.latest_change, '%h:%i%p %m/%d/%Y') AS latest_change
					FROM {$dataTable} U
					LEFT JOIN status_track S ON U.employee_id = S.employee_id
					ORDER BY U.first_name ASC, S.latest_change ASC";

$result = $conn->query($query_sql);

$employee_count = 1;
if($result->num_rows > 0)
{
	echo "<div class='tableContainer'>
			<table border='1.0'>
				<thead class='stickyModelTitle'>
					<th colspan='8' class='cellBorder titleTextStyle'>{$companyName}</th>
				</thead>
				<thead class='groupTitle'>
					<th colspan='8' class='cellBorder titleTextStyle'>{$group}</th>
				</thead>
				<thead class='groupSpecific'>
				<tr>
					<th>No.</th>
					<th>Employee ID</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Status</th>
					<th>Latest Change</th>
					</tr>
				</thead>

				<tbody>";
				$refNum = 0;
				$color = 'silver';
				while ($row = $result->fetch_assoc())
				{	
					if ($refNum != $row['employee_id'])
					{
						if ($color == 'white')
						{
							$color = 'silver';
						}
						else {
							$color = 'white';
						}

						$refNum = $row['employee_id'];
					} else {
						$color = $color;
						$refNum = $row['employee_id'];
					}
					echo "<tr class='$color'>
								<td>" . $employee_count . "</td>
								<td>" . $row['employee_id'] . "</td>
								<td>" . $row['first_name'] . "</td>
								<td>" . $row['last_name'] . "</td>
								<td>" . $row['status'] . "</td>
								<td>" . $row['latest_change'] . "</td>
								
						</tr>";
						$employee_count++;
				}
				"</tbody>
				<tfoot>
				</tfood>
			</table>
		</div>";
}
  	?>

  <div class='dialogCloseButton'><button onclick="document.getElementById('myDialog').close()">Close</button></div>
</dialog>






</body>
</html>