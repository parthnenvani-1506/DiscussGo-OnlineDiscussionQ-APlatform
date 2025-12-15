<?php
$activeCategoryId = isset($_GET['c-id']) ? (int)$_GET['c-id'] : 0;

$contextParams = '';
if (isset($_GET['latest'])) {
    $contextParams = '&latest=true';
}
if (isset($_GET['u-id'])) {
    $contextParams .= '&u-id=' . htmlspecialchars($_GET['u-id']);
}
?>
<div>
    <h1 class="heading_title">Categories</h1>
    <?php
    $query = "select * from category";
    $result = $conn->query($query);
    foreach($result as $row)
    {
        $name = htmlspecialchars($row["name"]);
        $id = htmlspecialchars($row['id']);
        $isActive = ($id == $activeCategoryId) ? 'active-category' : '';
        echo "<div class='question-list'>
        <h4><a href='?c-id=$id".$contextParams."' class='category-link ".$isActive."' data-category-id='$id'>".ucfirst($name)."</a></h4></div>";
    }
    ?>
</div>

<script src="./public/script.js"></script>
