<?php
// create_admin.php
$host = "localhost";
$db = "coffee_shop";
$user = "postgres";
$pass = "postgres";
$port = "5432";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Admin Details
    $admin_id = "0000-0000";
    $admin_email = "admin@coffee.com";
    $admin_password = "Admin_password123"; // Change this!
    $role = "admin";

    // Hash Password and Security Answers (to match your signup.php logic)
    $hashed_pass = password_hash($admin_password, PASSWORD_DEFAULT);
    $hashed_a1 = password_hash("AdminCity", PASSWORD_DEFAULT);
    $hashed_a2 = password_hash("Coffee", PASSWORD_DEFAULT);
    $hashed_a3 = password_hash("Black", PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (
                id_number, first_name, last_name, email, password, role, 
                age, sex, contact, dob, purok, barangay, city_municipality, 
                province, country, zip_code, 
                question1, answer1, question2, answer2, question3, answer3
            ) VALUES (
                :id, 'System', 'Admin', :email, :pass, :role, 
                30, 'Other', '0000000000', '1990-01-01', 'N/A', 'N/A', 'N/A', 
                'N/A', 'Philippines', '0000', 
                'In what city were you born?', :a1, 
                'What is your favorite food?', :a2, 
                'What is your favorite color?', :a3
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'id' => $admin_id,
        'email' => $admin_email,
        'pass' => $hashed_pass,
        'role' => $role,
        'a1' => $hashed_a1,
        'a2' => $hashed_a2,
        'a3' => $hashed_a3
    ]);

    echo "Admin created successfully!";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>