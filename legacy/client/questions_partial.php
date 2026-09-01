<?php
session_start();
include("../common/db.php");
include("../common/time_ago_function.php");

$isMyQuestions = false;
$isLatest = false;
$whereClauses = [];
$defaultProfileImagePath = './public/profiles/default_profile.png';

$query = "
    SELECT q.id, q.title, u.email, u.profile_image, q.created_at
    FROM questions q
    JOIN users u ON q.user_id = u.id
";

if (isset($_GET['c-id'])) {
    $cid = (int)$_GET['c-id'];
    $whereClauses[] = "q.category_id = $cid";
}

if (isset($_GET['u-id']) && isset($_SESSION['user']['user_id'])) {
    $uid = (int)$_GET['u-id'];
    $whereClauses[] = "q.user_id = $uid";
    $isMyQuestions = true;
}

if (isset($_GET['latest'])) {
    $isLatest = true;
}

if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}

$query .= " ORDER BY q.created_at DESC";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    foreach($result as $row) {
        $title = htmlspecialchars($row["title"]);
        $id = (int)$row['id'];
        $userEmail = htmlspecialchars($row['email']);
        $question_time = time_ago($row['created_at']);

        $profileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png');
        $webImagePath = './public/profiles/' . $profileImage;
        $serverImagePath = __DIR__ . '/../public/profiles/' . $profileImage;

        if (!file_exists($serverImagePath) || is_dir($serverImagePath)) {
            $userProfileImagePath = $defaultProfileImagePath;
        } else {
            $userProfileImagePath = $webImagePath . '?v=' . filemtime($serverImagePath);
        }

        echo "<div class='question-list ques_wrap d-flex justify-content-between align-items-center mb-3 p-3 border rounded shadow-sm'>";
        echo "<div class='d-flex align-items-center flex-grow-1 me-3'>";
        echo "<img src='" . $userProfileImagePath . "' alt='Profile' class='question-user-img me-3'>";
        echo "<div class='question-text'>";
        echo "<h4 class='my_questions mb-1'>";
        echo "<a href='?q-id=$id'>" . ucfirst($title) . "</a>";
        echo "</h4>";
        echo "<small class='text-muted'>Asked by: <b>$userEmail</b> · $question_time</small>";
        echo "</div></div>";

        if ($isMyQuestions) {
            echo "<a href='./server/request.php?delete=$id' 
                     class='btn btn-danger btn-sm ms-3'
                     title='Click here to delete your question'
                     onclick='return confirm(\"Are you sure you want to delete this question?\")'>
                     Delete
                  </a>";
        }

        echo "</div>";
    }
} else {
    echo "<p class='text-center'>No questions found.</p>";
}
?>