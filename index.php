<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "knoxed";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = '';

// Pagination settings
$results_per_page = 5; // Number of records per page

// Calculate pagination parameters
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$start_from = ($page - 1) * $results_per_page;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = $_POST['employee_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $department_id = $_POST['department'];
    $editing_employee_id = $_POST['editing_employee_id'];

    if ($_POST['action'] == 'Add') {
        $sql = "INSERT INTO employees (employee_id, first_name, last_name, department_id) VALUES ('$employee_id', '$first_name', '$last_name', '$department_id')";
        if ($conn->query($sql) === TRUE) {
            $response = 'Record added successfully!';
        } else {
            $response = "Error adding record: " . $conn->error;
        }
    } elseif ($_POST['action'] == 'Update') {
        if (!empty($editing_employee_id)) {
            $sql = "UPDATE employees SET employee_id='$employee_id', first_name='$first_name', last_name='$last_name', department_id='$department_id' WHERE employee_id='$editing_employee_id'";
            if ($conn->query($sql) === TRUE) {
                $response = 'Record updated successfully!';
            } else {
                $response = "Error updating record: " . $conn->error;
            }
        }
    }
    $_SESSION['response'] = $response;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $employee_id = $_GET['employee_id'];
    $sql = "DELETE FROM employees WHERE employee_id='$employee_id'";
    if ($conn->query($sql) === TRUE) {
        $response = 'Record deleted successfully!';
    } else {
        $response = "Error deleting record: " . $conn->error;
    }
    $_SESSION['response'] = $response;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$message = '';
if (isset($_SESSION['response'])) {
    $message = $_SESSION['response'];
    unset($_SESSION['response']);
}

// Fetch total number of records
$sql = "SELECT COUNT(*) AS total FROM employees";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_records = $row['total'];

// Calculate total pages
$total_pages = ceil($total_records / $results_per_page);

// Fetch records with pagination
$sql = "SELECT employees.employee_id, employees.first_name, employees.last_name, employees.department_id, departments.name AS department_name
        FROM employees
        INNER JOIN departments ON employees.department_id = departments.id
        LIMIT $start_from, $results_per_page";


$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
        }

        #messages {
            margin-bottom: 20px;
        }

        .alert {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border-radius: 5px;
            display: <?php echo empty($message) ? 'none' : 'block'; ?>;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        form {
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
        }

        form input[type="text"], form select {
            width: 200px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        form input[type="submit"], form input[type="reset"] {
            padding: 8px 15px;
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        form input[type="submit"]:hover, form input[type="reset"]:hover {
            background-color: #45a049;
        }

        .pagination {
            margin-top: 10px;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            margin: 0 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div id="messages">
    <p id="message" class="alert"><?php echo $message; ?></p>
</div>

<form id="employeeForm" method="post" action="">
    <input type="hidden" id="editing_employee_id" name="editing_employee_id">
    
    <label for="employee_id">Employee ID:</label>
    <input type="text" id="employee_id" name="employee_id" required><br>

    <label for="first_name">First Name:</label>
    <input type="text" id="first_name" name="first_name" required><br>

    <label for="last_name">Last Name:</label>
    <input type="text" id="last_name" name="last_name" required><br>

    <label for="department">Department:</label>
    <select id="department" name="department" required>
        <option value="">Select Department</option>
        <option value="1">HR</option>
        <option value="2">IT</option>
        <option value="3">Graphics</option>
    </select><br>

    <input type="submit" name="action" value="Add">
    <input type="submit" name="action" value="Update">
    <input type="reset" value="Reset">
</form>

<table>
    <thead>
        <tr>
            <th>Employee ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Department</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="employeeTable">
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["employee_id"] . "</td>";
            echo "<td>" . $row["first_name"] . "</td>";
            echo "<td>" . $row["last_name"] . "</td>";
            echo "<td>" . $row["department_name"] . "</td>"; // Display department name
            echo "<td><a href=\"$_SERVER[PHP_SELF]?action=delete&employee_id=" . $row["employee_id"] . "\">Delete</a> | ";
            echo "<a href=\"#\" onclick=\"editEmployee('" . $row["employee_id"] . "', '" . $row["first_name"] . "', '" . $row["last_name"] . "', '" . $row["department_id"] . "')\">Edit</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No employees found</td></tr>";
    }
    ?>
</tbody>


</table>

<div class="pagination">
    <?php
    // Pagination links
    if ($total_pages > 1) {
        
        if ($page > 1) {
            echo "<a href='" . $_SERVER['PHP_SELF'] . "?page=" . ($page - 1) . "'>&laquo; Previous</a>";
        }
        // Numbered page links
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='" . $_SERVER['PHP_SELF'] . "?page=" . $i . "'";
            if ($i == $page) {
                echo " class='active'";
            }
            echo ">" . $i . "</a>";
        }
        
        // Previous page link
        // Next page link
        if ($page < $total_pages) {
            echo "<a href='" . $_SERVER['PHP_SELF'] . "?page=" . ($page + 1) . "'>Next &raquo;</a>";
        } else {
            echo "<span class='disabled'>Next &raquo;</span>";
        }
    }
    ?>
</div>



<script>
    function editEmployee(employee_id, first_name, last_name, department_id) {
        document.getElementById('editing_employee_id').value = employee_id;
        document.getElementById('employee_id').value = employee_id;
        document.getElementById('first_name').value = first_name;
        document.getElementById('last_name').value = last_name;
        document.getElementById('department').value = department_id;
    }
</script>

</body>
</html>
<style>
.pagination {
    margin-top: 10px;
    text-align: center;
}

.pagination a, .pagination span.disabled {
    color: black;
    padding: 8px 16px;
    text-decoration: none;
    border: 1px solid #ddd;
    margin: 0 4px;
}

.pagination a.active {
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
}

.pagination a:hover:not(.active) {
    background-color: #ddd;
    cursor: pointer;
}
</style>
<script>
    // JavaScript to hide the message after 3 seconds (3000 milliseconds)
    setTimeout(function() {
        document.getElementById('message').style.display = 'none';
    }, 1000); // 3000 milliseconds = 3 seconds
</script>