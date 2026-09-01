<!-- Banner Section -->
<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img12.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">Signup</h2>
    </div>
  </div>
</div>

<!-- Login Form Section -->
<div class="container my-5">
  <div class="p-4 rounded-3 shadow-sm" 
       style="background-color:#fff; border-left:6px solid #0d6efd; max-width:500px; margin:auto;">
    
    <!-- Heading -->
    <h2 class="mb-4 text-primary fw-bold">Signup</h2>
    <p class="text-muted mb-4">
      Don’t have an account yet? Create one now by filling in your details. With your account, you can start asking questions, share your thoughts, and connect with others. Signing up takes only a minute, and it helps you become part of the community where everyone can learn and help each other.
    </p>

    <!-- Form -->
       <form method="post" action="./server/request.php">

        <!-- Username -->
        <div class="mb-3">
          <label for="username" class="form-label text-secondary">User Name</label>
          <input type="text" name="username" class="form-control form-control-lg" id="username" placeholder="Enter your name" required>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label text-secondary">Email</label>
          <input type="email" name="email" class="form-control form-control-lg" id="email" placeholder="Enter your email" required>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label text-secondary">Password</label>
          <input type="password" name="password" class="form-control form-control-lg" id="password" placeholder="Enter your password" required>
        </div>

        <!-- City -->
        <div class="mb-3">
          <label for="city" class="form-label text-secondary">City</label>
          <input type="text" name="city" class="form-control form-control-lg" id="city" placeholder="Enter your city" required>
        </div>

        <div class="text-center mt-3">
          <p>
            Already have an account? 
            <a href="http://localhost/discussgo/?login=true" class="text-primary fw-bold">Login here</a>
          </p>
        </div>

        <!-- Signup Button -->
        <div class="d-grid mt-4">
          <button type="submit" name="signup" class="btn btn-primary btn-lg">Signup</button>
        </div>
    </form>
  </div>
</div>