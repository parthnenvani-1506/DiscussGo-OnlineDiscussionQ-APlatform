<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img28.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">My Questions</h2>
    </div>
  </div>
</div>

<div class="container">
  <br><br>
  <p class="lead p-2 mb-2 border rounded">
    Here you can view your own questions, check answers from others,
    and delete any question you no longer want to keep — everything you need to manage your questions in one place.
    <span class="badge bg-primary ms-2">My Questions</span>
  </p>
  <br>

  <div class="row">
    <div class="col-8">
      <h1 class="heading_title" id="questions-heading">My Questions</h1>
      <div id="questions-container">
        <?php
        include("./common/db.php");
        include("./common/time_ago_function.php");

        $defaultProfileImagePath = './public/profiles/default_profile.png';

        if (!isset($_SESSION['user']['user_id'])) {
            echo "<p class='text-center'>Please log in to view your questions.</p>";
        } else {
            $uid = $_SESSION['user']['user_id'];

            $baseQuery = "SELECT q.id, q.title, u.email, u.profile_image, q.created_at
                          FROM questions q
                          JOIN users u ON q.user_id = u.id
                          WHERE q.user_id = ?";

            $conditions = [];
            $params = [$uid];
            $paramTypes = "i";

            if (isset($_GET["c-id"])) {
                $conditions[] = "q.category_id = ?";
                $params[] = intval($_GET["c-id"]);
                $paramTypes .= "i";
            }

            $query = $baseQuery;
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }
            $query .= " ORDER BY q.created_at DESC";

            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die('MySQL prepare error: ' . $conn->error);
            }
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                foreach ($result as $row) {
                    $title = htmlspecialchars($row["title"]);
                    $id = htmlspecialchars($row['id']);
                    $userEmail = htmlspecialchars($row['email']);
                    $profileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png');
                    $question_time = time_ago($row['created_at']);

                    $userProfileImagePath = './public/profiles/' . $profileImage;
                    if (!file_exists($userProfileImagePath) || is_dir($userProfileImagePath)) {
                        $userProfileImagePath = $defaultProfileImagePath;
                    }

                    echo "
                    <div class='question-list ques_wrap d-flex justify-content-between align-items-center mb-3 p-3 border rounded shadow-sm'>
                      <div class='d-flex align-items-center flex-grow-1 me-3'>
                          <img src='" . $userProfileImagePath . "' alt='Profile' class='question-user-img me-3'>
                          <div class='question-text'>
                              <h4 class='my_questions mb-1'>
                                  <a href='?q-id=$id' class='text-decoration-none' title='Click here to view and give answers'>" . nl2br(ucfirst($title)) . "</a>
                              </h4>
                              <small class='text-muted'>Asked by: <b>" . $userEmail . "</b> · " . $question_time . "</small>
                          </div>
                      </div>
                      <a href='./server/request.php?delete=$id'
                         class='btn btn-danger btn-sm'
                         title='Click here to delete your question'
                         onclick='return confirm(\"Are you sure you want to delete this question?\")'>
                         Delete
                      </a>
                    </div>";
                }
            } else {
                echo "<p class='text-center'>No questions found.</p>";
            }
        }
        ?>
      </div>
    </div>

    <div class="col-4">
      <?php
      $currentContextParams = ['u-id' => $_SESSION['user']['user_id'] ?? null];
      include("categorylist.php");
      ?>
    </div>
  </div>
</div>
