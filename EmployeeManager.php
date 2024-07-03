<?php
class EmployeeManager {
    private $conn;

    public function __construct($servername, $username, $password, $dbname) {
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function addEmployee($employee_id, $first_name, $last_name, $department_id) {
        $sql = "INSERT INTO employees (employee_id, first_name, last_name, department_id) VALUES ('$employee_id', '$first_name', '$last_name', '$department_id')";
        return $this->executeQuery($sql);
    }

    public function updateEmployee($employee_id, $first_name, $last_name, $department_id) {
        $sql = "UPDATE employees SET first_name='$first_name', last_name='$last_name', department_id='$department_id' WHERE employee_id='$employee_id'";
        return $this->executeQuery($sql);
    }

    public function deleteEmployee($employee_id) {
        $sql = "DELETE FROM employees WHERE employee_id='$employee_id'";
        return $this->executeQuery($sql);
    }

    public function getEmployees() {
        $sql = "SELECT employees.employee_id, employees.first_name, employees.last_name, departments.name AS department FROM employees INNER JOIN departments ON employees.department_id = departments.id";
        return $this->conn->query($sql);
    }

    private function executeQuery($sql) {
        if ($this->conn->query($sql) === TRUE) {
            return "Operation successful!";
        } else {
            return "Error: " . $sql . "<br>" . $this->conn->error;
        }
    }

    public function close() {
        $this->conn->close();
    }
}
