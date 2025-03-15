<?php
include '../session_start.php';
include '../../dbconnection/dbconnect.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    // Validate input
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email)) {
        echo "<script>
                alert('Please fill in all fields.');
                window.location.href = '../../src/Main_Pages/profile.php';
              </script>";
        exit();
    }

    // Check if passwords match (if a new password is entered)
    if (!empty($password) && $password !== $repassword) {
        echo "<script>
                alert('Passwords do not match.');
                window.location.href = '../../src/Main_Pages/profile.php';
              </script>";
        exit();
    }

    // Hash password if changed
    $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    // Handle file upload (profile picture)
    $profile_image = $_SESSION['profile_image'] ?? null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
        $target_dir = "../../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]); // Unique filename
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo "<script>
                    alert('Only JPG, JPEG, PNG, and GIF files are allowed.');
                    window.location.href = '../../src/Main_Pages/profile.php';
                  </script>";
            exit();
        }

        // Check for upload errors
        if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
            echo "<script>
                    alert('Upload error: " . $_FILES["profile_image"]["error"] . "');
                    window.location.href = '../../src/Main_Pages/profile.php';
                  </script>";
            exit();
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $file_name;
            $_SESSION['profile_image'] = $profile_image; // Update session with new image
        } else {
            echo "<script>
                    alert('Error uploading image.');
                    window.location.href = '../../src/Main_Pages/profile.php';
                  </script>";
            exit();
        }
    }

    // Construct update query
    $query = "UPDATE users SET firstname=?, lastname=?, username=?, email=?";
    $params = [$firstname, $lastname, $username, $email];
    $types = "ssss";

    if ($hashed_password) {
        $query .= ", password=?";
        $params[] = $hashed_password;
        $types .= "s";
    }

    if ($profile_image) {
        $query .= ", profile_image=?";
        $params[] = $profile_image;
        $types .= "s";
    }

    $query .= " WHERE id=?";
    $params[] = $user_id;
    $types .= "i";

    // Execute prepared statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;

        echo "<script>
                alert('Profile updated successfully!');
                var profilePic = document.getElementById('profilePic');
                if (profilePic) {
                    profilePic.src = '../../uploads/$profile_image' + '?' + new Date().getTime();
                }
                window.location.href = '../../src/Main_Pages/profile.php';
              </script>";
    } else {
        echo "<script>
                alert('Error updating profile. Please try again.');
                window.location.href = '../../src/Main_Pages/profile.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
