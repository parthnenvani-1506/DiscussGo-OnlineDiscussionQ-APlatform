<?php
// Ensure user is logged
include("./common/db.php");
if (!isset($_SESSION['user']['user_id'])) {
    header("Location: /discussgo/?login=true&msg=login_required_for_profile");
    exit();
}

$userId = $_SESSION['user']['user_id'];

// Fetch latest user data from DB
$stmt = $conn->prepare("SELECT user_name, email, city, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    $userName = htmlspecialchars($userData['user_name']);
    $userEmail = htmlspecialchars($userData['email']);
    $userCity = htmlspecialchars($userData['city'] ?? 'Not set');
    $profileImage = htmlspecialchars($userData['profile_image'] ?? 'default_profile.png');
} else {
    // If user not found, logout
    header("Location: ./logout.php");
    exit();
}
$stmt->close();

// Count user stats
$totalQuestions = 0;
$totalAnswers = 0;

// Total questions
$stmtQuestions = $conn->prepare("SELECT COUNT(*) AS total FROM questions WHERE user_id = ?");
$stmtQuestions->bind_param("i", $userId);
$stmtQuestions->execute();
$resultQuestions = $stmtQuestions->get_result();
if ($resultQuestions->num_rows > 0) {
    $totalQuestions = $resultQuestions->fetch_assoc()['total'];
}
$stmtQuestions->close();

// Total answers
$stmtAnswers = $conn->prepare("SELECT COUNT(*) AS total FROM answers WHERE user_id = ?");
$stmtAnswers->bind_param("i", $userId);
$stmtAnswers->execute();
$resultAnswers = $stmtAnswers->get_result();
if ($resultAnswers->num_rows > 0) {
    $totalAnswers = $resultAnswers->fetch_assoc()['total'];
}
$stmtAnswers->close();

// Path to profile image
$profileImagePath = './public/profiles/' . $profileImage;
if (!file_exists($profileImagePath) || is_dir($profileImagePath)) {
    $profileImagePath = './public/profiles/default_profile.png';
}
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <h1 class="text-center mb-4">Welcome, <?php echo $userName; ?>!</h1>

            <!-- Profile Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="<?php echo $profileImagePath; ?>?t=<?php echo time(); ?>" 
                         alt="Profile Image" class="profile-img-display mb-3">
                    <h3 class="card-title"><?php echo $userName; ?></h3>
                    <p class="card-text text-muted"><?php echo $userEmail; ?></p>
                    <p class="card-text"><strong>City:</strong> <?php echo $userCity; ?></p>
                    
                    <div class="row mt-4">
                        <div class="col-6">
                            <h5>Questions Posted</h5>
                            <span class="badge bg-primary fs-5"><?php echo $totalQuestions; ?></span>
                        </div>
                        <div class="col-6">
                            <h5>Answers Given</h5>
                            <span class="badge bg-info fs-5"><?php echo $totalAnswers; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Profile -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Update Your Profile</h4>
                </div>
                <div class="card-body">
                    <form action="./server/request.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Username</label>
                            <input type="text" class="form-control" id="user_name" name="user_name"
                                   value="<?php echo $userName; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                   value="<?php echo $userCity !== 'Not set' ? $userCity : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image"
                                   accept="image/jpeg, image/png, image/gif">
                            <div class="form-text">Max 5MB. JPG, PNG, GIF allowed.</div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-2">Update Profile</button>
                    </form>

                    <?php if ($profileImage !== 'default_profile.png') : ?>
                    <form action="./server/request.php" method="post" 
                          onsubmit="return confirm('Are you sure you want to delete your profile image?');">
                        <input type="hidden" name="action" value="delete_profile_image">
                        <button type="submit" class="btn btn-warning w-100 mb-2">Delete Profile Image</button>
                    </form>
                    <?php endif; ?>

                    <a class="btn btn-danger w-100" href="./server/request.php?logout=true">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>