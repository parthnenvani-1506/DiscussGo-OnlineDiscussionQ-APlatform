<?php
include('includes/header.php');

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM questions WHERE id = $id");
    // header("Location: manage_questions.php");
    // exit;
}

$query = "SELECT q.*, u.user_name, c.name AS category_name FROM questions q JOIN users u ON q.user_id = u.id JOIN category c ON q.category_id = c.id";

$whereClauses = [];
if (isset($_GET['category']) && $_GET['category'] !== '') {
    $whereClauses[] = "q.category_id = " . $_GET['category'];
}
if (isset($_GET['user']) && $_GET['user'] !== '') {
    $whereClauses[] = "q.user_id = " . $_GET['user'];
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$questions = $conn->query($query);
$categories = $conn->query("SELECT * FROM category");
$users = $conn->query("SELECT * FROM users");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Questions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form action="manage_questions.php" method="get">
                            <label for="category_filter">Filter by Category:</label>
                            <select name="category" id="category_filter" class="form-control" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php while ($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form action="manage_questions.php" method="get">
                            <label for="user_filter">Filter by User:</label>
                            <select name="user" id="user_filter" class="form-control" onchange="this.form.submit()">
                                <option value="">All Users</option>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo (isset($_GET['user']) && $_GET['user'] == $user['id']) ? 'selected' : ''; ?>><?php echo $user['user_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Question List</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-head-fixed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($questions->num_rows > 0): ?>
                            <?php while ($q = $questions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $q['id']; ?></td>
                                    <td class="questions_column"><?php echo $q['title']; ?></td>
                                    <td><?php echo $q['category_name']; ?></td>
                                    <td><?php echo $q['user_name']; ?></td>
                                    <td>
                                        <a href="manage_answers.php?question_id=<?php echo $q['id']; ?>" class="btn btn-primary btn-sm">View Answers</a>
                                        <a href="manage_questions.php?delete=<?php echo $q['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No questions found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
    td{
        word-wrap:break-word;
    }
    .questions_column{
        max-width:400px;
        word-wrap:break-word;
    }
</style>
<?php
include('includes/footer.php');
?>