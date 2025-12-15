<?php
session_start();
include("../common/db.php");

// Define a path for profile images
$profile_upload_dir = '../public/profiles/';

// Ensure the directory exists
if (!is_dir($profile_upload_dir)) {
    mkdir($profile_upload_dir, 0777, true);
}

/* -------------------------------
   USER SIGNUP
--------------------------------*/
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = md5($_POST['password']);
    $city = trim($_POST['city']);

    $default_profile_image = 'default_profile.png';

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        header("Location: /discussgo/?signup=true&msg=email_exists");
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    $stmt = $conn->prepare("INSERT INTO users (user_name, email, password, city, profile_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $city, $default_profile_image);

    if ($stmt->execute()) {
        $_SESSION["user"] = [
            "user_name" => $username,
            "email" => $email,
            "user_id" => $stmt->insert_id,
            "profile_image" => $default_profile_image,
            "city" => $city
        ];
        header("Location: /discussgo?msg=signup_success");
    } else {
        header("Location: /discussgo/?signup=true&msg=signup_error");
    }
    $stmt->close();
    exit();
}

/* -------------------------------
   USER LOGIN
--------------------------------*/
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = md5($_POST['password']);

    $stmt = $conn->prepare("SELECT id, user_name, email, profile_image, city FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION["user"] = [
            "user_name" => $row['user_name'],
            "email" => $row['email'],
            "user_id" => $row['id'],
            "profile_image" => $row['profile_image'],
            "city" => $row['city']
        ];
        header("Location: /discussgo?msg=login_success");
    } else {
        header("Location: /discussgo/?login=true&msg=login_error");
    }
    $stmt->close();
    exit();
}

/* -------------------------------
   LOGOUT
--------------------------------*/
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: /discussgo?msg=logout_success");
    exit();
}

/* -------------------------------
   ASK QUESTION
--------------------------------*/
if (isset($_POST["ask"])) {
    if (!isset($_SESSION['user']['user_id'])) {
        header("Location: /discussgo/?login=true&msg=login_required");
        exit();
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category'];
    $user_id = $_SESSION['user']['user_id'];

    $stmt = $conn->prepare("INSERT INTO questions (title, description, category_id, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $description, $category_id, $user_id);

    if ($stmt->execute()) {
        header("Location: /discussgo?ask_question=true&msg=question_added");
    } else {
        header("Location: /discussgo/?ask_question=true&msg=question_error");
    }
    $stmt->close();
    exit();
}

/* -------------------------------
   ADD OR UPDATE ANSWER
--------------------------------*/
if (isset($_POST["answer"])) {
    if (!isset($_SESSION['user']['user_id'])) {
        header("Location: /discussgo/?login=true&msg=login_required");
        exit();
    }

    $answer = trim($_POST['answer']);
    if (empty($answer)) {
        header("Location: /discussgo/?q-id=" . (int)$_POST['question_id'] . "&msg=empty_answer");
        exit();
    }

    $question_id = (int)$_POST['question_id'];
    $user_id = $_SESSION['user']['user_id'];
    $answer_id = isset($_POST['answer_id']) ? (int)$_POST['answer_id'] : 0;

    if ($answer_id > 0) {
        $stmt = $conn->prepare("UPDATE answers SET answer=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sii", $answer, $answer_id, $user_id);
        $msg = $stmt->execute() ? "answer_updated" : "answer_update_failed";
    } else {
        $stmt = $conn->prepare("INSERT INTO answers (answer, question_id, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $answer, $question_id, $user_id);
        $msg = $stmt->execute() ? "answer_added" : "answer_error";
    }
    $stmt->close();
    header("Location: /discussgo/?q-id=$question_id&msg=$msg");
    exit();
}

/* -------------------------------
   DELETE ANSWER
--------------------------------*/
if (isset($_GET['action']) && $_GET['action'] === "delete_answer") {
    if (!isset($_SESSION['user']['user_id'])) {
        header("Location: /discussgo/?login=true&msg=login_required");
        exit();
    }

    $answerId = (int)$_GET['id'];
    $qid = (int)$_GET['qid'];
    $userId = $_SESSION['user']['user_id'];

    $stmt = $conn->prepare("DELETE FROM answers WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $answerId, $userId);
    $stmt->execute();

    header("Location: /discussgo/?q-id=$qid&msg=answer_deleted");
    exit();
}

/* -------------------------------
   DELETE QUESTION
--------------------------------*/
if (isset($_GET["delete"])) {
    if (!isset($_SESSION['user']['user_id'])) {
        header("Location: /discussgo/?login=true&msg=login_required");
        exit();
    }

    $qid = (int)$_GET["delete"];
    $uid = $_SESSION['user']['user_id'];

    $stmt = $conn->prepare("SELECT user_id FROM questions WHERE id=?");
    $stmt->bind_param("i", $qid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        header("Location: /discussgo/?u-id=$uid&msg=question_not_found");
        exit();
    }
    $owner = $res->fetch_assoc();
    if ($owner['user_id'] != $uid) {
        header("Location: /discussgo/?u-id=$uid&msg=unauthorized_delete");
        exit();
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM questions WHERE id=?");
    $stmt->bind_param("i", $qid);
    $msg = $stmt->execute() ? "questions_deleted" : "questions_deleted_error";
    $stmt->close();

    header("Location: /discussgo/?u-id=$uid&msg=$msg");
    exit();
}

/* -------------------------------
   CONTACT FORM
--------------------------------*/
if (isset($_POST['contact_submit'])) {
    if (isset($_SESSION['user']['user_id'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    $stmt->execute();
    $stmt->close();

    header("Location: /discussgo/?msg=contact_success");
    exit();
    }
    else{
        header("Location: /discussgo/?msg=contact_error");
    }
}

/* -------------------------------
   ANSWER LIKE/UNLIKE
--------------------------------*/
if (isset($_POST['action']) && $_POST['action'] === "answer_like" && isset($_POST['answer_id'])) {
    if (!isset($_SESSION['user']['user_id'])) {
        echo "not_logged_in";
        exit();
    }

    $answer_id = (int)$_POST['answer_id'];
    $user_id = $_SESSION['user']['user_id'];

    $check = $conn->prepare("SELECT 1 FROM answer_likes WHERE user_id=? AND answer_id=?");
    $check->bind_param("ii", $user_id, $answer_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM answer_likes WHERE user_id=? AND answer_id=?");
        $stmt->bind_param("ii", $user_id, $answer_id);
        $stmt->execute();
        echo "unliked";
    } else {
        $stmt = $conn->prepare("INSERT INTO answer_likes (user_id, answer_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $answer_id);
        $stmt->execute();
        echo "liked";
    }
    exit();
}

/* -------------------------------
   UPDATE PROFILE
--------------------------------*/
if (isset($_POST['action']) && $_POST['action'] === "update_profile" && isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $user_name = trim($_POST['user_name']);
    $city = trim($_POST['city']);
    $current_profile_image = $_SESSION['user']['profile_image'];

    $fields = [];
    $types = "";
    $values = [];

    if (!empty($user_name) && $user_name !== $_SESSION['user']['user_name']) {
        $fields[] = "user_name=?";
        $types .= "s";
        $values[] = $user_name;
        $_SESSION['user']['user_name'] = $user_name;
    }

    if (!empty($city) && $city !== $_SESSION['user']['city']) {
        $fields[] = "city=?";
        $types .= "s";
        $values[] = $city;
        $_SESSION['user']['city'] = $city;
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_name = $_FILES['profile_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['profile_image']['size'];

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024;

        if (in_array($file_ext, $allowed) && $file_size <= $max_size) {
            $new_name = uniqid('profile_') . '.' . $file_ext;
            $upload_path = $profile_upload_dir . $new_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                if ($current_profile_image !== 'default_profile.png' && file_exists($profile_upload_dir . $current_profile_image)) {
                    unlink($profile_upload_dir . $current_profile_image);
                }
                $fields[] = "profile_image=?";
                $types .= "s";
                $values[] = $new_name;
                $_SESSION['user']['profile_image'] = $new_name;
            } else {
                header("Location: ../?profile=true&msg=image_upload_failed");
                exit();
            }
        } else {
            header("Location: ../?profile=true&msg=invalid_image_format_or_size");
            exit();
        }
    }

    if (!empty($fields)) {
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
        $types .= "i";
        $values[] = $user_id;

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                header("Location: ../?profile=true&msg=profile_updated");
            } else {
                header("Location: ../?profile=true&msg=profile_update_failed");
            }
            $stmt->close();
        } else {
            header("Location: ../?profile=true&msg=db_prepare_error");
        }
    } else {
        header("Location: ../?profile=true&msg=no_changes");
    }
    exit();
}

/* -------------------------------
   DELETE PROFILE IMAGE
--------------------------------*/
if (isset($_POST['action']) && $_POST['action'] === "delete_profile_image" && isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
    $current_profile_image = $_SESSION['user']['profile_image'];

    if ($current_profile_image !== 'default_profile.png' && file_exists($profile_upload_dir . $current_profile_image)) {
        unlink($profile_upload_dir . $current_profile_image);
    }

    $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE id=?");
    $default_profile = 'default_profile.png';
    $stmt->bind_param("si", $default_profile, $user_id);
    if ($stmt->execute()) {
        $_SESSION['user']['profile_image'] = $default_profile;
        header("Location: ../?profile=true&msg=image_deleted");
    } else {
        header("Location: ../?profile=true&msg=image_delete_failed");
    }
    $stmt->close();
    exit();
}
?>