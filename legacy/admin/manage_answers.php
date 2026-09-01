<?php
include('includes/header.php');

// Answer deletion logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM answers WHERE id = $id");
    // Redirect back to the question's answer page if applicable
    if (isset($_GET['question_id'])) {
        // header("Location: manage_answers.php?question_id=" . $_GET['question_id']);
    } else {
        // header("Location: manage_answers.php"); // Redirect to all answers page
    }
    // exit;
}

// Check if a specific question_id is provided in the URL
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;

$answers = null;
$page_title = "Manage All Answers";
$query = "";

if ($question_id > 0) {
    // Fetch answers for a specific question
    $question_query = "SELECT title FROM questions WHERE id = $question_id";
    $question_result = $conn->query($question_query);
    if ($question_result && $question_result->num_rows > 0) {
        $question_data = $question_result->fetch_assoc();
        $page_title = "Answers for: " . htmlspecialchars($question_data['title']);
    }

    $query = "SELECT a.id, a.answer, u.user_name 
              FROM answers a 
              LEFT JOIN users u ON a.user_id = u.id 
              WHERE a.question_id = $question_id";
} else {
    // Default: Fetch all answers
    $query = "SELECT a.id, a.answer, q.title AS question_title, u.user_name 
              FROM answers a 
              LEFT JOIN questions q ON a.question_id = q.id 
              LEFT JOIN users u ON a.user_id = u.id";
}

$answers = $conn->query($query);
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo $page_title; ?></h3>
                <?php if ($question_id > 0): ?>
                    <div class="card-tools">
                        <a href="manage_questions.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Questions
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-head-fixed">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Answer</th>
                            <?php if ($question_id == 0): ?>
                                <th>Question</th>
                            <?php endif; ?>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($answers->num_rows > 0): ?>
                            <?php while ($ans = $answers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $ans['id']; ?></td>
                                    <td class="answer_column"><?php echo nl2br(htmlspecialchars($ans['answer'])); ?></td>
                                    <?php if ($question_id == 0): ?>
                                        <td><?php echo nl2br(htmlspecialchars($ans['question_title'])); ?></td>
                                    <?php endif; ?>
                                    <td class="answer_column"><?php echo htmlspecialchars($ans['user_name'] ?? 'Guest'); ?></td>
                                    <td>
                                        <a href="manage_answers.php?delete=<?php echo $ans['id']; ?>&question_id=<?php echo $question_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this answer?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="<?php echo $question_id == 0 ? 5 : 4; ?>">No answers found.</td></tr>
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
    .answer_column{
        word-wrap:break-word;
        /* display: block; */
        overflow-wrap:break-word ;
        word-break: break-word;
        white-space: normal;
    }
</style>
<?php
include('includes/footer.php');
?>


<!-- .answer_column{
        max-width:400px;
        word-wrap:break-word;
    } -->