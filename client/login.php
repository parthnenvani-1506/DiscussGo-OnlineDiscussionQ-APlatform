<!-- Banner Section -->
<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img12.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">Login</h2>
    </div>
  </div>
</div>

<!-- Login Form Section -->
<div class="container my-5">
  <div class="p-4 rounded-3 shadow-sm" 
       style="background-color:#fff; border-left:6px solid #0d6efd; max-width:500px; margin:auto;">
    
    <!-- Heading -->
    <h2 class="mb-4 text-primary fw-bold">Login</h2>
    <p class="text-muted mb-4">
      Welcome back! If you already have an account, log in using your correct username and password. Once you’re logged in, you’ll be able to share your questions with the community. Other members can view your questions and respond with helpful answers, making it easier for you to get the information you need.
    </p>

    <!-- Form -->
    <form action="./server/request.php" method="post">
      
      <!-- Email -->
      <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Email</label>
        <input type="email" name="email" id="email" 
               class="form-control form-control-lg" 
               placeholder="Enter your email" required>
      </div>
      
      <!-- Password -->
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" name="password" id="password" 
               class="form-control form-control-lg" 
               placeholder="Enter your password" required>
      </div>
      <div class="text-center mt-3">
    <p>
        Don't have an account? 
        <a href="http://localhost/discussgo/?signup=true" class="text-primary fw-bold" style="text-decoration:nome;">Sign up here</a>
    </p>
</div>
      <!-- Submit -->
      <div class="d-grid mt-4">
        <button type="submit" name="login" class="btn btn-primary btn-lg">Login</button>
      </div>
      
    </form>
  </div>
</div>