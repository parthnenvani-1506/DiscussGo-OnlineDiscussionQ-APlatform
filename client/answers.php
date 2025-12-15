<div class="container">
  <h5>Answers:</h5>
  <div class="offset-sm-1">

    <button class="btn btn-success mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#answersBox" aria-expanded="false" aria-controls="answersBox" title="click here to view answers">
      Show Answers
    </button>

    <div class="collapse" id="answersBox">
      <?php
      include_once("./common/time_ago_function.php"); 

      // Default profile image path
      $defaultProfileImagePath = './public/profiles/default_profile.png';

      $userId = $_SESSION['user']['user_id'] ?? null;

      // Select 'profile_image' for answers and order by 'created_at'
      // u.profile_image added to SELECT statement
      $query = "SELECT a.id, a.answer, a.user_id, u.email, u.profile_image, a.created_at 
                FROM answers a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.question_id = ?
                ORDER BY a.created_at ASC"; // Using prepared statement

      $stmt = $conn->prepare($query);
      if ($stmt === false) {
          die('MySQL prepare error for answers: ' . $conn->error);
      }
      $stmt->bind_param("i", $qid); // $qid should be available from question_details.php
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows > 0) {
          foreach ($result as $row) {
              $answerId = htmlspecialchars($row['id']);
              $answer   = htmlspecialchars($row['answer']); 
              $email    = htmlspecialchars($row['email']);   
              $ownerId  = htmlspecialchars($row['user_id']); 
              $profileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png'); // Fetch profile image
              $answer_time = time_ago($row['created_at']); // Get time ago for answer

              // Construct profile image path for answer
              $answerUserProfileImagePath = './public/profiles/' . $profileImage;
              if (!file_exists($answerUserProfileImagePath) || is_dir($answerUserProfileImagePath)) {
                  $answerUserProfileImagePath = $defaultProfileImagePath;
              }

              // Check if current user already liked this answer
              $liked = false;
              if ($userId) {
                  $checkLike_stmt = $conn->prepare("SELECT 1 FROM answer_likes WHERE user_id=? AND answer_id=?");
                  if ($checkLike_stmt === false) {
                      die('MySQL prepare error for checking like: ' . $conn->error);
                  }
                  $checkLike_stmt->bind_param("ii", $userId, $answerId);
                  $checkLike_stmt->execute();
                  $checkLike_result = $checkLike_stmt->get_result();
                  if ($checkLike_result->num_rows > 0) {
                      $liked = true;
                  }
              }

              // Total like count for this answer 
              $likeRes_stmt = $conn->prepare("SELECT COUNT(*) as total FROM answer_likes WHERE answer_id=?");
              if ($likeRes_stmt === false) {
                  die('MySQL prepare error for like count: ' . $conn->error);
              }
              $likeRes_stmt->bind_param("i", $answerId);
              $likeRes_stmt->execute();
              $likeRes_result = $likeRes_stmt->get_result();
              $likeCount = $likeRes_result->fetch_assoc()['total'];

              echo "
              <div class='card mb-3 shadow-sm'>
                <div class='card-body'>
                  <div class='d-flex align-items-center mb-2'>
                    <img src='" . $answerUserProfileImagePath . "' alt='Profile' class='question-user-img me-2'>
                    <div>
                      <h5 style='color:#1c7194 !important;' class='mb-0'>$email:</h5>
                      <small class='text-muted'><b>·</b> $answer_time</small>
                    </div>
                  </div>
                  <span class='mb-0'>" . nl2br($answer) . "</span>
                  <div class='mt-2'>
                    <button class='btn btn-sm btn-like' data-answer='$answerId'>"
                      . ($liked ? "❤️ Liked" : "👍 Like") .
                    "</button>
                    <span id='like-count-$answerId'>$likeCount</span> Likes
                  </div>
              ";

              // Edit/delete buttons if user is owner
              if ($userId && $userId == $ownerId) {
                  echo "<div class='mt-2'>
                    <a href='?q-id=$qid&edit_answer=$answerId' class='custom-btn btn-edit'>Edit</a>
                    <a href='./server/request.php?action=delete_answer&id=$answerId&qid=$qid' class='custom-btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this answer?\")'>Delete</a>
                  </div>";
              }
              echo "</div></div>";
          }
      } else {
          echo "<p class='text-muted'>No answers yet.</p>";
      }
      ?>
    </div>
  </div>
</div>

<script>
document.querySelectorAll(".btn-like").forEach(btn => {
    btn.addEventListener("click", function() {
        let answerId = this.getAttribute("data-answer");
        let btn = this;
        let countEl = document.getElementById("like-count-" + answerId);
        let count = parseInt(countEl.innerText);

        fetch("server/request.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "action=answer_like&answer_id=" + answerId
        })
        .then(res => res.text())
        .then(data => {
            if (data === "liked") {
                btn.innerText = "❤️ Liked";
                countEl.innerText = count + 1;
            } else if (data === "unliked") {
                btn.innerText = "👍 Like";
                countEl.innerText = count - 1;
            } else if (data === "not_logged_in") { // Handle case where user is not logged in
                alert("Please log in to like answers.");
            }
        });
    });
});
</script>