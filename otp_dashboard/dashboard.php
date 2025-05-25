<?php
session_start();
require_once 'config.php';

// Check if user is logged in and verified
if (!isset($_SESSION['email']) || !isset($_SESSION['is_verified']) || $_SESSION['is_verified'] != 1) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [
    'total_students' => $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] ?? 0,
    'total_subjects' => $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'] ?? 0,
    'total_grades' => $conn->query("SELECT COUNT(*) as count FROM grades")->fetch_assoc()['count'] ?? 0
];

// Get recent grades
$recent_grades = $conn->query("
    SELECT g.*, s.first_name, s.last_name, sub.subject_name 
    FROM grades g 
    JOIN students s ON g.student_id = s.student_id 
    JOIN subjects sub ON g.subject_code = sub.subject_code 
    ORDER BY g.created_at DESC 
    LIMIT 5
");

// Get grade distribution
$grade_distribution = $conn->query("
    SELECT 
        CASE 
            WHEN final_grade >= 90 THEN 'A'
            WHEN final_grade >= 80 THEN 'B'
            WHEN final_grade >= 70 THEN 'C'
            WHEN final_grade >= 60 THEN 'D'
            ELSE 'F'
        END as grade,
        COUNT(*) as count
    FROM grades
    GROUP BY grade
    ORDER BY grade
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSCS Grades Portal - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
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
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                    <a class="nav-link" href="grades.php">
                        <i class='bx bxs-book'></i> Grades
                    </a>
                    <a class="nav-link" href="students.php">
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
                <h2 class="mb-4">Dashboard</h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <h2 class="card-text"><?php echo $stats['total_students']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Subjects</h5>
                                <h2 class="card-text"><?php echo $stats['total_subjects']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Grades</h5>
                                <h2 class="card-text"><?php echo $stats['total_grades']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Grade Distribution Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Grade Distribution</h5>
                                <canvas id="gradeDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Grades -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Grades</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Subject</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($grade = $recent_grades->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($grade['final_grade']); ?></td>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grade Distribution Chart
        const gradeData = <?php 
            $data = [];
            while($row = $grade_distribution->fetch_assoc()) {
                $data[$row['grade']] = $row['count'];
            }
            echo json_encode($data);
        ?>;

        new Chart(document.getElementById('gradeDistributionChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(gradeData),
                datasets: [{
                    label: 'Number of Students',
                    data: Object.values(gradeData),
                    backgroundColor: [
                        '#28a745', // A
                        '#17a2b8', // B
                        '#ffc107', // C
                        '#fd7e14', // D
                        '#dc3545'  // F
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 