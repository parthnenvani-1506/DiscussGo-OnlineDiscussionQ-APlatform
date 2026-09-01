<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img28.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">Latest Questions</h2>
      <form class="d-flex" action="">
        <input type="hidden" name="latest" value="true">
        <input class="form-control me-2" name="search" type="search" placeholder="Search Questions" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        <button class="btn btn-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</div>

<div class="container">
  <br><br>
  <p class="lead p-2 mb-2 border rounded">
    Here you’ll find the most recent questions asked by users in the community.
    Browse through them to discover what others are curious about, share your knowledge by answering,
    or get inspired to ask your own question.
    <span class="badge bg-success ms-2">Latest Questions</span>
  </p>
  <br>

  <div class="row">
    <div class="col-8">
      <h1 class="heading_title" id="questions-heading">Latest Questions</h1>
      <div id="questions-container">
        <?php
        include("./common/db.php");
        include("./common/time_ago_function.php");

        $defaultProfileImagePath = './public/profiles/default_profile.png';

        $baseQuery = "SELECT q.id, q.title, u.email, u.profile_image, q.created_at
                      FROM questions q
                      JOIN users u ON q.user_id = u.id";

        $conditions = [];
        $params = [];
        $paramTypes = "";

        if (isset($_GET["c-id"])) {
            $conditions[] = "q.category_id = ?";
            $params[] = intval($_GET["c-id"]);
            $paramTypes .= "i";
        }

        if (isset($_GET["search"]) && !empty(trim($_GET["search"]))) {
            $search = "%" . trim($_GET["search"]) . "%";
            $conditions[] = "q.title LIKE ?";
            $params[] = $search;
            $paramTypes .= "s";
        }

        $query = $baseQuery;
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " ORDER BY q.created_at DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            if ($stmt === false) {
                die('MySQL prepare error: ' . $conn->error);
            }
            $stmt->bind_param($paramTypes, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }

        if ($result && $result->num_rows > 0) {
            foreach ($result as $row) {
                $title = htmlspecialchars($row["title"]);
                $id = htmlspecialchars($row['id']);
                $userEmail = htmlspecialchars($row["email"]);
                $profileImage = htmlspecialchars($row["profile_image"] ?? 'default_profile.png');
                $question_time = time_ago($row['created_at']);

                $userProfileImagePath = './public/profiles/' . $profileImage;
                if (!file_exists($userProfileImagePath) || is_dir($userProfileImagePath)) {
                    $userProfileImagePath = $defaultProfileImagePath;
                }

                echo "<div class='question-list ques_wrap d-flex align-items-center mb-3 p-3 border rounded shadow-sm'>";
                echo "<img src='" . $userProfileImagePath . "' alt='Profile' class='question-user-img me-3'>";
                echo "<div>";
                echo "<h4 class='my_questions mb-1'><a href='?q-id=$id' title='click here to view and give answers'>" . nl2br(ucfirst($title)) . "</a></h4>";
                echo "<small class='text-muted'>Asked by: <b>" . $userEmail . "</b> · " . $question_time . "</small>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p class='text-center'>No questions found.</p>";
        }
        ?>
      </div>
    </div>

    <div class="col-4">
      <?php
      $currentContextParams = ['latest' => true];
      include("categorylist.php");
      ?>
    </div>
  </div>
</div>