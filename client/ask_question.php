<!-- Banner Section -->
<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img28.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">Ask a Questions</h2>
    </div>
  </div>
</div>

<div class="container my-5">
  <div class="p-4 rounded-3 shadow-sm" style="background-color:#fff; border-left:6px solid #0d6efd; max-width:700px; margin:auto;">
    
    <!-- Heading -->
    <h2 class="mb-4 text-primary fw-bold">Ask a Question</h2>
    <p class="text-muted mb-4">Have a doubt or want to share something with the community? Use this section to ask your question. 
      Be clear and specific so others can easily understand and give you helpful answers. 
      Once posted, your question will be visible to everyone, and anyone in the community can respond with their knowledge and experience.</p>
    
    <!-- Form -->
    <form action="./server/request.php" method="post">
      
      <!-- Title -->
      <div class="mb-3">
        <label for="title" class="form-label fw-semibold">Title</label>
        <input type="text" name="title" class="form-control" id="title" placeholder="Enter your question" required>
      </div>

      <!-- Description -->
      <div class="mb-3">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" id="description" rows="4" placeholder="Explain your question in detail" required></textarea>
      </div>

      <!-- Category -->
      <div class="mb-3">
        <label for="category" class="form-label fw-semibold">Category</label>
        <?php include("category.php"); ?>
      </div>

      <!-- Submit -->
      <div class="d-grid mt-4">
        <button type="submit" name="ask" class="btn btn-primary btn-lg">Ask Question</button>
      </div>

    </form>
  </div>
</div>