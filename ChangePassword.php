<?php
session_start();
$UserID = $_SESSION["UserID"];
$IsLogin = true;

//session_destroy() ;

//$_SESSION["IsLogin"]="LoginPage";
include($_SERVER['DOCUMENT_ROOT'] . "/SerpDbConfig/config.php");

$sql = "select * from AdminLoginDetails where UserID='$UserID'";
$stmt = $iconn->query($sql);
$RowFetchArray = $stmt->fetch_array();
$UserName = $RowFetchArray['UserLoginID'];
$UserPassword = $RowFetchArray['Hashpassword'];

// echo $_SESSION["ClientPath"];
if (isset($_POST['submit'])) {


    $user_name = $_POST['username'];
    $user_password = $_POST['userpassword'];
    $cnfpassword = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);



    if(password_verify($user_password, $UserPassword)){

        $sql_org_insert = "UPDATE `AdminLoginDetails`
     SET `Hashpassword` = '$cnfpassword' WHERE `UserID` = '$UserID'";
        $sql_org_insert;

        $stmt1 = $iconn->query($sql_org_insert);

        if ($stmt1) {
          //  header('location:' . $_SESSION["ClientPath"]);
        //   echo "<script>alert('Passsord changed successfully, Please login again')</script>";
            echo "
            <script>
                alert('Password changed successfully. Please login again.');
                window.location.href = '/auth-login';
            </script>
            ";
            exit;
           //header('location:/auth-login');
        } else {
            echo "<script>alert('Passsord is Incorrect')</script>";
        }
    } else {
        echo "<script>alert('Passsord is Incorrect')</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Camaroo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            overflow: hidden;
        }

        /* Background Image */
        .background-image {
            position: absolute;
            inset: 0;
            background-image: url('https://images.unsplash.com/photo-1771530789155-b1f03fbf82b5?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx3YXJlaG91c2UlMjBsb2dpc3RpY3MlMjBpbnRlcmlvcnxlbnwxfHx8fDE3NzM2NjM4MjZ8MA&ixlib=rb-4.1.0&q=80&w=1080');
            background-size: cover;
            background-position: center;
            opacity: 0.15;
        }

        /* White Overlay */
        .overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom right, #ffffff, #f9fafb, #eff6ff);
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 28rem;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(40px);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(229, 231, 235, 0.5);
        }

        /* Lock Icon */
        .lock-icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            margin-top: 1.5rem;
            transform: scale(0);
            animation: scaleIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s forwards;
        }

        .lock-icon {
            width: 70%;
/*            height: 4rem;
            background: linear-gradient(to bottom right, #3b82f6, #4f46e5);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);*/
        }

        .lock-icon svg {
            width: 2rem;
            height: 2rem;
            color: white;
        }

        .lock-icon img {
            width: 100%;
        }

        /* Welcome Text */
        .welcome-text {
            text-align: center;
            margin-bottom: 2rem;
            opacity: 0;
            animation: fadeIn 0.5s ease-out 0.3s forwards;
        }

        .welcome-text h1 {
            color: #111827;
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Form */
        form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .form-group {
            opacity: 0;
            transform: translateX(-20px);
        }

        .form-group.username {
            animation: slideInLeft 0.4s ease-out 0.4s forwards;
        }

        .form-group.password,.form-group.newpassword {
            animation: slideInLeft 0.4s ease-out 0.5s forwards;
        }

        .form-group label {
            display: block;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .input-icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        input[type="username"],
        input[type="password"],
        input[type="newpassword"],
        input[type="text"] {
            width: 100%;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem 0.75rem 3rem;
            color: #111827;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s ease;
        }

        input[type="username"]::placeholder,
        input[type="password"]::placeholder,
        input[type="newpassword"]::placeholder,
        input[type="text"]::placeholder {
            color: #9ca3af;
        }

        input[type="username"]:focus,
        input[type="password"]:focus,
        input[type="newpassword"]:focus,
        input[type="text"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: #6b7280;
            transform: translateY(-50%) scale(1.1);
        }

        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Remember & Forgot */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            opacity: 0;
            animation: fadeIn 0.4s ease-out 0.6s forwards;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
            border: 1px solid #d1d5db;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .checkbox-wrapper span {
            color: #6b7280;
            font-size: 0.875rem;
            transition: color 0.2s ease;
        }

        .checkbox-wrapper:hover span {
            color: #111827;
        }

        .forgot-link {
            color: #3b82f6;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .forgot-link:hover {
            color: #2563eb;
            transform: translateX(3px);
        }

        /* Sign In Button */
        .submit-btn {
            width: 100%;
            background: linear-gradient(to right, #3b82f6, #4f46e5);
            color: white;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.4s ease-out 0.7s forwards;
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            opacity: 0;
            animation: fadeIn 0.4s ease-out 0.8s forwards;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: #d1d5db;
        }

        .divider-text {
            color: #6b7280;
            font-size: 0.875rem;
        }

        /* Social Buttons */
        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            opacity: 0;
            animation: fadeIn 0.4s ease-out 0.9s forwards;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 0.75rem;
            color: #374151;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .social-btn:hover {
            background: #f9fafb;
            transform: scale(1.05);
        }

        .social-btn:active {
            transform: scale(0.95);
        }

        .social-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Sign Up Link */
        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            opacity: 0;
            animation: fadeIn 0.4s ease-out 1s forwards;
        }

        .signup-link p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .signup-link a {
            color: #3b82f6;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .signup-link a:hover {
            color: #2563eb;
            transform: translateX(2px);
        }

        /* Animations */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            to {
                transform: scale(1);
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .login-card {
                padding: 1.5rem;
            }

            .welcome-text h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="background-image"></div>
    <div class="overlay"></div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Lock Icon -->
            <div class="lock-icon-wrapper">
                <div class="lock-icon">
                    <img src="assets/images/camaroo-logo-2.png" alt="">
                </div>
            </div>

            <!-- Welcome Text -->
            <div class="welcome-text">
                <p>Change Your Account Password</p>
            </div>

            <!-- Form -->
            <form id="loginForm" method="post">
                <!-- Username Input -->
                <div class="form-group username">
                    <label for="username">User Name</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" 
                                 width="24" 
                                 height="24" 
                                 viewBox="0 0 24 24" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 stroke-width="2" 
                                 stroke-linecap="round" 
                                 stroke-linejoin="round">
                                
                                <!-- Head -->
                                <circle cx="12" cy="8" r="4"></circle>

                                <!-- Body -->
                                <path d="M4 20c1.5-4 5-6 8-6s6.5 2 8 6"></path>
                            </svg>
                        </div>
                        <input type="username" name="username" placeholder="Enter username" required value="<?php echo $UserName; ?>" >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group password">
                    <label for="password">Current Password</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <input type="password" name="userpassword" id="password" placeholder="Enter password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                <line x1="2" x2="22" y1="2" y2="22"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group password">
                    <label for="password">New Password</label>
                    <div class="input-wrapper">
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <input type="password" name="newpassword" placeholder="Enter password" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                <line x1="2" x2="22" y1="2" y2="22"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
<!--                 <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div> -->

                <!-- Submit Button -->
                <button type="submit" name="submit" class="submit-btn">Change Password</button>

                <!-- Divider -->
<!--                 <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">Or continue with</span>
                    <div class="divider-line"></div>
                </div> -->

                <!-- Social Buttons -->
<!--                 <div class="social-buttons">
                    <button type="button" class="social-btn" id="googleBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <circle cx="12" cy="12" r="4"/>
                            <line x1="21.17" x2="12" y1="8" y2="8"/>
                            <line x1="3.95" x2="8.54" y1="6.06" y2="14"/>
                            <line x1="10.88" x2="15.46" y1="21.94" y2="14"/>
                        </svg>
                        <span>Google</span>
                    </button>
                    <button type="button" class="social-btn" id="githubBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/>
                            <path d="M9 18c-4.51 2-5-2-7-2"/>
                        </svg>
                        <span>GitHub</span>
                    </button>
                </div> -->

                <!-- Sign Up Link -->
<!--                 <div class="signup-link">
                    <p>Don't have an account? <a href="#">Sign up</a></p>
                </div> -->
            </form>
        </div>
    </div>

    <script>
document.querySelectorAll('.password-toggle').forEach(function(button) {

    button.addEventListener('click', function () {

        const wrapper = this.closest('.input-wrapper');

        const passwordInput = wrapper.querySelector('input');
        const eyeOffIcon = wrapper.querySelector('.eyeOffIcon');

        const type =
            passwordInput.getAttribute('type') === 'password'
                ? 'text'
                : 'password';

        passwordInput.setAttribute('type', type);

    });

});




        // Form Submit
        {/*
        const loginForm = document.getElementById('loginForm');
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;
            
            console.log('Login attempt:', { email, password, remember });
            
            // Add your login logic here
            alert('Login functionality would be implemented here!\n\nEmail: ' + email);
        });

        // Social Login Buttons
        document.getElementById('googleBtn').addEventListener('click', function() {
            console.log('Google login clicked');
            alert('Google login would be implemented here!');
        });

        document.getElementById('githubBtn').addEventListener('click', function() {
            console.log('GitHub login clicked');
            alert('GitHub login would be implemented here!');
        }); */}
    </script>
</body>
</html>
