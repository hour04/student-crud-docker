<?php
$host = "db";
$dbname = "student_db";
$username = "root";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Add student
if (isset($_POST["add"])) {
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $major = $_POST["major"];

    $stmt = $pdo->prepare("INSERT INTO students (name, gender, email, phone, major) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $gender, $email, $phone, $major]);

    header("Location: index.php");
    exit;
}

// Delete student
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];

    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}

// Get student for edit
$editStudent = null;

if (isset($_GET["edit"])) {
    $id = $_GET["edit"];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $editStudent = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update student
if (isset($_POST["update"])) {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $major = $_POST["major"];

    $stmt = $pdo->prepare("UPDATE students SET name = ?, gender = ?, email = ?, phone = ?, major = ? WHERE id = ?");
    $stmt->execute([$name, $gender, $email, $phone, $major, $id]);

    header("Location: index.php");
    exit;
}

// Search students
$search = $_GET["search"] ?? "";

if ($search !== "") {
    $stmt = $pdo->prepare("
        SELECT * FROM students
        WHERE name LIKE ?
        OR gender LIKE ?
        OR email LIKE ?
        OR phone LIKE ?
        OR major LIKE ?
        ORDER BY id DESC
    ");

    $keyword = "%$search%";
    $stmt->execute([$keyword, $keyword, $keyword, $keyword, $keyword]);
} else {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student CRUD</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h1 {
            text-align: center;
        }

        form {
            margin-bottom: 20px;
        }

        input, select, button {
            padding: 10px;
            margin: 5px;
        }

        input, select {
            width: 180px;
        }

        button {
            cursor: pointer;
            border: none;
            border-radius: 5px;
            background: #007bff;
            color: white;
        }

        .edit {
            background: #28a745;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .delete {
            background: #dc3545;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .cancel {
            padding: 7px 12px;
            text-decoration: none;
            background: gray;
            color: white;
            border-radius: 5px;
        }

        .search-box {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Student Management System</h1>

    <form method="POST">
        <?php if ($editStudent): ?>
            <input type="hidden" name="id" value="<?= e($editStudent["id"]) ?>">
        <?php endif; ?>

        <input type="text" name="name" placeholder="Name" required value="<?= e($editStudent["name"] ?? "") ?>">

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option value="Male" <?= isset($editStudent["gender"]) && $editStudent["gender"] === "Male" ? "selected" : "" ?>>Male</option>
            <option value="Female" <?= isset($editStudent["gender"]) && $editStudent["gender"] === "Female" ? "selected" : "" ?>>Female</option>
            <option value="Other" <?= isset($editStudent["gender"]) && $editStudent["gender"] === "Other" ? "selected" : "" ?>>Other</option>
        </select>

        <input type="email" name="email" placeholder="Email" required value="<?= e($editStudent["email"] ?? "") ?>">
        <input type="text" name="phone" placeholder="Phone" required value="<?= e($editStudent["phone"] ?? "") ?>">
        <input type="text" name="major" placeholder="Major" required value="<?= e($editStudent["major"] ?? "") ?>">

        <?php if ($editStudent): ?>
            <button type="submit" name="update">Update Student</button>
            <a class="cancel" href="index.php">Cancel</a>
        <?php else: ?>
            <button type="submit" name="add">Add Student</button>
        <?php endif; ?>
    </form>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search student..." value="<?= e($search) ?>">
        <button type="submit">Search</button>
        <a class="cancel" href="index.php">Reset</a>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Gender</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Major</th>
            <th>Actions</th>
        </tr>

        <?php if (count($students) > 0): ?>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?= e($student["id"]) ?></td>
                    <td><?= e($student["name"]) ?></td>
                    <td><?= e($student["gender"]) ?></td>
                    <td><?= e($student["email"]) ?></td>
                    <td><?= e($student["phone"]) ?></td>
                    <td><?= e($student["major"]) ?></td>
                    <td>
                        <a class="edit" href="index.php?edit=<?= e($student["id"]) ?>">Edit</a>
                        <a class="delete" href="index.php?delete=<?= e($student["id"]) ?>" onclick="return confirm('Delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7">No students found</td>
            </tr>
        <?php endif; ?>
    </table>

</div>
</body>
</html>