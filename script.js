// // ======= AJAX function to filter input from companies->groups->effective Dates ========= [Top]
//------> located in index.php at the code line # 397
document.getElementById('companyName').addEventListener('change', function() {
    var company = this.value;

    // Make an AJAX request to fetch the group names
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '?company=' + company, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // When the request is successful, populate the group dropdown
            document.getElementById('groupName').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});
document.getElementById('groupName').addEventListener('change', function() {
    var group = this.value;

    // Make an AJAX request to fetch the group names
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '?group=' + group, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // When the request is successful, populate the group dropdown
            document.getElementById('effectiveDate').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
});

// // ======= AJAX function to filter input from companies->groups->effective Dates ========= [Bottom]



// ======= Confimations button ========= [Top]
function confirmAddNewEmp(form) {
    var empId = form.employeeID.value;
    var fName = form.firstName.value;
    var lName = form.lastName.value;
    // return confirm("Once you confirm, this new employee will be added to the system. \nWould you like to proceed?");  
    var message = "Once you confirm, the following new employee will be added to the system:\n\n" +
    "Employee ID: " + empId + "\n" +
    "First Name: " + fName + "\n" +
    "Last Name: " + lName + "\n\n" +
    "Would you like to proceed?";
    return confirm(message);
}

// function confirmProcessing() {
//     return confirm("Are you sure you want to mark this item as 'Processing'? \nThis action will initiate the processing workflow.");    
// }

function confirmProcessing(form) {
    var empId = form.emp_id.value;  // Access the emp_id value from the hidden input
    var fName = form.first_name.value;
    var lName = form.last_name.value;
    var message = "Are you sure you want to mark status of following Employee as 'Processing'? \n\n" + 
    "Employee ID: " + empId + "\n" +
    "First Name: " + fName + "\n" + 
    "Last Name: " + lName + "\n\n" + 
    "This action will initiate the processing workflow.";
    return confirm(message); 
    // return confirm("Are you sure you want to mark this item as 'Processing'? \nThis action will initiate the processing workflow.");    
}

function confirmDone(form) {
    var empId = form.emp_id.value;  // Access the emp_id value from the hidden input
    var fName = form.first_name.value;
    var lName = form.last_name.value;
    // return confirm("Are you sure you want to mark this item as 'Done'? \nThis action will finalize the process and cannot be undone.");    
    var message = "Are you sure you want to mark the status of the following Employee as 'Done'?\n\n" +
    "Employee ID: " + empId + "\n" +
    "First Name: " + fName + "\n" +
    "Last Name: " + lName + "\n\n" +
    "This action will finalize the process and cannot be undone.";
return confirm(message);
}

function confirmRefresh(form) {
    var empId = form.emp_id.value;  // Access the emp_id value from the hidden input
    var fName = form.first_name.value;
    var lName = form.last_name.value;
    var message = "Do you want to reload the data and refresh the current status for the following Employee?\n\n" +
                  "Employee ID: " + empId + "\n" +
                  "First Name: " + fName + "\n" +
                  "Last Name: " + lName + "\n\n" +
                  "This action will refresh the data and update the current status.";
    return confirm(message); 
}

function confirmDelete(form) {
    var empId = form.emp_id.value;  // Access the emp_id value from the hidden input
    var fName = form.first_name.value;
    var lName = form.last_name.value;
    var message = "Are you certain you want to permanently delete the following Employee?\n\n" +
                  "Employee ID: " + empId + "\n" +
                  "First Name: " + fName + "\n" +
                  "Last Name: " + lName + "\n\n" +
                  "Once deleted, it cannot be recovered.";
    return confirm(message);
}
// ======= Confimations button ========= [Bottom]



function filterCustomerList() {
    var searchInput = document.getElementById('searchInput');
    var filter = searchInput.value.toLowerCase(); //Make it case insensitive (lower case)

    var tableContent = document.querySelector('#content table');
    var rows = tableContent.getElementsByTagName('tr');

    for (var i = 0; i < rows.length; i++) {
        var columns = rows[i].getElementsByTagName('td');
        if (columns.length > 0) {
            var firstNameCell = columns[3];
            if (firstNameCell) {
                var firstName = firstNameCell.textContent || firstNameCell.innerText;
                if (firstName.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    }
}





