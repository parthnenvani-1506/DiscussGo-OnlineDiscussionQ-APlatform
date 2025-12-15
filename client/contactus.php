<div class="container-fluid p-0">
  <div class="banner position-relative text-center text-white">
    <img src="./public/img26.jpg" class="img-fluid w-100" alt="Banner Image">
    <div class="banner-text position-absolute top-50 start-50 translate-middle">
      <h2 class="fw-bold display-5">Contact Us</h2>
    </div>
  </div>
</div>

<div class="container my-5">
  <div class="p-4 rounded-3 shadow-sm" 
       style="background-color:#fff; border-left:6px solid #0d6efd; max-width:500px; margin:auto;">
    

    <h2 class="mb-4 text-primary fw-bold">Get in Touch</h2>
    <p class="text-muted mb-4">
      Whether you have a question about our services, need assistance, 
      or just want to share your feedback, our team is ready to listen.  
      Use the form below to reach out, and we’ll get back to you shortly.</p>
    
  
    <form action="./server/request.php" method="post">
      
   
      <div class="mb-3">
        <label for="name" class="form-label fw-semibold">Your Name</label>
       <input type="text" name="name" class="form-control" id="name" required>
      </div>

       <div class="mb-3">
        <label for="email" class="form-label fw-semibold">Your Email</label>
        <input type="email" name="email" class="form-control" id="email" required>
      </div>
      
     <div class="mb-3">
        <label for="message" class="form-label fw-semibold">Your Message</label>
            <textarea name="message" class="form-control" id="message" rows="5" required></textarea>
     </div>
     <div class="d-grid mt-4">
        <button type="submit" name="contact_submit" class="btn btn-primary btn-lg">Send Message</button>
      </div>
      <div class="contact-card mb-3">
    <h5>Other Ways to Connect</h5>
    <p class="text-muted mb-1">Email: <a href="mailto:dicussgo@example.com">dicussgo@example.com</a></p>
    <p class="text-muted">Phone: +91 11111 XXXXX</p>
</div>

</div>