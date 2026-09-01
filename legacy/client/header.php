<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default profile image
$profileImagePath = "./public/profiles/default_profile.png";
if (isset($_SESSION['user']['profile_image']) && !empty($_SESSION['user']['profile_image'])) {
    $tempPath = "./public/profiles/" . $_SESSION['user']['profile_image'];
    if (file_exists($tempPath) && !is_dir($tempPath)) {
        $profileImagePath = $tempPath;
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand" href="./">
      <img src="./public/img19.png" class="logo_img" alt="Logo">
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu items -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <!-- Home -->
        <li class="nav-item">
          <a class="nav-link <?php echo (!isset($_GET['c-id']) && !isset($_GET['u-id']) && !isset($_GET['latest']) && !isset($_GET['signup']) && !isset($_GET['login']) && !isset($_GET['ask_question']) && !isset($_GET['q-id']) && !isset($_GET['contact'])) ? 'active' : ''; ?>" href="./">
            Home
          </a>
        </li>

        <?php if (isset($_SESSION['user']) && $_SESSION['user']['user_name']) { ?>
          <!-- Ask Question -->
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['ask_question'])) ? 'active' : ''; ?>" href="?ask_question=true">
              Ask a Question
            </a>
          </li>

          <!-- My Questions -->
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['u-id'])) ? 'active' : ''; ?>" href="?u-id=<?php echo $_SESSION['user']['user_id'] ?>">
              My Questions
            </a>
          </li>
        <?php } else { ?>
          <!-- Login -->
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['login'])) ? 'active' : ''; ?>" href="?login=true">
              Login
            </a>
          </li>

          <!-- Signup -->
          <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['signup'])) ? 'active' : ''; ?>" href="?signup=true">
              SignUp
            </a>
          </li>
        <?php } ?>

        <!-- Latest Questions -->
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_GET['latest'])) ? 'active' : ''; ?>" href="?latest=true">
            Latest Questions
          </a>
        </li>

        <!-- Contact Us -->
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_GET['contact'])) ? 'active' : ''; ?>" href="?contact=true">
            Contact Us
          </a>
        </li>
      </ul>

      <!-- Right-aligned user info -->
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['user_name']) { ?>
          <!-- Profile Image -->
          <li class="nav-item me-2">
            <a class="nav-link p-0" href="?profile=true">
              <img src="<?php echo $profileImagePath; ?>" alt="Profile" class="navbar-profile-img">
            </a>
          </li>

          <!-- Logout
          <li class="nav-item">
            <a class="nav-link text-danger fw-bold" href="./server/request.php?logout=true">
              Logout
            </a>
          </li> -->
        <?php } ?>
      </ul>
    </div>
  </div>
</nav>