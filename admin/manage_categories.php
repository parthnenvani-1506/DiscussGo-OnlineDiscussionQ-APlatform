<?php
include('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;
    if ($id) {
        $conn->query("UPDATE category SET name = '$name' WHERE id = $id");
    } else {
        $conn->query("INSERT INTO category (name) VALUES ('$name')");
    }
    // header("Location: manage_categories.php");
    // exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM category WHERE id = $id");
    // header("Location: manage_categories.php");
    // exit;
}

$categories = $conn->query("SELECT * FROM category");
?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Add/Edit Category</h3>
            </div>
            <form action="manage_categories.php" method="post">
                <div class="card-body">
                    <input type="hidden" name="id" id="categoryId">
                    <div class="form-group">
                        <label for="categoryName">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="submit" class="btn btn-primary">Save Categories</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Category List(For Update/Delete)</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo $cat['name']; ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo $cat['name']; ?>')">Edit</button>
                                    <a href="manage_categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
function editCategory(id, name) {
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
}
</script>
<?php
include('includes/footer.php');
?>