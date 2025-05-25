<?php
session_start();
require_once 'config.php';

// Check if user is logged in and verified
if (!isset($_SESSION['email']) || !isset($_SESSION['is_verified']) || $_SESSION['is_verified'] != 1) {
    header("Location: login.php");
    exit();
}

// Create grades table if not exists
$grades_table_sql = "CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    midterm_grade DECIMAL(5,2) NOT NULL,
    final_grade DECIMAL(5,2) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($grades_table_sql)) {
    die("Error creating grades table: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO grades (student_id, student_name, subject_code, subject_name, midterm_grade, final_grade, semester, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssddss", 
                    $_POST['student_id'],
                    $_POST['student_name'],
                    $_POST['subject_code'],
                    $_POST['subject_name'],
                    $_POST['midterm_grade'],
                    $_POST['final_grade'],
                    $_POST['semester'],
                    $_POST['academic_year']
                );
                $stmt->execute();
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE grades SET student_name=?, subject_name=?, midterm_grade=?, final_grade=?, semester=?, academic_year=? WHERE id=?");
                $stmt->bind_param("ssddssi", 
                    $_POST['student_name'],
                    $_POST['subject_name'],
                    $_POST['midterm_grade'],
                    $_POST['final_grade'],
                    $_POST['semester'],
                    $_POST['academic_year'],
                    $_POST['grade_id']
                );
                $stmt->execute();
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM grades WHERE id=?");
                $stmt->bind_param("i", $_POST['grade_id']);
                $stmt->execute();
                break;
        }
    }
}

// Get filter parameters
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT * FROM grades WHERE 1=1";
if ($semester) {
    $query .= " AND semester = '$semester'";
}
if ($academic_year) {
    $query .= " AND academic_year = '$academic_year'";
}
if ($search) {
    $query .= " AND (student_name LIKE '%$search%' OR subject_name LIKE '%$search%')";
}
$query .= " ORDER BY updated_at DESC";

$grades = $conn->query($query);

// Get unique semesters and academic years for filters
$semesters = $conn->query("SELECT DISTINCT semester FROM grades ORDER BY semester");
$academic_years = $conn->query("SELECT DISTINCT academic_year FROM grades ORDER BY academic_year DESC");

// Get all students for the dropdown
$students = $conn->query("SELECT id, student_id, first_name, last_name FROM students ORDER BY last_name, first_name");

// Get all subjects for the dropdown
$subjects = $conn->query("SELECT subject_code, subject_name FROM subjects ORDER BY subject_code");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSCS Grades Portal - Grades Management</title>
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
                    <a class="nav-link active" href="grades.php">
                        <i class='bx bxs-book'></i> Grades
                    </a>
                    <a class="nav-link" href="students.php">
                        <i class='bx bxs-user-detail'></i> Students
                    </a>
                    <a class="nav-link" href="subjects.php">
                        <i class='bx bxs-book-content'></i> Subjects
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class='bx bxs-report'></i> Reports
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class='bx bxs-log-out'></i> Logout
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Grades Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGradeModal">
                        <i class='bx bx-plus'></i> Add New Grade
                    </button>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search student or subject..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="semester">
                                    <option value="">All Semesters</option>
                                    <?php while($row = $semesters->fetch_assoc()): ?>
                                        <option value="<?php echo $row['semester']; ?>" <?php echo $semester === $row['semester'] ? 'selected' : ''; ?>>
                                            <?php echo $row['semester']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="academic_year">
                                    <option value="">All Academic Years</option>
                                    <?php while($row = $academic_years->fetch_assoc()): ?>
                                        <option value="<?php echo $row['academic_year']; ?>" <?php echo $academic_year === $row['academic_year'] ? 'selected' : ''; ?>>
                                            <?php echo $row['academic_year']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Grades Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="gradesTable">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Subject</th>
                                        <th>Midterm</th>
                                        <th>Final</th>
                                        <th>Semester</th>
                                        <th>Academic Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($grade = $grades->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                        <td><?php echo number_format($grade['midterm_grade'], 2); ?></td>
                                        <td><?php echo number_format($grade['final_grade'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($grade['semester']); ?></td>
                                        <td><?php echo htmlspecialchars($grade['academic_year']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-grade" 
                                                    data-id="<?php echo $grade['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editGradeModal">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-grade"
                                                    data-id="<?php echo $grade['id']; ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteGradeModal">
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

    <!-- Add Grade Modal -->
    <div class="modal fade" id="addGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Student</label>
                            <select class="form-select" name="student_id" id="student_select" required>
                                <option value="">Select Student ID</option>
                                <?php while($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($student['student_id']); ?>">
                                        <?php echo htmlspecialchars($student['student_id']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" name="student_name" id="student_name" readonly required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select class="form-select" name="subject_code" id="subject_select" required>
                                <option value="">Select Subject</option>
                                <?php while($subject = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($subject['subject_code']); ?>" 
                                            data-name="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                        <?php echo htmlspecialchars($subject['subject_code']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" id="subject_name" readonly required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Midterm Grade</label>
                            <input type="number" step="0.01" class="form-control" name="midterm_grade" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Final Grade</label>
                            <input type="number" step="0.01" class="form-control" name="final_grade" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" name="academic_year" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Grade Modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="grade_id" id="edit_grade_id">
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" name="student_name" id="edit_student_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Midterm Grade</label>
                            <input type="number" step="0.01" class="form-control" name="midterm_grade" id="edit_midterm_grade" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Final Grade</label>
                            <input type="number" step="0.01" class="form-control" name="final_grade" id="edit_final_grade" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="text" class="form-control" name="semester" id="edit_semester" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Academic Year</label>
                            <input type="text" class="form-control" name="academic_year" id="edit_academic_year" required>
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

    <!-- Delete Grade Modal -->
    <div class="modal fade" id="deleteGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Grade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="grade_id" id="delete_grade_id">
                        <p>Are you sure you want to delete this grade record?</p>
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
            $('#gradesTable').DataTable({
                order: [[1, 'asc']], // Sort by student name by default
                pageLength: 10
            });

            // Handle student selection
            $('#student_select').change(function() {
                const studentId = $(this).val();
                if (studentId) {
                    // Make an AJAX call to get student information
                    $.ajax({
                        url: 'get_student.php',
                        method: 'POST',
                        data: { student_id: studentId },
                        success: function(response) {
                            if (response.success) {
                                $('#student_name').val(response.student_name);
                            } else {
                                alert('Error: ' + response.message);
                                $('#student_name').val('');
                            }
                        },
                        error: function() {
                            alert('Error fetching student information');
                            $('#student_name').val('');
                        }
                    });
                } else {
                    $('#student_name').val('');
                }
            });

            // Handle subject selection
            $('#subject_select').change(function() {
                const selectedOption = $(this).find('option:selected');
                const subjectName = selectedOption.data('name');
                $('#subject_name').val(subjectName);
            });

            // Handle edit button click
            $('.edit-grade').click(function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                
                $('#edit_grade_id').val(id);
                $('#edit_student_name').val(row.find('td:eq(1)').text());
                $('#edit_subject_name').val(row.find('td:eq(2)').text());
                $('#edit_midterm_grade').val(row.find('td:eq(3)').text());
                $('#edit_final_grade').val(row.find('td:eq(4)').text());
                $('#edit_semester').val(row.find('td:eq(5)').text());
                $('#edit_academic_year').val(row.find('td:eq(6)').text());
            });

            // Handle delete button click
            $('.delete-grade').click(function() {
                const id = $(this).data('id');
                $('#delete_grade_id').val(id);
            });
        });
    </script>
</body>
</html> 