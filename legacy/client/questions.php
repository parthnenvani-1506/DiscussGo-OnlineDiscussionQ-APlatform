<div class="container">
    <div class="row flex-lg-row-reverse align-items-center g-5 py-5">
        <div class="col-10 col-sm-8 col-lg-6">
            <img src="./public/img2.png" class="d-block mx-lg-auto img-fluid" alt="Bootstrap Themes" width="700" height="500" loading="lazy">
        </div>
        <div class="col-lg-6">
            <h1 class="display-5 fw-bold text-body-emphasis lh-1 mb-3">Hello, Welcome to Discuss Go</h1>
            <p class="lead">Welcome to <b>DiscussGo</b>, your go-to platform for real discussions, real people, and real solutions. We believe in the power of shared knowledge and community-driven conversations.</p>
            <p class="lead">Our mission is to create a space where everyone, from curious learners to seasoned experts, can ask questions, share insights, and grow together. Whether you're stuck on a technical problem, seeking advice, or just want to explore new ideas, DiscussGo is the place for you.</p>
            <p class="lead">We are committed to maintaining a friendly, respectful, and inclusive environment. Join us and be a part of a community that values curiosity and collaboration.</p>
            <h5>💡 "Real discussions. Real people. Real solutions"</h5><br>
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <a href="./?latest=true" class="btn btn-primary btn-lg px-4 me-md-2">Our Latest Questions</a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-8">
            <h1 class="heading_title" id="questions-heading">Questions</h1>
            <div id="questions-container">
                <?php
                include("./common/db.php");
                include("./common/time_ago_function.php");

                $defaultProfileImagePath = './public/profiles/default_profile.png';

                $query = "
                    SELECT q.id, q.title, u.email, u.profile_image, q.created_at
                    FROM questions q
                    JOIN users u ON q.user_id = u.id
                    ORDER BY q.created_at DESC
                ";

                if (isset($_GET["c-id"])) {
                    $cid = intval($_GET["c-id"]);
                    $query = "
                        SELECT q.id, q.title, u.email, u.profile_image, q.created_at
                        FROM questions q
                        JOIN users u ON q.user_id = u.id
                        WHERE q.category_id = ?
                        ORDER BY q.created_at DESC
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $cid);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    $result = $conn->query($query);
                }

                if ($result && $result->num_rows > 0) {
                    foreach ($result as $row) {
                        $title = htmlspecialchars($row["title"]);
                        $id = htmlspecialchars($row["id"]);
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
                        echo "<h4 class='my_questions mb-1'>";
                        echo "<a href='?q-id=$id' title='Click here to view and give answers'>" . ucfirst($title) . "</a>";
                        echo "</h4>";
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
            <?php include("categorylist.php"); ?>
        </div>
    </div>
</div>