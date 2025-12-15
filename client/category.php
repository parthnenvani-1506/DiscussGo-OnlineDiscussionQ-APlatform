<select name="category" id="category" class="form-select" required>
  <option value="" disabled selected hidden>Select a Category</option>
  <?php
      include("./common/db.php");
      $query="SELECT * FROM category";
      $result=$conn->query($query);
      foreach($result as $row){
          $name = ucfirst($row['name']);
          $id   = $row['id'];
          echo "<option value='$id'>$name</option>";
      }
  ?>
</select>