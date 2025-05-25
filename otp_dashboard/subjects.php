<?php
session_start();
require_once 'config.php';

// Check if user is logged in and verified
if (!isset($_SESSION['email']) || !isset($_SESSION['is_verified']) || $_SESSION['is_verified'] != 1) {
    header("Location: login.php");
    exit();
}

// Create subjects table if not exists
$subjects_table_sql = "CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(255) NOT NULL,
    units INT NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($subjects_table_sql)) {
    die("Error creating subjects table: " . $conn->error);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, units, year_level, semester) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiss", 
                    $_POST['subject_code'],
                    $_POST['subject_name'],
                    $_POST['units'],
                    $_POST['year_level'],
                    $_POST['semester']
                );
                $stmt->execute();
                break;

            case 'edit':
                $stmt = $conn->prepare("UPDATE subjects SET subject_name=?, units=?, year_level=?, semester=? WHERE id=?");
                $stmt->bind_param("sissi", 
                    $_POST['subject_name'],
                    $_POST['units'],
                    $_POST['year_level'],
                    $_POST['semester'],
                    $_POST['subject_id']
                );
                $stmt->execute();
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM subjects WHERE id=?");
                $stmt->bind_param("i", $_POST['subject_id']);
                $stmt->execute();
                break;
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT * FROM subjects WHERE 1=1";
if ($search) {
    $query .= " AND (subject_code LIKE '%$search%' OR subject_name LIKE '%$search%')";
}
$query .= " ORDER BY subject_code";

$subjects = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSCS Grades Portal - Subjects Management</title>
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
                    <a class="nav-link" href="students.php">
                        <i class='bx bxs-user-detail'></i> Students
                    </a>
                    <a class="nav-link active" href="subjects.php">
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
                    <h2>Subjects Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class='bx bx-plus'></i> Add New Subject
                    </button>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, or description..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Subjects Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="subjectsTable">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Units</th>
                                        <th>Year Level</th>
                                        <th>Semester</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($subject = $subjects->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($subject['subject_code']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['units']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['year_level']); ?></td>
                                        <td><?php echo htmlspecialchars($subject['semester']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-subject" 
                                                    data-id="<?php echo $subject['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSubjectModal">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-subject"
                                                    data-id="<?php echo $subject['id']; ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSubjectModal">
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

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Subject Code</label>
                            <input type="text" class="form-control" name="subject_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Units</label>
                            <input type="number" class="form-control" name="units" required min="1" max="6">
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
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" required>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="subject_id" id="edit_subject_id">
                        <div class="mb-3">
                            <label class="form-label">Subject Name</label>
                            <input type="text" class="form-control" name="subject_name" id="edit_subject_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Units</label>
                            <input type="number" class="form-control" name="units" id="edit_units" required min="1" max="6">
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
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" id="edit_semester" required>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
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

    <!-- Delete Subject Modal -->
    <div class="modal fade" id="deleteSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="subject_id" id="delete_subject_id">
                        <p>Are you sure you want to delete this subject?</p>
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
            $('#subjectsTable').DataTable({
                order: [[0, 'asc']], // Sort by subject code by default
                pageLength: 10
            });

            // Handle edit button click
            $('.edit-subject').click(function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');
                
                $('#edit_subject_id').val(id);
                $('#edit_subject_name').val(row.find('td:eq(1)').text());
                $('#edit_units').val(row.find('td:eq(2)').text());
                $('#edit_year_level').val(row.find('td:eq(3)').text());
                $('#edit_semester').val(row.find('td:eq(4)').text());
            });

            // Handle delete button click
            $('.delete-subject').click(function() {
                const id = $(this).data('id');
                $('#delete_subject_id').val(id);
            });
        });
    </script>
</body>
</html> 