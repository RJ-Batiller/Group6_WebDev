<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terms and Conditions - Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .terms-container {
            background: var(--container-bg);
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            margin: 20px auto;
        }
        .terms-section {
            margin-bottom: 20px;
        }
        .terms-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        ol {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="terms-container">
            <h2 class="text-center mb-4">Terms and Conditions</h2>
            
            <div class="terms-section">
                <h3><b>1. Library Membership</b></h3>
                <ol>
                    <li>Membership is open to all registered users.</li>
                    <li>Members must be at least 13 years old to register.</li>
                    <li>Each member is responsible for their account security.</li>
                    <li>Membership is non-transferable.</li>
                </ol>
            </div>

            <div class="terms-section">
                <h3><b>2. Borrowing Rules</b></h3>
                <ol>
                    <li>Members can borrow up to 3 books at a time.</li>
                    <li>The standard loan period is 14 days.</li>
                    <li>Late returns will incur fines of ₱5 per day per item.</li>
                    <li>Lost or damaged items must be replaced or paid for.</li>
                </ol>
            </div>

            <div class="terms-section">
                <h3><b>3. User Responsibilities</b></h3>
                <ol>
                    <li>Keep personal information up to date.</li>
                    <li>Report lost or stolen cards immediately.</li>
                    <li>Handle library materials with care.</li>
                    <li>Follow library rules and regulations.</li>
                </ol>
            </div>

            <div class="terms-section">
                <h3><b>4. Privacy Policy</b></h3>
                <ol>
                    <li>Personal information will be kept confidential.</li>
                    <li>Usage data may be collected for library improvement.</li>
                    <li>Information will not be shared with third parties.</li>
                    <li>Users can request their data deletion.</li>
                </ol>
            </div>

            <div class="terms-section">
                <h3><b>5. Account Termination</b></h3>
                <ol>
                    <li>Violation of terms may result in account suspension.</li>
                    <li>Users can request account deletion.</li>
                    <li>Outstanding fines must be settled before termination.</li>
                    <li>The library reserves the right to terminate accounts.</li>
                </ol>
            </div>

            <div class="text-center mt-4">
                <a href="register.php" class="btn btn-primary">Back to Registration</a>
            </div>
        </div>
    </div>
</body>
</html> 