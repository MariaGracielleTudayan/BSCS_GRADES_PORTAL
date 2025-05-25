<?php
session_start();
require_once 'config.php';

// Check if user is logged in and verified
if (!isset($_SESSION['email']) || !isset($_SESSION['is_verified']) || $_SESSION['is_verified'] != 1) {
    header("Location: login.php");
    exit();
}

// Create students table if not exists
$students_table_sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($students_table_sql)) {
    die("Error creating students table: " . $conn->error);
}

// Remove email column if it exists
$alter_table_sql = "ALTER TABLE students DROP COLUMN IF EXISTS email";
if (!$conn->query($alter_table_sql)) {
    die("Error removing email column: " . $conn->error);
}

// Remove course column if it exists
$alter_table_sql = "ALTER TABLE students DROP COLUMN IF EXISTS course";
if (!$conn->query($alter_table_sql)) {
    die("Error removing course column: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, year_level) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", 
                    $_POST['student_id'],
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['year_level']
                );
                $stmt->execute();
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, year_level=? WHERE id=?");
                $stmt->bind_param("sssi", 
                    $_POST['first_name'],
                    $_POST['last_name'],
                    $_POST['year_level'],
                    $_POST['student_id']
                );
                $stmt->execute();
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
                $stmt->bind_param("i", $_POST['student_id']);
                $stmt->execute();
                break;
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT * FROM students WHERE 1=1";
if ($search) {
    $query .= " AND (student_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%')";
}
$query .= " ORDER BY last_name, first_name";

$students = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSCS Grades Portal - Students Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .nav-link {
            color: white;
            margin: 5px 0;
        }
        .nav-link:hover {
            background: #34495e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <h4 class="text-center mb-4">BSCS Portal</h4>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                    <a class="nav-link" href="grades.php">
                        <i class='bx bxs-book'></i> Grades
                    </a>
                    <a class="nav-link active" href="students.php">
                        <i class='bx bxs-user-detail'></i> Students
                    </a>
                    <a class="nav-link" href="subjects.php">
                        <i class='bx bxs-book-content'></i> Subjects
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class='bx bxs-log-out'></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Students Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class='bx bx-plus'></i> Add New Student
                    </button>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" placeholder="Search by ID, name, or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Year Level</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($student = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['year_level']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-student" 
                                                    data-id="<?php echo $student['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editStudentModal">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-student"
                                                    data-id="<?php echo $student['id']; ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteStudentModal">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" name="student_id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year Level</label>
                            <select class="form-select" name="year_level" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="student_id" id="edit_student_id">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year Level</label>
                            <select class="form-select" name="year_level" id="edit_year_level" required>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Student Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="student_id" id="delete_student_id">
                        <p>Are you sure you want to delete this student record?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#studentsTable').DataTable({
                order: [[1, 'asc']], // Sort by name by default
                pageLength: 10
            });

            // Handle edit button click
            $('.edit-student').click(function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                const name = row.find('td:eq(1)').text().split(', ');
                
                $('#edit_student_id').val(id);
                $('#edit_last_name').val(name[0]);
                $('#edit_first_name').val(name[1]);
                $('#edit_year_level').val(row.find('td:eq(2)').text());
            });

            // Handle delete button click
            $('.delete-student').click(function() {
                const id = $(this).data('id');
                $('#delete_student_id').val(id);
            });
        });
    </script>
</body>
</html> 