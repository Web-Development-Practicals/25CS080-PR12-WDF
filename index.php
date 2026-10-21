<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

include 'db.php';
include 'upload.php';

$error = "";

// ---------- CREATE ----------
if (isset($_POST['add'])) {
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $date     = mysqli_real_escape_string($conn, $_POST['event_date']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    list($poster, $error) = upload_poster($_FILES['poster'] ?? null);

    if (!$error) {
        $poster_sql = $poster ? "'$poster'" : "NULL";
        mysqli_query($conn, "INSERT INTO events (title, event_date, location, category, poster)
                             VALUES ('$title', '$date', '$location', '$category', $poster_sql)");
        header("Location: index.php?msg=Event created successfully with poster");
        exit();
    }
}

// ---------- DELETE ----------
if (isset($_GET['delete'])) {
    $id  = (int)$_GET['delete'];
    $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT poster FROM events WHERE id = $id"));

    mysqli_query($conn, "DELETE FROM events WHERE id = $id");
    if ($row && $row['poster'] && file_exists("uploads/" . $row['poster'])) {
        unlink("uploads/" . $row['poster']);
    }
    header("Location: index.php?msg=Event deleted successfully");
    exit();
}

// ---------- READ ----------
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$cat    = mysqli_real_escape_string($conn, $_GET['category'] ?? '');

$sql = "SELECT * FROM events WHERE (title LIKE '%$search%' OR location LIKE '%$search%')";
if ($cat != '') {
    $sql .= " AND category = '$cat'";
}
$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Event Management</title>
    <style>
        body  { font-family: Arial, sans-serif; margin: 25px; }
        .top  { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 8px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th    { background: #f8f9fa; }
        img   { width: 70px; height: 90px; object-fit: cover; border-radius: 4px; }
        .ok   { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; }
        .err  { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; }
        .form-box { background: #fafafa; border: 1px solid #e0e0e0; padding: 15px; border-radius: 5px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="top">
        <h2>Admin Dashboard</h2>
        <div>Logged in as: <b><?php echo htmlspecialchars($_SESSION['admin_user']); ?></b> | <a href="logout.php">Logout</a></div>
    </div>

    <?php if (isset($_GET['msg'])) echo "<p class='ok'>" . htmlspecialchars($_GET['msg']) . "</p>"; ?>
    <?php if ($error) echo "<p class='err'>" . htmlspecialchars($error) . "</p>"; ?>

    <div class="form-box">
        <h3>Add New Event</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Event Title" required>
            <input type="date" name="event_date" required>
            <input type="text" name="location" placeholder="Location" required>
            <select name="category" required>
                <option value="">Category</option>
                <option>Music</option>
                <option>Conference</option>
                <option>Workshop</option>
                <option>Sports</option>
            </select>
            <label>Poster: <input type="file" name="poster"></label>
            <button type="submit" name="add">Add Event</button>
        </form>
    </div>

    <h3>Events List</h3>
    <form method="GET">
        <input type="text" name="search" placeholder="Search title or location" value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach (['Music', 'Conference', 'Workshop', 'Sports'] as $c): ?>
                <option <?php if ($cat == $c) echo 'selected'; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="index.php">Reset</a>
    </form>

    <table>
        <tr><th>ID</th><th>Poster</th><th>Title</th><th>Date</th><th>Location</th><th>Category</th><th>Action</th></tr>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['poster'] ? "<img src='uploads/" . htmlspecialchars($row['poster']) . "' alt='Poster'>" : "No poster"; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['event_date']; ?></td>
            <td><?php echo htmlspecialchars($row['location']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td>
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                <a href="index.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>