<?php

// Set up form submission logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $question = $_POST['question'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "questions_db");

    // Check for database connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the question already exists
    $sql_check_duplicate = "SELECT * FROM questions WHERE email = ? AND question = ?";
    $stmt_check = $conn->prepare($sql_check_duplicate);
    $stmt_check->bind_param("ss", $email, $question);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    // Set a flag for duplicate message
    $duplicate_message = false;
    if ($result_check->num_rows > 0) {
        $duplicate_message = true;
    } else {
        // Prepare and bind the insert query
        $stmt = $conn->prepare("INSERT INTO questions (email, question, timestamp) VALUES (?, ?, NOW())");

        // Check if statement preparation was successful
        if ($stmt === false) {
            die('Error preparing statement: ' . $conn->error);
        }

        // Bind parameters (email and question as strings)
        $stmt->bind_param("ss", $email, $question);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect after successful post to prevent re-posting on refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error posting question: " . $stmt->error . "<br>";
        }
    }

    // Close the statement and connection
    $stmt_check->close();
    $conn->close();
}

// Database connection to fetch questions
$conn = new mysqli("localhost", "root", "", "questions_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct questions to avoid displaying the same question multiple times
$sql = "SELECT email, question, timestamp FROM questions GROUP BY question ORDER BY timestamp DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask a Question</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom Properties based on Ansys colors */
        :root {
          --primary-color: #ED1C24; /* Ansys Red */
          --secondary-color: #FDFDFD; /* Off-White */
          --gradient-bg: linear-gradient(135deg, #000000, #333333); /* Black to Dark Gray */
          --dark-bg: #000000; /* Black */
          --light-bg: #F5F6F5; /* Light Gray */
          --text-dark: #000000; /* Black */
          --text-muted: #333333; /* Dark Gray */
          --shadow-sm: 0 4px 20px rgba(0, 0, 0, 0.1);
          --transition: all 0.3s ease;
        }

        /* Reset */
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: 'Poppins', Arial, sans-serif;
          background-color: var(--light-bg);
          color: var(--text-muted);
          line-height: 1.6;
          perspective: 1000px; /* Enables 3D effect */
        }

        /* Navbar */
        .navbar {
          position: sticky;
          top: 0;
          z-index: 1000;
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 1rem 3rem;
          background: var(--secondary-color);
          box-shadow: var(--shadow-sm);
        }

        .navbar-left img {
          height: 40px;
          transition: var(--transition);
        }

        .navbar-left img:hover {
          transform: scale(1.1);
        }

        .navbar-right ul {
          display: flex;
          list-style: none;
          gap: 2rem;
          align-items: center;
        }

        .navbar-right a {
          color: var(--text-dark);
          text-decoration: none;
          font-size: 1rem;
          font-weight: 500;
          transition: var(--transition);
        }

        .navbar-right a:hover {
          color: var(--primary-color);
        }

        .dropdown {
          position: relative;
        }

        .dropdown .dropbtn {
          background: transparent;
          color: var(--text-dark);
          border: none;
          cursor: pointer;
          font-size: 1rem;
          font-weight: 500;
          transition: var(--transition);
        }

        .dropdown:hover .dropbtn {
          color: var(--primary-color);
        }

        .dropdown-content {
          display: none;
          position: absolute;
          background: var(--secondary-color);
          min-width: 12rem;
          box-shadow: var(--shadow-sm);
          border-radius: 8px;
          transform: translateY(-10px);
          opacity: 0;
          transition: var(--transition);
        }

        .dropdown:hover .dropdown-content {
          display: block;
          transform: translateY(0);
          opacity: 1;
        }

        .dropdown-content a {
          color: var(--text-dark);
          padding: 0.75rem 1rem;
          display: block;
        }

        .dropdown-content a:hover {
          color: var(--primary-color);
          background: #f0f0f0;
        }

        /* Hero Section */
        .hero {
          background: var(--gradient-bg);
          color: var(--secondary-color);
          text-align: center;
          padding: 6rem 2rem;
          position: relative;
          overflow: hidden;
        }

        .hero h1 {
          font-size: 3.5rem;
          font-weight: 700;
          margin-bottom: 1rem;
          animation: fadeInUp 1s ease;
        }

        .hero p {
          font-size: 1.25rem;
          max-width: 40rem;
          margin: 0 auto 2rem;
          animation: fadeInUp 1s ease 0.2s backwards;
        }

        .hero .cta-button {
          font-size: 1.1rem;
          padding: 1rem 2rem;
          animation: fadeInUp 1s ease 0.4s backwards;
        }

        @keyframes fadeInUp {
          from { opacity: 0; transform: translateY(20px); }
          to { opacity: 1; transform: translateY(0); }
        }

        /* CTA Button */
        .cta-button {
          background: var(--primary-color);
          color: var(--secondary-color);
          padding: 0.75rem 1.5rem;
          border-radius: 25px;
          text-decoration: none;
          font-weight: 600;
          transition: var(--transition);
        }

        .cta-button:hover {
          background: #C81018;
          transform: translateY(-2px);
        }

        /* Content */
        .content {
          max-width: 75rem;
          margin: 0 auto;
          padding: 4rem 2rem;
          display: grid;
          gap: 3rem;
        }

        .section {
          background: var(--secondary-color);
          padding: 2rem;
          border-radius: 12px;
          box-shadow: var(--shadow-sm);
          border: 2px solid transparent;
          position: relative;
          transition: var(--transition);
        }

        .section:hover {
          border: 2px solid var(--primary-color);
        }

        .section h2 {
          color: var(--text-dark);
          font-size: 2rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
        }

        .section label {
          font-size: 1rem;
          color: var(--text-muted);
          display: block;
          margin-bottom: 0.5rem;
        }

        .section input,
        .section textarea {
          width: 100%;
          padding: 0.75rem;
          margin-bottom: 1.5rem;
          border-radius: 8px;
          border: 1px solid var(--text-muted);
          font-family: 'Poppins', sans-serif;
          font-size: 1rem;
          transition: var(--transition);
        }

        .section input:focus,
        .section textarea:focus {
          border-color: var(--primary-color);
          outline: none;
          box-shadow: 0 0 5px rgba(237, 28, 36, 0.3);
        }

        .section button {
          background: var(--primary-color);
          color: var(--secondary-color);
          padding: 0.75rem 1.5rem;
          border: none;
          border-radius: 25px;
          cursor: pointer;
          font-size: 1rem;
          font-weight: 600;
          transition: var(--transition);
        }

        .section button:hover {
          background: #C81018;
          transform: translateY(-2px);
        }

        /* Question List */
        .question-list {
          margin-top: 2rem;
          padding: 0 2rem;
        }

        .question-list h2 {
          color: var(--text-dark);
          font-size: 2rem;
          font-weight: 600;
          margin-bottom: 1.5rem;
        }

        .question-item-container {
          display: grid;
          gap: 1rem;
        }

        .question-item {
          background: var(--secondary-color);
          padding: 1.9rem;
          border-radius: 10px;
          box-shadow: var(--shadow-sm);
          border: 2px solid transparent;
          transition: var(--transition);
        }

        .question-item:hover {
          border: 2px solid var(--primary-color);
        }

        .question-item h4 {
          color: var(--text-dark);
          font-size: 1.25rem;
          margin-bottom: 0.5rem;
        }

        .question-item p {
          font-size: 1rem;
          color: var(--text-muted);
        }

        .question-item .meta {
          font-size: 0.9rem;
          color: #999;
          text-align: right;
          margin-top: 0.5rem;
        }

        /* Popup Styling */
        .popup {
          display: none;
          position: fixed;
          top: 20%;
          left: 50%;
          transform: translate(-50%, -50%);
          background-color: var(--primary-color);
          color: white;
          padding: 1rem 2rem;
          border-radius: 8px;
          box-shadow: var(--shadow-sm);
          z-index: 1000;
          font-size: 1.1rem;
          font-weight: 500;
          opacity: 0;
          transition: opacity 0.3s ease;
        }

        .popup.show {
          display: block;
          opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
          .navbar {
            flex-direction: column;
            padding: 1rem;
          }

          .navbar-right ul {
            flex-direction: column;
            gap: 1rem;
            margin-top: 1rem;
          }

          .hero {
            padding: 4rem 1rem;
          }

          .hero h1 {
            font-size: 2.5rem;
          }

          .hero p {
            font-size: 1rem;
          }

          .content {
            padding: 2rem 1rem;
          }

          .section h2 {
            font-size: 1.5rem;
          }

          .question-list {
            padding: 0 1rem;
          }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <a href="index.html"><img src="ansys-logo.jpg" alt="Ansys Logo"></a>
        </div>
        <div class="navbar-right">
            <ul>
                <li><a href="index.html">Home</a></li>
                <li class="dropdown">
                    <button class="dropbtn">Tutorials</button>
                    <div class="dropdown-content">
                        <a href="Ansys_Basics.html">1. Ansys Basics</a>
                        <a href="Ansys_Operations.html">2. Ansys Operations</a>
                        <a href="Modeling&Meshing.html">3. Modeling & Meshing</a>
                        <a href="Structural_Analysis.html">4. Structural Analysis</a>
                    </div>
                </li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="pattern.html">Pattern</a></li>
                <li><a href="index.php">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Ask a Question</h1>
        <p>Submit your question and join the discussion below.</p>
        <a href="#ask" class="cta-button">Get Started</a>
    </section>

    <div class="content" id="ask">
        <div class="section">
            <h2>Submit Your Question</h2>
            <form method="POST" action="index.php">
                <label for="email">Your email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="question">Your Question</label>
                <textarea id="question" name="question" rows="5" placeholder="Type your question here" required></textarea>

                <button type="submit">Submit</button>
            </form>
        </div>
    </div>

    <div class="question-list">
        <h2>Previously Asked Questions</h2>
        <div class="question-item-container">
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="question-item">';
                    echo '<h4>' . htmlspecialchars($row['question']) . '</h4>';
                    echo '<p>' . htmlspecialchars($row['email']) . '</p>';
                    echo '<p class="meta">' . date('F j, Y, g:i a', strtotime($row['timestamp'])) . '</p>';
                    echo '</div>';
                }
            } else {
                echo "No questions available.";
            }
            ?>
        </div>
    </div>

    <!-- Popup Message -->
    <div id="popup" class="popup">This question has already been submitted.</div>

    <!-- JavaScript for Popup -->
    <script>
        <?php if (isset($duplicate_message) && $duplicate_message): ?>
            document.addEventListener("DOMContentLoaded", function() {
                var popup = document.getElementById("popup");
                popup.classList.add("show");
                setTimeout(function() {
                    popup.classList.remove("show");
                }, 3000); // Hide after 3 seconds
            });
        <?php endif; ?>
    </script>
</body>
</html>