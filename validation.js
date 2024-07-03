// Function to display message
function showMessage(message, type) {
    var messagesDiv = document.getElementById('messages');
    var messageElement = document.createElement('div');
    messageElement.className = 'message ' + type;
    messageElement.textContent = message;
    messagesDiv.appendChild(messageElement);
    // Automatically remove message after 3 seconds
    setTimeout(function () {
        messagesDiv.removeChild(messageElement);
    }, 3000);
}

// Function to update table content using AJAX
function updateEmployeeTable(page = 1) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'display_employees.php?page=' + page, true);
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 400) {
            var response = JSON.parse(xhr.responseText);
            document.getElementById('employeeTable').innerHTML = response.table;
            document.getElementById('pagination').innerHTML = response.pagination;
        } else {
            console.error('Request failed: ' + xhr.statusText);
        }
    };
    xhr.send();
}

// Add event listener to the form submit
document.getElementById('employeeForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    var formData = new FormData(this);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'employee_operations.php', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Add this header for AJAX requests
    xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 400) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === 'success') {
                showMessage(response.message, 'success');
                updateEmployeeTable(); // Update table after successful operation
                document.getElementById('employeeForm').reset(); // Reset form after successful operation
                document.getElementById('editing_employee_id').value = ''; // Reset editing_employee_id
                // Enable Add button and disable Update button
                document.querySelector('input[name="action"][value="Add"]').disabled = false;
                document.querySelector('input[name="action"][value="Update"]').disabled = true;
            } else {
                showMessage(response.message, 'error');
            }
        } else {
            console.error('Request failed: ' + xhr.statusText);
        }
    };
    xhr.send(formData);
});

// Function to handle edit action
function editEmployee(employeeId, firstName, lastName, department) {
    document.getElementById('editing_employee_id').value = employeeId;
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('first_name').value = firstName;
    document.getElementById('last_name').value = lastName;
    document.getElementById('department').value = department;

    document.querySelector('input[name="action"][value="Add"]').disabled = true;
    document.querySelector('input[name="action"][value="Update"]').disabled = false;
}

// Reset form handler
document.getElementById('employeeForm').addEventListener('reset', function () {
    document.getElementById('editing_employee_id').value = '';
    document.querySelector('input[name="action"][value="Add"]').disabled = false;
    document.querySelector('input[name="action"][value="Update"]').disabled = true;
});

// Handle delete operation
document.addEventListener('click', function (event) {
    if (event.target.matches('.delete')) {
        event.preventDefault();
        if (confirm('Are you sure you want to delete this record?')) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', event.target.getAttribute('href'), true);
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 400) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        showMessage(response.message, 'success');
                        updateEmployeeTable(); // Update table after successful operation
                    } else {
                        showMessage(response.message, 'error');
                    }
                } else {
                    console.error('Request failed: ' + xhr.statusText);
                }
            };
            xhr.send();
        }
    }
});

// Initial table load
updateEmployeeTable();
