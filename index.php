<?php
ob_start();
session_start(); // Start the session


require_once("./config.php");
$servername = DB_SERVER;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$db_name = DB_NAME;

$conn = new mysqli($servername, $username, $password, $db_name);

//Reset the group_id in the SESSION whenever filter not activated
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['filter'])) {
	unset($_SESSION['group_id']);
	unset($_SESSION['effective_date']);
	unset($_SESSION['end_date']);

}

//------------- [CSV generated Button Function] ------------- [Top]
$group_id_Token = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
$companyName_Token = isset($_SESSION['companyName']) ? $_SESSION['companyName'] : '';
$groupName_Token = isset($_SESSION['groupName']) ? $_SESSION['groupName'] : '';
$companyString = str_replace(' ', '', $companyName_Token);
$fileDownloadName = $companyString . '_' . $groupName_Token . '_Status.csv';
$effectiveDate_Token = $_SESSION['effective_date'];
$endDate_Token = $_SESSION['end_date'];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cvs_File']))
{
	header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'  . $fileDownloadName . '"');

    $output = fopen('php://output', 'w');
	fputcsv($output, [$companyName_Token, '', '', '', '', 'Effective Date', $effectiveDate_Token]);
	fputcsv($output, [$groupName_Token, '', '', '', '', 'End Date', $endDate_Token]);
    fputcsv($output, ['Employee ID', 'First Name', 'Last Name', 'Status', 'Time']); // CSV header

    $query_sql = $conn->prepare("SELECT *, DATE_Format(status_update_time, '%W %h:%i%p %m/%d/%Y') AS update_time FROM employee_status WHERE group_id = ? AND deleted = 'N' ORDER BY first_name ASC");
	$query_sql->bind_param('i', $group_id_Token);
	$query_sql->execute();
	$result = $query_sql->get_result();
	$order = 3;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
			if ($row['status'] == 'Done') {
				fputcsv($output, [$row['employee_id'], $row['first_name'], $row['last_name'], $row['status'], $row['update_time']]);
			} else if ($row['status'] == 'Processing') {
				fputcsv($output, [$row['employee_id'], $row['first_name'], $row['last_name']]);
			} else {
				fputcsv($output, [$row['employee_id'], $row['first_name'], $row['last_name'], $row['status']]);
			}
			$order++;
        }
		fputcsv($output, ['', '', '', '']);
		fputcsv($output, ['Total', "=CONCATENATE(COUNTA(B4:B" . $order . "), \" Employees\")", "=CONCATENATE(COUNTIF(D4:D" . $order . ", \"Done\"), \" Done\")"]);
		fputcsv($output, ['', '', "=CONCATENATE(COUNTIF(D4:D" . $order . ", \"\"), \" Pending\")"]);
    }
    fclose($output);
    exit();
} 
//------------- [CSV generated Button Function] ------------- [Bottom]
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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>


</head>
<body>
<?php


// Default the modal to hidden
$validClose = "style='display: none;'";
// Check if the modal should be displayed based on session state and URL parameters
if ($_SESSION['isNotice'] && !isset($_GET['filter'])) {
    // Show the modal if notice has been closed and no filter is set in URL
    $validClose = "style='display: block;'";
} else if ($_SESSION['isNotice'] && isset($_GET['filter'])) {
    // Hide the modal if notice is closed and filter is set in the URL
    $validClose = "style='display: none;'";
} else if (!$_SESSION['isNotice']) {
    // Show the modal if notice hasn't been closed yet
    $validClose = "style='display: block;'";
}

if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == 'refresh=true') {
// Check if the modal should be displayed based on session state and URL parameters
if ($_SESSION['isNotice'] && !isset($_GET['filter'])) {
    // Show the modal if notice has been closed and no filter is set in URL
    $validClose = "style='display: block;'";
}
}


// Handle the form submission to close the modal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['closeNoticeModal'])) {
    $validClose = "style='display: none;'";
    $_SESSION['isNotice'] = 'true';  // Mark the notice as closed
    // Redirect to the same page (this will process the updated session)
    // header("Location: " . $_SERVER['REQUEST_URI']);
    // exit();
}
          
if ($_SESSION['visited']) {

}
?>
<dialog id="noticeModal" <?php echo $validClose;?>>
	<div id='noticeModalContentCover'>
		<div id='noticeModalContent'>
			<div class='noticeModalContentAlignment'>
				<div class='topLine'></div>
				<div class='closeNoticeModalDiv'>
					<form action="" method="post">
						<button type='submit' name='closeNoticeModal' class="button okayNoticeButton">OK</button>
					</form>
				</div>

				<div id="dataSecurityInfo">
					<div class='noticeContent'>
						<div class='summaryTitleStyle' style='height: 35px;'>Summary:</div>
						<div>This page is part of the Metro Uniforms management platform designed to track and manage employee voucher status for Organizations (e.g., UT Southwestern), 
							specifically related to Groups (ARC/PFAS). 
						</div>
						<div style='height: 10px;'></div>
						<div>
						The interface provides an overview of employees, their voucher statuses, and completion progress. 
						Users can filter employees, track completion statuses, generate CSV reports, and view data visualizations about voucher usage and employee engagement.
						</div>
						
						<div class='keyFeatureTitleStyle'  style='height: 15px; padding-top: 10px;'>Key Features:</div>
						<div class='verticalSpace5px'></div>
						<div class='displayFlexRow'>
							<div class='fontBoldandWidth'> • Employee Tracking:</div> 
							<div class='leftAlignment'>See a list of employees along with their voucher statuses and progress.</div>
						</div>
						<div class='verticalSpace5px'></div>
						<div class='displayFlexRow'>
							<div class='fontBoldandWidth'> • Voucher Policy:</div> 
							<div class='leftAlignment'>Display voucher details like discount, effective dates, and available options.</div>
						</div>
						<div class='verticalSpace5px'></div>
						<div class='displayFlexRow'>
							<div class='fontBoldandWidth'> • Completion Summary:</div> 
							<div class='leftAlignment'>Overview of employee completion statuses, including pending and completed tasks.</div>
						</div>
						<div class='verticalSpace5px'></div>
						<div class='displayFlexRow'>
							<div class='fontBoldandWidth'> • Data Reporting:</div> 
							<div class='leftAlignment'>Option to generate CSV files of employee data.</div>
						</div>
						<div class='verticalSpace5px'></div>
						<div class='displayFlexRow'>
							<div class='fontBoldandWidth'> • Visualization:</div> 
							<div class='leftAlignment'>A graph of the most frequent weekdays for voucher usage, with an analysis of time behavior for male and female customers from selection to checkout.</div>
						</div>
						<div class='verticalSpace5px'></div>
						<div id='notice'>
							<div id='noticeContent'>
								<div style='height: 3px;'></div>
								<div class='noticeTitleStyle'>Notice:</div>
								<div class='verticalSpace5px'></div>
								<div class='italicStyle'>The employee data on this platform is fictional and used solely for the prototype. No real employee information is included, and all data is protected to respect my former company and comply with privacy guidelines.</div>
								<div class='verticalSpace5px'></div>
							</div>
						</div>
						<div class='noticeFooter' style='margin-top: 5%;'>Designed and Developed by Nam Ho</div>
                        <div class='noticeFooter'>Metro Uniforms Management Tool</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</dialog>

<div id='headNav'>
	<div id='menuNav'>
		<div class='menu1 menuButton'>
			<a href="./" class='menuSrcStyle dashMenuLink'>Dashboard
			</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu2 menuButton'>
			<a href="./" class='menuSrcStyle'>Manual</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu3 menuButton'>
			<a href="./" class='menuSrcStyle'>Setting</a>
		</div>
		<div class='paddingHorizontal10'></div>
		<div class='menu4 menuButton'>
			<a href="./itHelp" class='menuSrcStyle'>IT Help</a>
		</div>
	</div>
	<div id='workCompanyDiv'>
		<div class='workCompany'>
			<a href="./" class='workCompanyTitle'>
				<div><h1>Metro Uniforms</h1></div>
				<div class='metroLogo'></div>
			</a>
		</div>
		<div id='imagesNav'>
				<div class='pic1 pic'></div>
				<div class='pic2 pic'></div>
				<div class='pic3 pic'></div>
				<div class='pic4 pic'></div>
				<div class='pic5 pic'></div>
				<div class='pic6 pic'></div>
				<div class='pic7 pic'></div>
				<div class='pic8 pic'></div>
				<div class='pic9 pic'></div>
				<div class='pic10 pic'></div>
				<div class='pic11 pic'></div>
				<div class='pic12 pic'></div>
				<div class='pic13 pic'></div>
				<div class='pic14 pic'></div>
				<div class='pic15 pic'></div>
				<div class='pic16 pic'></div>
		</div>
	</div>
</div>

<div id='bodyNav'>
	<div id='leftNav'>
		<div id='filter'>
			<div class='cyanBorder'>
				<?php
					// Handle the AJAX request if a company is selected
					if (isset($_GET['company'])) {
						$company = $_GET['company'];
						$_SESSION['com'] = $company;

						// Prepare the SQL query to get group names for the selected company
						$sql = "SELECT DISTINCT group_name
								FROM group_benefits
								WHERE company = ?
								ORDER BY group_name ASC";

						$statement = $conn->prepare($sql);
						$statement->bind_param('s', $company);
						$statement->execute();
						$result = $statement->get_result();

						// Output the group options
						echo "<option value='' disabled selected>Select Group</option>";
						if ($result->num_rows > 0) {
							while ($row = $result->fetch_assoc()) {
								echo "<option value='" . $row['group_name'] . "'>" . $row['group_name'] . "</option>";
							}
						}

						$statement->close();
						exit();  // End the script to prevent further output
					}

					if (isset($_GET['group'])) {
						$group = $_GET['group'];
						$com = $_SESSION['com'];

						// Prepare the SQL query to get group names for the selected group
						$sql = "SELECT DISTINCT effective_date
								FROM group_benefits
								WHERE group_name = ?
								AND company = ?
								ORDER BY group_name ASC";

						$statement = $conn->prepare($sql);
						$statement->bind_param('ss', $group, $com);
						$statement->execute();
						$result = $statement->get_result();

						// Output the group options
						echo "<option value='' disabled selected>Select Group</option>";
						if ($result->num_rows > 0) {
							while ($row = $result->fetch_assoc()) {
								echo "<option value='" . $row['effective_date'] . "'>" . $row['effective_date'] . "</option>";
							}
						}

						$statement->close();
						exit();  // End the script to prevent further output
					}
				?>
				<div id='searchNav' class='centerCell'><i id='searchFilterIcon' class="fa fa-search"></i> Search Filter</div>
				<form method='get' class='filterMethod'>
					<div class='filterDetails'>
						<div class='centerInputRow'>
							<label for='companyName' class=''>Company</label>
							<select name="companyName" id='companyName' class='borderRadius6 filterInputStyle'>
								<option value="" disabled selected>Select Company</option>
									<?php
										$sql = "SELECT DISTINCT company
												from group_benefits
												ORDER BY company ASC";

										$statement = $conn->prepare($sql);
										// $statement->bind_param('i', $userId);
										$statement->execute();
										$result = $statement->get_result();

										// $result = $conn->query($sql);

										if($result->num_rows > 0)
										{
											while($row = $result->fetch_assoc())
											{
												if (isset($_GET['companyName']) && $row['company'] === $_GET['companyName']) {
													// If selected, mark the option as selected
													echo "<option value='" . $row['company'] . "' selected>" . $row['company'] . "</option>";
												} else {
													// Otherwise, just display the option without the selected attribute
													echo "<option value='" . $row['company'] . "'>" . $row['company'] . "</option>";
												}
											}
										}
										$statement->close();
									?>
							</select>
						</div>
					</div>

					<div class='filterDetails'>
						<div class='centerInputRow'>
							<label for='groupName' class=''>Group</label>
							<select name="groupName" id='groupName' class='borderRadius6 filterInputStyle'>
								<option value="" disabled selected>Select Group</option>
									<?php
										if (isset($_GET['companyName'])) {
											$companyName = $_GET['companyName'];
											$sql = "SELECT DISTINCT group_name FROM group_benefits WHERE company = ? ORDER BY group_name ASC";
											$stmt = $conn->prepare($sql);
											$stmt->bind_param('s', $companyName);
											$stmt->execute();
											$result = $stmt->get_result();

											while ($row = $result->fetch_assoc()) {
												$selected = (isset($_GET['groupName']) && $row['group_name'] === $_GET['groupName']) ? 'selected' : '';
												echo "<option value='" . $row['group_name'] . "' $selected>" . $row['group_name'] . "</option>";
											}
											$stmt->close();
										}
									?>
							</select>
						</div>
					</div>

					<div class='filterDetails'>
						<div class='centerInputRow'>
							<label for='effectiveDate' class=''>Effective Date</label>
							<select name="effectiveDate" id='effectiveDate' class='borderRadius6 filterInputStyle'>
								<option value="" disabled selected>Select Date</option>
									<?php
										if (isset($_GET['groupName'])) {
											$groupName = $_GET['groupName'];
											$sql = "SELECT DISTINCT effective_date FROM group_benefits WHERE group_name = ? ORDER BY effective_date ASC";
											$stmt = $conn->prepare($sql);
											$stmt->bind_param('s', $groupName);
											$stmt->execute();
											$result = $stmt->get_result();

											while ($row = $result->fetch_assoc()) {
												$selected = (isset($_GET['effectiveDate']) && $row['effective_date'] === $_GET['effectiveDate']) ? 'selected' : '';
												echo "<option value='" . $row['effective_date'] . "' $selected>" . $row['effective_date'] . "</option>";
											}
											$stmt->close();
										}
									?>
							</select>
						</div>
					</div>

					<div id='filterButton'>
						<div class='centerInputRow'>
							<BUTTON type='submit' name='filter' class='button generateButton'>Filter</BUTTON>
						</div>
					</div>
				</form>
			</div>
<script>// Filter functions of Ajax on script file [Code lines: 1->29]</script>
		</div>

			<?php
//------------- [Filter Button Function] ------------- [Top]
				$group_id = 0;
				$companyName = '';
				$groupName = '';
				
				if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['filter'])) {
					$companyName = $_GET['companyName'];
					$groupName = $_GET['groupName'];
					$effectiveDate = $_GET['effectiveDate'];

					$_SESSION['companyName'] = $_GET['companyName'];
					$_SESSION['groupName'] = $_GET['groupName'];
				
					// Fetch the group_id based on the selected filter options
					$stmt = $conn->prepare("SELECT group_id, effective_date, end_date FROM group_benefits WHERE company = ? AND group_name = ? AND effective_date = ?");
					$stmt->bind_param('sss', $companyName, $groupName, $effectiveDate);
					$stmt->execute();
					$result = $stmt->get_result();
				
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						$group_id = $row['group_id']; 
						$_SESSION['group_id'] = $row['group_id'];
						$_SESSION['effective_date'] = $row['effective_date'];
						$_SESSION['end_date'] = $row['end_date'];
					} else {
						// echo "<div class='warning spaceBottom centerCell red'>No matching Group found.</div>";
						echo "<script>
							document.addEventListener('DOMContentLoaded', function () {
							var filterDiv = document.getElementById('content');
							if (filterDiv) { 
								filterDiv.style.display = 'flex';
								filterDiv.style.justifyContent = 'center';
								filterDiv.style.height = '40%';
									// Check if the element exists
									let warningDiv = document.createElement('div');
									warningDiv.id = 'warningMes';
									warningDiv.className = 'warning spaceBottom centerCell red';
									warningDiv.innerHTML = 'No matching Group found.';
									warningDiv.style.height = '50%';
									warningDiv.style.width = '50%';
									warningDiv.style.color = 'red';
									warningDiv.style.fontWeight = 'bold';
									warningDiv.style.backgroundColor = 'white';
									warningDiv.style.backgroundImage = 'linear-gradient(to bottom, rgb(192, 207, 206) 0%, transparent 10%, rgb(255, 255, 255) 30%, transparent 40%, rgb(187, 212, 209) 75%, transparent 100%)';
									warningDiv.style.borderRadius = '300px';
									warningDiv.style.display = 'flex';
									warningDiv.style.justifyContent = 'space-evenly';
									warningDiv.style.alignItems = 'center';
									warningDiv.style.marginTop = '10%';
									warningDiv.style.border = '1px outset silver';
									filterDiv.appendChild(warningDiv);
							}

							var filter = document.getElementById('filter');
							var warningMes = document.getElementById('warningMes'); 
							if (warningMes) {
								warningMes.addEventListener('mouseover', (event) => {
									filter.style.boxShadow = '1px 1px 25px red';
									filter.style.transition = 'transform 1s';
									filter.style.transform = 'scaleX(1.1)';
								});

								warningMes.addEventListener('mouseout', (event) => {
									filter.style.boxShadow = '1px 1px 25px white';
									filter.style.transition = 'transform .5s';
									filter.style.transform = 'scaleX(1)';
								});
							}  
							});
						</script>";
					}
					$stmt->close();
				}
//------------- [Filter Button Function] ------------- [Bottom]



//------------- [Add New Employee Function] ------------- [Top]
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;

    if ($group_id != 0) {
        $employeeID = $_POST['employeeID'];

        // Check if employee already exists in the group
        $stmtIdCheck = $conn->prepare("SELECT employee_id FROM employee_status WHERE group_id = ? and employee_id = ?");
        $stmtIdCheck->bind_param('is', $group_id, $employeeID);
        $stmtIdCheck->execute();
        $empIdResult = $stmtIdCheck->get_result();

        if ($empIdResult->num_rows > 0) {
            // Employee ID already exists
            // echo "<div class='warning spaceBottom centerCell red'>The employee ID already exists!</div>";
			$_SESSION['employee_exist'] = "The employee ID already exists!";
            header("Location: " . $_SERVER['REQUEST_URI']); // Redirect to prevent resubmission
            exit(); // Ensure script stops after redirect
        } else {
            // If no duplicate ID is found, proceed to insert the new employee
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $status = "";  // Default status (empty string)

            // Insert the new employee into the database
            $stmt = $conn->prepare("INSERT INTO employee_status (employee_id, group_id, first_name, last_name, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sisss', $employeeID, $group_id, $firstName, $lastName, $status);

            if ($stmt->execute()) {
                // Redirect after successful insertion to prevent form resubmission
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit(); // Ensure script stops after redirect
            } else {
                echo "Error in adding new employee: " . $stmt->error;
            }
            $stmt->close();
        }

        $stmtIdCheck->close();
    }
}
//------------- [Add New Employee Function] ------------- [Bottom]	



//------------- [Processing Button Function] ------------- [Top]
				
				if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['processing_status'])) {
					$group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
					// Ensure that both emp_id and group_id are set
					if (isset($_POST['emp_id']) && $group_id != 0) {
						$em_id = $_POST['emp_id'];
						$status = 'Processing';
				
						// Prepare the UPDATE query
						$update_sql = $conn->prepare("UPDATE employee_status SET status = ? WHERE employee_id = ? AND group_id = ?");
						$update_sql->bind_param('ssi', $status, $em_id, $group_id);
				
						if ($update_sql->execute()) {
							// Prepare the INSERT query for the status tracker
							$statusChange = $conn->prepare("INSERT INTO status_tracker (employee_id, group_id, status, latest_change) VALUES (?, ?, ?, NOW())");
							$statusChange->bind_param('sis', $em_id, $group_id, $status);
				
							if ($statusChange->execute()) {
								// Redirect after successful query execution
								header("Location: " . $_SERVER['REQUEST_URI']);
								// echo "<script>window.location.reload();</script>";
								exit();
							} else {
								// Handle insertion failure
								echo "Error in status tracker insertion: " . $conn->error;
							}
						} else {
							// Handle update failure
							echo "Error in status update: " . $conn->error;
						}
					} else {
						// Handle missing parameters
						echo "Required fields are missing. Group ID: " . $group_id;
					}
				}
//------------- [Processing Button Function] ------------- [Bottom]




//------------- [Done Button Function] ------------- [Top]
				if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
					$group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
					// Ensure that both emp_id and group_id are set
					if (isset($_POST['emp_id']) && $group_id != 0) {
						$em_id = $_POST['emp_id'];
						$status = 'Done';
				
						// Prepare the UPDATE query
						$update_sql = $conn->prepare("UPDATE employee_status SET status = ?, status_update_time = CURRENT_TIMESTAMP WHERE employee_id = ? AND group_id = ?");
						$update_sql->bind_param('ssi', $status, $em_id, $group_id);
				
						if ($update_sql->execute()) {
							// Prepare the INSERT query for the status tracker
							$statusChange = $conn->prepare("INSERT INTO status_tracker (employee_id, group_id, status, latest_change) VALUES (?, ?, ?, NOW())");
							$statusChange->bind_param('sis', $em_id, $group_id, $status);
				
							if ($statusChange->execute()) {
								// Redirect after successful query execution
								header("Location: " . $_SERVER['REQUEST_URI']);
								exit();
							} else {
								// Handle insertion failure
								echo "Error in status tracker insertion: " . $conn->error;
							}
						} else {
							// Handle update failure
							echo "Error in status update: " . $conn->error;
						}
					} else {
						// Handle missing parameters
						echo "Required fields are missing. Group ID: " . $group_id;
					}
				}
//------------- [Done Button Function] ------------- [Bottom]




//------------- [Refreshing Button Function] ------------- [Top]
				if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refresh_status'])) {
					$group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
					// Ensure that both emp_id and group_id are set
					if (isset($_POST['emp_id']) && $group_id != 0) {
						$em_id = $_POST['emp_id'];
						$status = '';
				
						// Prepare the UPDATE query
						$update_sql = $conn->prepare("UPDATE employee_status SET status = ? WHERE employee_id = ? AND group_id = ?");
						$update_sql->bind_param('ssi', $status, $em_id, $group_id);
				
						if ($update_sql->execute()) {
							// Prepare the INSERT query for the status tracker
							$statusChange = $conn->prepare("INSERT INTO status_tracker (employee_id, group_id, status, latest_change) VALUES (?, ?, ?, NOW())");
							$statusTracker = 'Refresh';
							$statusChange->bind_param('sis', $em_id, $group_id, $statusTracker);
				
							if ($statusChange->execute()) {
								// Redirect after successful query execution
								header("Location: " . $_SERVER['REQUEST_URI']);
								exit();
							} else {
								// Handle insertion failure
								echo "Error in status tracker insertion: " . $conn->error;
							}
						} else {
							// Handle update failure
							echo "Error in status update: " . $conn->error;
						}
					} else {
						// Handle missing parameters
						echo "Required fields are missing. Group ID: " . $group_id;
					}
				}
//------------- [Refreshing Button Function] ------------- [Bottom]



//------------- [Deleting Button Function] ------------- [Top]
				if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_status'])) {
					$group_id = isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 0;
					// Ensure that both emp_id and group_id are set
					if (isset($_POST['emp_id']) && $group_id != 0) {
						$em_id = $_POST['emp_id'];
						$deleted = 'Y';
				
						// Prepare the UPDATE query
						$update_sql = $conn->prepare("UPDATE employee_status SET deleted = ? WHERE employee_id = ? AND group_id = ?");
						$update_sql->bind_param('ssi', $deleted, $em_id, $group_id);
				
						if ($update_sql->execute()) {
							// Prepare the INSERT query for the status tracker
							$statusChange = $conn->prepare("INSERT INTO status_tracker (employee_id, group_id, status, latest_change) VALUES (?, ?, ?, NOW())");
							$statusTracker = 'Deleted';
							$statusChange->bind_param('sis', $em_id, $group_id, $statusTracker);
				
							if ($statusChange->execute()) {
								// Redirect after successful query execution
								header("Location: " . $_SERVER['REQUEST_URI']);
								exit();
							} else {
								// Handle insertion failure
								echo "Error in status tracker insertion: " . $conn->error;
							}
						} else {
							// Handle update failure
							echo "Error in status update: " . $conn->error;
						}
					} else {
						// Handle missing parameters
						echo "Required fields are missing. Group ID: " . $group_id;
					}
				}
//------------- [Deleting Button Function] ------------- [Bottom]

				?>
				
		
		





			<?php
				$query_sql = $conn->prepare("SELECT * FROM employee_status WHERE group_id = ? AND deleted = 'N' ORDER BY first_name ASC");
				$query_sql->bind_param('i', $group_id);
				$query_sql->execute();
				$result = $query_sql->get_result();
				// $result = $conn->query($query_sql);
				$employee_count = 1;
				if($result->num_rows > 0)
				{
					while ($row = $result->fetch_assoc())
					{	
							$employee_count++;
					}
					
				}
				//Count for showing the status
				$totalEmp = $employee_count - 1;
				$sumQuery = $conn->prepare("SELECT COUNT(if(u.`status` = 'Done', 1, NULL)) AS TotalDone
							FROM employee_status u
							WHERE group_id = ? AND deleted = 'N'");

				$sumQuery->bind_param('i', $group_id);
				$sumQuery->execute();
				$result = $sumQuery->get_result();
				// $result = $conn->query($sumQuery);
				if($result->num_rows > 0)
				{
					while ($row = $result->fetch_assoc())
					{
						$totalComplete = $row['TotalDone'];
					}
				
					$totalIncomplete = $totalEmp - $totalComplete;
				}
				$sumQuery->close();
			?>

<?php
				if($group_id != 0) {
					$stmt = $conn->prepare("SELECT effective_date, end_date from group_benefits
					WHERE group_id = ?");
					$stmt->bind_param('i', $group_id);
					$stmt->execute();
						$result = $stmt->get_result();
						if ($result->num_rows > 0) {
							$row = $result->fetch_assoc();
							$voucherStartDate = $row['effective_date'];
							$voucherLastDate = $row['end_date'];
						} else {
							echo "<div class='warning bottomCell centerCell red'>No matching Effective Date found.</div>";
						}
					$stmt->close();
				}
					
//=================Time Configure============== 
	//Voucher Start date initialized in the top
	//Voucher Last date initialized in the top
	$currentDate = new Datetime();
	$voucherStartDate = new Datetime($voucherStartDate);
	$voucherLastDate = new Datetime($voucherLastDate);

	if($currentDate < $voucherStartDate)
	{
		$voucherStatus = 'Not Started';
		$voucherStatusColor = 'orange';
		$remainingDate = 'N/A';
	} else {
		$interval = $voucherLastDate->diff($currentDate);

		if ($voucherLastDate < $currentDate) {
			// $remainingDate = '-' . $interval->format('%a');
			$remainingDate = '0';
			$voucherStatus = 'Expired';
			$voucherStatusColor = 'red';
			$validVoucherDateColor = 'blackWordColor';
		} else {
			$remainingDate = $interval->format('%a');
			$voucherStatus = 'Valid';
			$voucherStatusColor = 'green';
			$validVoucherDateColor = 'blackWordColor';
		}
	}
?>

		<div id='voucherAndSummaryTableNav'>
			<div class='cyanBorder'>
				<table border='.2' class='voucherSummaryTable'>
					<th colspan="2" class='voucherTracker'>
						<i id='calendarVoucherTrackerIcon' class="fa fa-calendar"></i> Voucher Tracker
					</th>
						<tr>
							<td class='subtitleCellSize subTitleCellBackG boldText voucherSummaryCellBlur'>Status</td>	
							<td class='dataTableSize centerCell  <?php echo $voucherStatusColor;?>'><?php echo $voucherStatus; ?></td>
						</tr>
						<tr>
							<td class='boldText subTitleCellBackG voucherSummaryCellBlur'>Days left</td>
							<td class='dataTableSize boldText voucherStatusCell voucherSummaryCellBlur whiteCell-centerCell <?php echo $validVoucherDateColor;?>'><?php echo $remainingDate . " dates";?></td>
						</tr>
					<th colspan='2' class='completeSumColor'><i class="fa fa-file-text-o"></i> Completion Summary</th>
						<tr class=''>
							<td class='boldText subTitleCellBackG voucherSummaryCellBlur'>Status</td>
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
						<tr class=''>
							<td class='boldText subTitleCellBackG voucherSummaryCellBlur'>Total </td>
							<td class='dataTableSize boldText rightTextCell voucherSummaryCellBlur whiteCell'><?php echo $totalEmp ?> employees</td>
						</tr>
						<tr class=''>
							<td class='doneTextStyle boldText subTitleCellBackG voucherSummaryCellBlur'>Completed </td>
							<td class='dataTableSize empComplete rightTextCell voucherSummaryCellBlur whiteCell'><?php echo $totalComplete ?> employees</td>
						</tr>
						<tr class=''>
							<td class='notDoneTextStyle boldText subTitleCellBackG voucherSummaryCellBlur'>Pending</td>
							<td class='dataTableSize empIncomplete rightTextCell voucherSummaryCellBlur whiteCell'><?php echo $totalIncomplete ?> employees</td>
						</tr>
						<tr>
							<td class='boldText subTitleCellBackG voucherSummaryCellBlur'>Generate CSV file</td>
							<td class='dataTableSize centerCell voucherSummaryCellBlurFile'>
								<form method='post'>
									<button type='submit' name='cvs_File' class='generateButton'><i class="fa fa-cloud-download"></i></button>
								</form>
							</td>
						</tr>
						<tr>
							<td class='boldText subTitleCellBackG voucherSummaryCellBlur'>Progress Report</td>
							<td class='dataTableSize centerCell voucherSummaryCellBlurFile'>
								<button onclick="document.getElementById('myDialog').showModal()" class='generateButton'><i class='fas fa-exchange-alt'></i></button>
							</td>
						</tr>
				</table>
			</div>
		</div>

		<div class='paddingVertical10'></div>
		<div id='addEmpNav'>
			<div class='cyanBorder'>
			<div id='addEmpTitle'><i class="fa fa-user-plus"></i> Add New Employee</div>
			<form method='post' class='addEmpMethod' onsubmit='return confirmAddNewEmp(this);'>
				<div class='addEmpDetails'>
					<div class='addEmpTitleInput'>
						<label for='employeeID' type='text' class='addEmpTitle'>Employee ID</label>
						<input type="text" name="employeeID" id='employeeID' class='boxInput borderRadius6 addEmpInputStyle' required>
					</div>
				</div>
				
				<div class='addEmpDetails'>
					<div class='addEmpTitleInput'>
						<label for='firstName' type='text' class='addEmpTitle'>First Name</label>
						<input type="text" name="firstName" id='firstName' class='boxInput borderRadius6 addEmpInputStyle' required>
					</div>
				</div>

				<div class='addEmpDetails'>
					<div class='addEmpTitleInput'>
						<label for='lastName' type='text' class='addEmpTitle'>Last Name</label>
						<input type="text" name="lastName" id='lastName' class='boxInput borderRadius6 addEmpInputStyle' required>
					</div>
				</div>

				<div id='addEmpButton'>
					<div class='centerInputRow'>
						<BUTTON type='submit' name='add' class='button generateButton'>Add</BUTTON>
					</div>
				</div>
			</form>
									</div>
					<?php
					//This code for addemp function to show up the message about employee exist already
					if (isset($_SESSION['employee_exist'])) {
						echo "<div class='warning spaceBottom centerCell red'>" . $_SESSION['employee_exist'] . "</div>";
						unset($_SESSION['employee_exist']);
					}
					?>

		</div>
	</div>



<?php
$groupNameActual = '';
if ($groupName == 'ARC') {
	$groupNameActual = 'Animal Resource Center';
} else if($groupName == 'PFAS') {
	$groupNameActual = 'Patient Financial And Services';
}
?>


	<div id='middleNav'>
		<div class='cyanBorder'>
			<div id='header'>
				<div class='headerManualCell centerCell headerCellStyle'> <?php echo $companyName; ?></div>
				<div class='headerManualCell centerCell headerCellStyle'>
					<div class='groupNameHeader'><i class='fas fa-notes-medical'></i> <?php echo $groupNameActual; ?></div>
					<div id="searchBox" class='searchLastNameHeader'>
						<i id='searchLastNameIcon' class="fa fa-search"></i>
						<div class="paddingHorizontal10"></div>
						<input type="text" id="searchInput" name='searchLastName' class='searchInputClass' placeholder="Search by Last Name..." onkeyup="filterCustomerList()">
					</div>
				</div>
				<table  class='dataTableSize'>
							<td class='numPercent centerCell headerTextStyle headerCellStyle'>#</td>
							<td class='eidPercent centerCell headerTextStyle headerCellStyle'>Employee Id</td>
							<td class='fnPercent centerCell headerTextStyle headerCellStyle'>First Name</td>
							<td class='lnPercent centerCell headerTextStyle headerCellStyle'>Last Name</td>
							<td class='statusPercent centerCell headerTextStyle headerCellStyle'>Status</td>
							<td class='actionsPercent centerCell headerTextStyle headerCellStyle'>Actions</td>
				</table>
			</div>



			<div id='content'>
				<?php
					$query_sql = "SELECT * FROM employee_status WHERE group_id = $group_id AND deleted = 'N' ORDER BY first_name ASC";
					$result = $conn->query($query_sql);
					$employee_count = 1;
					if($result->num_rows > 0)
					{
						echo "<table border='1.0' class='dataTableSize'>";
						while ($row = $result->fetch_assoc())
						{	
							$highlightRowStatusDone = $row['status'] == 'Done' ? 'highlightDone' : 'noHighLight';
							$highlightRowStatusProcess = $row['status'] == 'Processing' ? 'highlightProcess' : 'noHighLight';
							$status = $row['update_status'] == 'Done' ? 'Done' : 'Not Done';
							// $doneSymbolStatus = $row['status'] == 'Done' ? "<i class='far fa-check-square'></i>" : '';
							// $doneSymbolStatus = $row['status'] == 'Processing' ? "<i class='fa fa-gear fa-spin'></i>" : '';
							
							//Function to filter out the status types, disable button if its status already registered.
							//E.g. If Done -> disable Done button (prevent many transaction of Done multiple times in a row). Other buttons still available for clicking.
							if ($row['status'] == 'Done') {
								$doneSymbolStatus = "<i class='far fa-check-square'></i>";
								$disabledDoneButton = "disabled style='cursor: default;'";
								$disabledProcessButton = "";
								$disabledRefreshButton = "";
							} else if ($row['status'] == 'Processing') {
									$doneSymbolStatus = "<i class='fa fa-gear fa-spin'></i>";
									$disabledProcessButton = "disabled style='cursor: default;'";
									$disabledDoneButton = "";
									$disabledRefreshButton = "";
							} else {
									$doneSymbolStatus = '';
									$disabledProcessButton = "";
									$disabledDoneButton = "";
									$disabledRefreshButton = "disabled style='cursor: default;'";
							}
							echo "<tr class='contentRow transparentCell cellBorder $highlightRowStatusDone $highlightRowStatusProcess'>
										<td class='numPercent cellBorder centerCell'>" . $employee_count . "</td>
										<td class='eidPercent centerCell cellBorder'>" . $row['employee_id'] . "</td>
										<td class='fnPercent centerCell cellBorder'>" . $row['first_name'] . "</td>
										<td class='lnPercent centerCell cellBorder'>" . $row['last_name'] . "</td>
										<td class='statusPercent centerCell cellBorder'>" . $row['status'] . " " . $doneSymbolStatus . "</td>
										<td class='processPercent centerCell cellBorder buttonCellBackground'>
											<form method='post' onsubmit='return confirmProcessing(this);'>
												<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
												<input type='hidden' name='first_name' value='" . $row['first_name'] . "'>
												<input type='hidden' name='last_name' value='" . $row['last_name'] . "'>
												<button type='submit' name='processing_status' class='cellBorder processButtonStyle' value='Processing' " . $disabledProcessButton . " >Process</button>
											</form>
										</td>
										<td class='donePercent centerCell cellBorder buttonCellBackground'>
											<form method='post' onsubmit='return confirmDone(this);'>
												<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
												<input type='hidden' name='first_name' value='" . $row['first_name'] . "'>
												<input type='hidden' name='last_name' value='" . $row['last_name'] . "'>
												<button type='submit' name='update_status' class='cellBorder buttonStyle doneButtonStyle' value='" . $status . "' " . $disabledDoneButton . ">
													<i class='fas fa-check'></i>
												</button>
											</form>
										</td>
										<td class='refreshPercent centerCell cellBorder buttonCellBackground'>
											<form method='post' onsubmit='return confirmRefresh(this);'>
												<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
												<input type='hidden' name='first_name' value='" . $row['first_name'] . "'>
												<input type='hidden' name='last_name' value='" . $row['last_name'] . "'>
												<button type='submit' name='refresh_status' class='cellBorder buttonStyle refreshButtonStyle' value='' " . $disabledRefreshButton . ">
													<i class='fa fa-refresh refreshIcon'></i>
												</button>
											</form>
										</td>
										<td class='refreshPercent centerCell cellBorder buttonCellBackground'>
											<form method='post' onsubmit='return confirmDelete(this);'>
												<input type='hidden' name='emp_id' value='" . $row['employee_id'] . "'>
												<input type='hidden' name='first_name' value='" . $row['first_name'] . "'>
												<input type='hidden' name='last_name' value='" . $row['last_name'] . "'>
												<button type='submit' name='delete_status' class='cellBorder buttonStyle deleteButtonStyle' value=''>
													<i class='fa fa-trash'></i>
												</button>
											</form>
										</td>
								</tr>";
								$employee_count++;
						}
						
					}
				
				
				//Count for showing the status
				$totalEmp = $employee_count - 1;
				$sumQuery = "SELECT COUNT(if(u.`status` = 'Done', 1, NULL)) AS TotalDone
							FROM employee_status u";
				
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
				</table>
			</div>
		</div>
	</div>



	<div id='rightNav'>
		<div class='policyCover'>
			<div class='cyanBorder'>
				<div id='policyTable'>
				<?php
				$stmt = $conn->prepare("SELECT * from group_benefits WHERE group_id = ?");
				$stmt->bind_param('i', $group_id);
				$stmt->execute();
				$result = $stmt->get_result();
					while ($row = $result->fetch_assoc()) {
						$freeTax = $row['free_tax'] == 'Y' ? 'Yes' : "No"; 
						$voucherPolicy = $row['voucher'];
						$discountPolicy = $row['discount'];
						$freeTax;
						$topsBottomsPolicy = $row['tops_bottoms'];
						$jacketsVestSweatersPolicy = $row['jackets_vest_sweaters'];
						$notCoverPolicy = $row['not_cover']; 
						$embroideryPolicy = $row['embroidery'];
						$embroideryPosPolicy = $row['embroidery_pos'];
						$effectiveDatePolicy = $row['effective_date'];
						$endDatePolicy = $row['end_date'];
					}
					$stmt->close();
					$dollarSign = $voucherPolicy ? '$' : '';
					$percentageSign = $discountPolicy ? '%' : '';

					echo "<table border='.2' class='policyTableStyle borderRadius6'>
						<thead>
							<th colspan='2' class='cellBorder policyStyle voucherPolicyTitle'><i class='fas fa-book'></i> Voucher Policy</th>
						</thead>
							<tr>
								<td class='boldText subTitleCellBackG'>Voucher</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $dollarSign . $voucherPolicy . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>Discount</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $percentageSign . $discountPolicy . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>Free Tax</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $freeTax . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>Tops|Bottoms Color</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $topsBottomsPolicy . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>Jackets|Vest|Sweater Color</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell jacketTopPadding'>" . $jacketsVestSweatersPolicy . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>Not Cover</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $notCoverPolicy . "</div></td>
							</tr>

							<tr>
								<td class='boldText subTitleCellBackG'>Embroidery</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $embroideryPolicy . "</div></td>
							</tr>

							<tr>
								<td class='boldText subTitleCellBackG'>Embroidery Position</td>
								<td class='dataTableSize blueText rightCell policyContentCoverDiv'><div class='whiteCell'>" . $embroideryPosPolicy . "</div></td>
							</tr>

							<tr>
								<td class='boldText subTitleCellBackG'>Effective Date</td>
								<td class='dataTableSize effectiveDateColor rightCell effectiveDateStyle policyContentCoverDiv'><div class='whiteCell'>" . $effectiveDatePolicy . "</div></td>
							</tr>
							<tr>
								<td class='boldText subTitleCellBackG'>End Date</td>
								<td class='dataTableSize endDateColor rightCell lastDateStyle policyContentCoverDiv'><div class='whiteCell'>" . $endDatePolicy . "</div></td>
							</tr> 
					</table>";
					?>
				</div>
			</div>
		</div>

		<?php
			$query_sql = $conn->prepare("SELECT DAYNAME(status_update_time) AS weekday_name, count(*) AS weekday_count
			FROM employee_status
			WHERE group_id = ? AND status = ?
			GROUP BY DAYNAME(status_update_time)
			ORDER BY FIELD(DAYNAME(status_update_time), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') ASC");

			$statusForChart = 'Done';
			$query_sql->bind_param('is', $group_id, $statusForChart);
			$query_sql->execute();
			$result = $query_sql->get_result();

			// Prepare data array
			$data = [];
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$data[] = [$row['weekday_name'], (int)$row['weekday_count']];
				}
			}
			$query_sql->close();

			// Convert PHP array to JSON
			$jsonData = json_encode($data);
		?>

<!-- Google Chart Div -->
 <div id='chart'>
 	<div class='cyanBorder'>
		<div id="chart_div">

		</div>
	</div>
 </div>


<script type="text/javascript">
    // Get the data from PHP and pass it to JavaScript
var chartData = <?php echo $jsonData; ?>;

// Load Google Charts
google.charts.load('current', {'packages':['corechart', 'bar']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    // Get the data from PHP
    var chartData = <?php echo $jsonData; ?>;

    // Find the maximum visit count
	let maxVisits = 0;
	for (let i = 0; i < chartData.length; i++) {
		if (chartData[i][1] > maxVisits) {
			maxVisits = chartData[i][1];
		}
	}

	// Create the data table with an additional column for style
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Weekday');
	data.addColumn('number', 'Visits');
	data.addColumn({ type: 'string', role: 'style' }); //Style column

	for (let i = 0; i < chartData.length; i++) {
		const weekday = chartData[i][0];
		const visits = chartData[i][1];
		let style = '';

		if (visits === maxVisits) {
			style = 'color: #FF4500';
		} else {
			style = 'color: #1E90FF';
		}
		data.addRow([weekday, visits, style]);
	}

    // Options for the chart (remove the global colors option)
    var options = {
        title: 'Most Visited Weekday',
        titleTextStyle: {
            fontSize: 14,
			color: 'rgb(9, 70, 193)',
        },
        backgroundColor: 'transparent',
        chartArea: {width: '55%'},
        vAxis: {
            title: 'Number of Visits',
            minValue: 0,
            titleTextStyle: {
                bold: true
            }
        },
        hAxis: {
            title: 'Weekday',
            titleTextStyle: {
                bold: true
            }
        },
		legend: { position: 'none' },
    };

    // Create and draw the chart using ColumnChart
    var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
    chart.draw(data, options);
}


// ======= Refresh Icon change from static to spin ========= [Top]
const contentRows = document.querySelectorAll('.contentRow');
const refreshButton = document.querySelectorAll('.refreshButtonStyle');
const refreshIcon = document.querySelectorAll('.refreshIcon');
const checkIcon = document.querySelectorAll('.fa-check');
const trashIcon = document.querySelectorAll('.fa-trash');

if (contentRows.length === refreshButton.length && refreshButton.length === refreshIcon.length) {
    contentRows.forEach((row, index) => {
        row.addEventListener('mouseover', function() {
            if (!refreshButton[index].disabled) {
                refreshIcon[index].classList.add('fa-spin');
            }
            trashIcon[index].style.color = "rgb(217, 89, 42)";
        });

        row.addEventListener('mouseleave', function() {
            refreshIcon[index].classList.remove('fa-spin');
            trashIcon[index].style.color = "rgb(99, 98, 98)";
        });
    });
} else {
    console.error('Mismatch between contentRows, refreshButton, and refreshIcon elements.');
}

// ======= Refresh Icon change from static to spin ========= [Bottom]
    </script>




		
	</div>
</div>

<div id='footer'>
	<div class='expandVertical'></div>
	<div class='organizationLogo'>
		<!-- <div class='expandSpace'></div> -->
		<img src="./logo/metroScrubLogo.jpg" alt="" onclick="window.open('http://www.metrouniforms.com', 'blank');" style='cursor: pointer;'>
		<div class='expandSpace'></div>
		<img src="./logo/mentclinicLogo.jpg" alt="">
		<div class='expandSpace'></div>
		<img src="./logo/utswLogo.jpg" alt="">
		<div class='expandSpace'></div>
		<img src="./logo/barcoLogo.webp" alt="">
	</div>

	<!-- <div class='centerCell bottomCell'> -->
		
	<!-- <div>Support me by subscribing YouTube channel 
		<a href='https://www.youtube.com/channel/UCEqlEvuQtuy-zVoF61hawKw' target='_blank'>
			<i class='fab fa-youtube'></i> Picky Cat Soul
		</a>
	</div> -->
	<div class='footerContent'>&copy; <?php echo date('Y');?> Metro Uniforms. All rights reserved. Created by Nam Ho.</div>

</div>

<dialog id="myDialog">
	<div class='cyanBorder' style='width: 100%; height: 100%; border-radius: 4px;'>
<div class='dialogCloseButton'><button onclick="document.getElementById('myDialog').close()" class='button progressButton'>Close</button></div>
  <div id='statusSummary'><h2 class="statusChangeSummary"><i class="fas fa-tasks"></i> Status Change Summary</h2></div>
  	<?php
  	$query_sql = "SELECT U.employee_id, U.first_name, U.last_name, S.`status`, DATE_Format(S.latest_change, '%W %h:%i%p %m/%d/%Y') AS latest_change
					FROM employee_status U
					LEFT JOIN status_tracker S ON U.employee_id = S.employee_id AND U.group_id = S.group_id
					WHERE U.group_id = $group_id
					ORDER BY U.first_name ASC, S.latest_change ASC";

$result = $conn->query($query_sql);
$employee_count = 1;
if($result->num_rows > 0)
{
	echo "<div class='tableContainer'>
			<table border='1.0'>
				<thead class='stickyModelTitle'>
					<th colspan='8' class='cellBorder titleTextStyle'>" . $companyName . "</th>
				</thead>
				<thead class='groupTitle'>
					<th colspan='8' class='cellBorder titleTextStyle'>" . $groupName . "</th>
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
								<td class='centerCell'>" . $employee_count . "</td>
								<td class='centerCell'>" . $row['employee_id'] . "</td>
								<td class='centerCell'>" . $row['first_name'] . "</td>
								<td class='centerCell'>" . $row['last_name'] . "</td>
								<td class='centerCell'>" . $row['status'] . "</td>
								<td class='centerCell'>" . $row['latest_change'] . "</td>
								
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

</div>
</dialog>

<div id="smallScreenNotice">
    <div class="noticeContentBox">
        <h2>Limited Mobile Support</h2>
        <p>
            This site is best viewed on a <strong>laptop or desktop</strong> for full access to all features and functions.
        </p>
        <p>
            We appreciate your understanding and look forward to serving you on a larger screen.
        </p>
		<p class='orangeWords'>
            Please switch to a larger device for the full experience.
        </p>
        <p class="noticeSignature">— Nam Ho | Metro Uniforms Management Tool</p>
    </div>
</div>



<script src='script.js'></script>
</body>
</html>
<?php
ob_end_flush();
?>