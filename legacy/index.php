<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Project</title>
    <link rel="icon" type="image/png" href="./public/adminlogo2.jpeg">
    <?php include("./client/common.php"); ?>
</head>
<body>
    <main>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include("./client/header.php");

    $messageText = "";
    $messageType = "info"; 

    if (isset($_GET['msg'])) {
        $msg = htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8');

        switch ($msg) {
            case "signup_success":
                $messageText = "Signup successful! Welcome to DiscussGo.";
                $messageType = "success";
                break;
            case "signup_error":
                $messageText = "Signup failed! Please try again.";
                $messageType = "error";
                break;
            case "login_success":
                $messageText = "Login successful! Welcome back.";
                $messageType = "success";
                break;
            case "login_error":
                $messageText = "Invalid email or password!";
                $messageType = "error";
                break;
            case "logout_success":
                $messageText = "You have been logged out.";
                $messageType = "info";
                break;
            case "login_required":
            case "login_required_for_profile":
                $messageText = "Please log in to access this feature.";
                $messageType = "error";
                break;
            case "question_added":
                $messageText = "Your question has been posted successfully!";
                $messageType = "success";
                break;
            case "question_error":
                $messageText = "Failed to post question.";
                $messageType = "error";
                break;
            case "questions_deleted":
                $messageText = "Question deleted successfully!";
                $messageType = "success";
                break;
            case "questions_deleted_error":
                $messageText = "Failed to delete question.";
                $messageType = "error";
                break;
            case "unauthorized_delete":
                $messageText = "You are not authorized to delete this question.";
                $messageType = "error";
                break;
            case "question_not_found":
                $messageText = "Question not found.";
                $messageType = "error";
                break;
            case "answer_added":
                $messageText = "Your answer has been added!";
                $messageType = "success";
                break;
            case "answer_updated":
                $messageText = "Answer updated successfully.";
                $messageType = "success";
                break;
            case "answer_update_failed":
                $messageText = "Answer update failed.";
                $messageType = "error";
                break;
            case "answer_deleted":
                $messageText = "Answer deleted successfully.";
                $messageType = "success";
                break;
            case "answer_error":
                $messageText = "Failed to post answer.";
                $messageType = "error";
                break;
            case "empty_answer":
                $messageText = "Answer cannot be empty.";
                $messageType = "error";
                break;
            case "profile_updated":
                $messageText = "Your profile has been updated successfully!";
                $messageType = "success";
                break;
            case "profile_update_failed":
                $messageText = "Failed to update profile. Please try again.";
                $messageType = "error";
                break;
            case "image_upload_failed":
                $messageText = "Image upload failed. Please try again.";
                $messageType = "error";
                break;
            case "invalid_image_format_or_size":
                $messageText = "Invalid image format or size. Please use JPG, PNG, or GIF up to 5MB.";
                $messageType = "error";
                break;
            case "no_changes":
                $messageText = "No changes were detected for your profile.";
                $messageType = "info";
                break;
            case "db_prepare_error":
                $messageText = "Database error occurred. Please try again later.";
                $messageType = "error";
                break;
            case "contact_success":
                $messageText = "Thank you for your message!";
                $messageType = "success";
                break;
            case "contact_error":
                $messageText = "Please login or SignUp for Contact to us.";
                $messageType = "error";
                break;
            case "email_exists":
                $messageText = "This email is already registered. Please log in or use a different email.";
                $messageType = "error";
                break;
            default:
                $messageText = "An operation completed: " . str_replace("_", " ", $msg);
                $messageType = "info";
                break;
        }

        if (!empty($messageText)) {
            echo "<div id='popup' class='popup-message $messageType'>$messageText</div>";
        }
    }
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const popup = document.getElementById('popup');
            if (popup) {
                setTimeout(() => popup.classList.add('show'), 100);
                setTimeout(() => {
                    popup.classList.remove('show');
                    popup.addEventListener('transitionend', () => {
                        if (!popup.classList.contains('show')) {
                            popup.remove();
                        }
                    }, { once: true });
                }, 5000);

                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('msg')) {
                    urlParams.delete('msg');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState({}, document.title, newUrl);
                }
            }
        });
    </script>

    <?php
    if (isset($_GET['signup']) && empty($_SESSION['user']['user_id'])) {
        include("./client/signup.php");
    } elseif (isset($_GET['login']) && empty($_SESSION['user']['user_id'])) {
        include("./client/login.php");
    } elseif (isset($_GET['ask_question'])) {
        include("./client/ask_question.php");
    } elseif (isset($_GET['q-id'])) {
        $qid = (int) $_GET['q-id'];
        include("./client/question_details.php");
    } elseif (isset($_GET['c-id'])) {
        $cid = (int) $_GET['c-id'];
        if (isset($_GET['latest'])) {
            include("./client/latest_questions.php");
        } elseif (isset($_GET['u-id'])) {
            include("./client/myquestions.php");
        } else {
            include("./client/questions.php");
        }
    } elseif (isset($_GET['u-id'])) {
        $uid = (int) $_GET['u-id'];
        include("./client/myquestions.php");
    } elseif (isset($_GET['contact'])) {
        include("./client/contactus.php");
    } elseif (isset($_GET['profile'])) {
        include("./client/profile.php");
    } elseif (isset($_GET['latest'])) {
        include("./client/latest_questions.php");
    } elseif (isset($_GET['search'])) {
        $search = htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8');
        include("./client/latest_questions.php");
    } else {
        include("./client/questions.php");
    }
    ?>
    </main>

    <?php include("./client/footer.php"); ?>
</body>
</html>