<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-column">
      <h4>DiscussGo</h4>
      <p>Namaste! Welcome to DiscussGo — a place for real discussions, real people, and real solutions. Ask questions, share answers, and grow together.</p>
    </div>
  <?php if (isset($_SESSION['user']) && $_SESSION['user']['user_name']) { ?>
    <div class="footer-column">
      <h4>Links</h4>
      <a href="http://localhost/discussgo/">Home</a><br>
      <a href="http://localhost/discussgo/?ask_question=true">Ask a Question</a><br>
      <a href="http://localhost/discussgo/?u-id=<?php echo $_SESSION['user']['user_id'] ?>">My Question</a><br>
      <a href="http://localhost/discussgo/?latest=true">Latest Questions</a><br>
      <a href="http://localhost/discussgo/server/request.php/?logout=true">Logout</a><br>
    </div>
  <?php } else { ?>
    <div class="footer-column">
      <h4>Links</h4>
      <a href="http://localhost/discussgo/">Home</a><br>
      <a href="http://localhost/discussgo/?login=true">Login</a><br>
      <a href="http://localhost/discussgo/?signup=true">SignUp</a><br>
      <a href="http://localhost/discussgo/?latest=true">Latest Questions</a><br>
    </div>
  <?php } ?>

    <div class="footer-column">
      <h4>Popular Categories</h4>
      <a href="http://localhost/discussgo/?c-id=2">AI</a><br>
      <a href="http://localhost/discussgo/?c-id=3">Food</a><br>
      <a href="http://localhost/discussgo/?c-id=4">Games</a><br>
      <a href="http://localhost/discussgo/?c-id=1">Mobile</a><br>
      <a href="http://localhost/discussgo/?c-id=5">General</a>
    </div>
    <div class="footer-column">
      <h4>Get in Touch</h4>
      <a href="#">Facebook</a><br>
      <a href="#">Instagram</a><br>
      <a href="#">Whatsapp</a>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; 2025 DiscussGo(Nenvani Parth V.) | All Rights Reserved.
  </div>
</footer>