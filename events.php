<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "csso";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// ===== CREATE EVENT =====
if (isset($_POST['create_event'])) {
  $event_Name = $_POST['event_Name'];
  $event_Date = $_POST['event_Date'];
  $location   = $_POST['location'];

  $stmt = $conn->prepare("INSERT INTO event (event_Name, event_Date, location) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $event_Name, $event_Date, $location);
  $stmt->execute();
  $stmt->close();
  header("Location: events.php");
  exit();
}

// ===== UPDATE EVENT =====
if (isset($_POST['update_event'])) {
  $original_name = $_POST['original_name'];
  $event_Name = $_POST['event_Name'];
  $event_Date = $_POST['event_Date'];
  $location   = $_POST['location'];

  $stmt = $conn->prepare("UPDATE event SET event_Name=?, event_Date=?, location=? WHERE event_Name=?");
  $stmt->bind_param("ssss", $event_Name, $event_Date, $location, $original_name);
  $stmt->execute();
  $stmt->close();
  header("Location: events.php");
  exit();
}

// ===== SEARCH FEATURE =====
$search = "";
if (isset($_GET['search'])) {
  $search = trim($_GET['search']);
  $query = $conn->prepare("SELECT * FROM event WHERE event_Name LIKE ? OR location LIKE ?");
  $like = "%$search%";
  $query->bind_param("ss", $like, $like);
  $query->execute();
  $events = $query->get_result();
} else {
  $events = $conn->query("SELECT * FROM event");
}

// ===== DELETE EVENT =====
if (isset($_GET['delete_name'])) {
  $event_Name = $_GET['delete_name'];
  $stmt = $conn->prepare("DELETE FROM event WHERE event_Name = ?");
  $stmt->bind_param("s", $event_Name);
  $stmt->execute();
  $stmt->close();
  header("Location: events.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Events List</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
body {
  background-color: #f5f7fa;
  font-family: 'Segoe UI', sans-serif;
}
.container {
  margin-top: 40px;
}
h4 {
  color: #2563eb;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
}
.search-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.search-bar input {
  width: 300px;
  padding-left: 35px;
  border-radius: 8px;
  border: 1px solid #ccc;
}
.search-wrapper {
  position: relative;
}
.search-wrapper i {
  position: absolute;
  top: 10px;
  left: 10px;
  color: #2563eb;
}
.btn-success {
  background-color: #22c55e;
  border: none;
}
.btn-success:hover {
  background-color: #16a34a;
}

/* ====== TABLE STYLE ====== */
.table {
  border-radius: 8px;
  overflow: hidden;
}
.table thead th {
  background-color: #2563eb !important;
  color: white !important;
  text-align: center !important;
  vertical-align: middle !important;
}
.table tbody td {
  text-align: center;
  vertical-align: middle;
  background-color: white;
}
.action-buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
}
.action-btn {
  border: none;
  color: white;
  padding: 8px 10px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 35px;
  height: 35px;
  cursor: pointer;
  transition: 0.2s ease;
}
.action-btn.edit { background-color: #facc15; }
.action-btn.delete { background-color: #ef4444; }
.action-btn:hover { opacity: 0.8; }
.modal-header { background-color: #2563eb; color: white; }
</style>
</head>

<body>
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-calendar-alt"></i> Events List</h4>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEventModal">+ Add Event</button>
  </div>

  <!-- SEARCH BAR -->
  <form method="GET" class="search-bar">
    <div class="search-wrapper">
      <i class="fas fa-search"></i>
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search event..." class="form-control">
    </div>
    <a href="events.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i></a>
  </form>

  <!-- EVENTS TABLE -->
  <div class="card shadow-sm">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>Event Name</th>
          <th>Event Date</th>
          <th>Location</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($events->num_rows > 0): ?>
          <?php while ($row = $events->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['event_Name']) ?></td>
              <td><?= htmlspecialchars($row['event_Date']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td>
                <div class="action-buttons">
                  <button class="action-btn edit"
                          data-name="<?= htmlspecialchars($row['event_Name']) ?>"
                          data-date="<?= htmlspecialchars($row['event_Date']) ?>"
                          data-location="<?= htmlspecialchars($row['location']) ?>">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="action-btn delete"
                          data-name="<?= htmlspecialchars($row['event_Name']) ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#confirmDeleteModal">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4">No events found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL ADD EVENT -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Event Name</label>
            <input type="text" name="event_Name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Event Date</label>
            <input type="date" name="event_Date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create_event" class="btn btn-primary">Create</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDIT EVENT -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-warning">
          <h5 class="modal-title">Edit Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="original_name" id="original_name">
          <div class="mb-3">
            <label class="form-label">Event Name</label>
            <input type="text" name="event_Name" id="edit_event_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Event Date</label>
            <input type="date" name="event_Date" id="edit_event_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" id="edit_location" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update_event" class="btn btn-success">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL CONFIRM DELETE -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this event?</p>
        <h6 id="eventToDelete" class="text-danger fw-bold text-center"></h6>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Yes, Delete</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
  const deleteButtons = document.querySelectorAll(".delete");
  const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
  const eventToDelete = document.getElementById("eventToDelete");

  // DELETE CONFIRMATION
  deleteButtons.forEach(button => {
    button.addEventListener("click", function() {
      const name = this.getAttribute("data-name");
      eventToDelete.textContent = name;
      confirmDeleteBtn.href = "events.php?delete_name=" + encodeURIComponent(name);
    });
  });

  // EDIT CONFIRMATION
  const editButtons = document.querySelectorAll(".edit");
  const editModal = new bootstrap.Modal(document.getElementById("editEventModal"));
  const editName = document.getElementById("edit_event_name");
  const editDate = document.getElementById("edit_event_date");
  const editLocation = document.getElementById("edit_location");
  const originalName = document.getElementById("original_name");

  editButtons.forEach(button => {
    button.addEventListener("click", function() {
      if (confirm("Are you sure you want to edit this record?")) {
        const name = this.getAttribute("data-name");
        const date = this.getAttribute("data-date");
        const location = this.getAttribute("data-location");

        editName.value = name;
        editDate.value = date;
        editLocation.value = location;
        originalName.value = name;

        editModal.show();
      }
    });
  });
});
</script>
</body>
</html>
