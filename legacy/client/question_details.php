<div class="container">
  <div class="row">
    <div class="col-lg-8 col-md-12">
      <h1 class="heading_title2">Answers</h1>
      <?php
      include("./common/db.php");
      include("./common/time_ago_function.php");

      $defaultProfileImagePath = './public/profiles/default_profile.png';

      $query = "SELECT q.id, q.title, q.description, q.category_id, q.created_at, u.email, u.profile_image 
                FROM questions q
                JOIN users u ON q.user_id = u.id 
                WHERE q.id = ?";

      $stmt = $conn->prepare($query);
      if ($stmt === false) {
          die('MySQL prepare error for main question: ' . $conn->error);
      }
      $stmt->bind_param("i", $qid);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $cid = $row['category_id'];
          $question_time = time_ago($row['created_at']);
          $questionUserEmail = htmlspecialchars($row["email"]);
          $questionProfileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png');

          $questionUserProfileImagePath = './public/profiles/' . $questionProfileImage;
          if (!file_exists($questionUserProfileImagePath) || is_dir($questionUserProfileImagePath)) {
              $questionUserProfileImagePath = $defaultProfileImagePath;
          }

          echo "<div class='d-flex align-items-start mb-3 p-3 border rounded shadow-sm ques_wrap'>";
          echo "<img src='" . $questionUserProfileImagePath . "' alt='Profile' class='question-user-img me-3'>";
          echo "<div>";
          echo "<h4 class='margin-bottom question_title mb-1'>Question: " . ucfirst(htmlspecialchars($row["title"])) . "</h4>";
          echo "<p class='margin-bottom mb-2 text-muted'>Asked by: <b>" . $questionUserEmail . "</b> · " . $question_time . "</p>";
          echo "<p class='margin-bottom mb-0'>" . nl2br(ucfirst(htmlspecialchars($row["description"]))) . "</p>";
          echo "</div>";
          echo "</div>";
      }

      include("answers.php");

      $editAnswerText = "";
      $actionType = "answer";
      $answerIdHidden = "";

      if (isset($_GET['edit_answer']) && isset($_SESSION['user']['user_id'])) {
          $editId = (int)$_GET['edit_answer'];
          $userId = $_SESSION['user']['user_id'];

          $res_stmt = $conn->prepare("SELECT answer FROM answers WHERE id=? AND user_id=?");
          if ($res_stmt === false) {
              die('MySQL prepare error for edit answer: ' . $conn->error);
          }
          $res_stmt->bind_param("ii", $editId, $userId);
          $res_stmt->execute();
          $res_result = $res_stmt->get_result();

          if ($res_result && $res_result->num_rows > 0) {
              $row = $res_result->fetch_assoc();
              $editAnswerText = htmlspecialchars($row['answer']);
              $actionType = "update_answer";
              $answerIdHidden = "<input type='hidden' name='answer_id' value='{$editId}'>";
          }
      }
      ?>
      <form action="./server/request.php" method="post" class="mt-4">
        <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($qid) ?>">
        <?php echo $answerIdHidden; ?>
        <input type="hidden" name="action" value="<?php echo htmlspecialchars($actionType); ?>">
        <textarea name="answer" placeholder="Your Answer.." class="form-control margin-bottom" rows="5" required><?php echo $editAnswerText; ?></textarea>
        <button type="submit" class="btn btn-primary mt-2">
          <?php echo ($actionType === "update_answer") ? "Update Answer" : "Submit your Answer"; ?>
        </button>
      </form>
    </div>

    <div class="col-lg-4 col-md-12 mt-4 mt-lg-0">
      <?php
      $categoryquery = "SELECT name FROM category WHERE id=?";
      $cat_stmt = $conn->prepare($categoryquery);
      if ($cat_stmt === false) {
          die('MySQL prepare error for category: ' . $conn->error);
      }
      $cat_stmt->bind_param("i", $cid);
      $cat_stmt->execute();
      $categoryresult = $cat_stmt->get_result();

      if ($categoryresult && $categoryresult->num_rows > 0) {
          $categoryrow = $categoryresult->fetch_assoc();
          echo "<h1 class='heading_title'>Related to " . ucfirst(htmlspecialchars($categoryrow['name'])) . "</h1>";
      } else {
          echo "<h1 class='heading_title'>Related Questions</h1>";
      }

      $relatedQuery = "SELECT q.id, q.title, u.email, u.profile_image, q.created_at 
                       FROM questions q
                       JOIN users u ON q.user_id = u.id 
                       WHERE q.category_id=? AND q.id!=?
                       ORDER BY q.created_at DESC";

      $related_stmt = $conn->prepare($relatedQuery);
      if ($related_stmt === false) {
          die('MySQL prepare error for related questions: ' . $conn->error);
      }
      $related_stmt->bind_param("ii", $cid, $qid);
      $related_stmt->execute();
      $result = $related_stmt->get_result();

      if ($result && $result->num_rows > 0) {
          foreach ($result as $row) {
              $id = htmlspecialchars($row['id']);
              $title = htmlspecialchars($row['title']);
              $relatedUserEmail = htmlspecialchars($row['email']);
              $relatedProfileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png');
              $related_question_time = time_ago($row['created_at']);

              $relatedUserProfileImagePath = './public/profiles/' . $relatedProfileImage;
              if (!file_exists($relatedUserProfileImagePath) || is_dir($relatedUserProfileImagePath)) {
                  $relatedUserProfileImagePath = $defaultProfileImagePath;
              }

              echo "<div class='question-list d-flex align-items-center mb-2 p-2 border rounded shadow-sm ques_wrap'>";
              echo "<img src='" . $relatedUserProfileImagePath . "' alt='Profile' class='question-user-img me-2'>";
              echo "<div>";
              echo "<h4><a href='?q-id=$id' title='click here to view and give answers'>" . ucfirst($title) . "</a></h4>";
              echo "<small class='text-muted'>Asked by: <b>" . $relatedUserEmail . "</b> · " . $related_question_time . "</small>";
              echo "</div>";
              echo "</div>";
          }
      } else {
          echo "<p>Sorry, No questions found related to this!!</p>";
      }
      ?>
    </div>
  </div>
</div>