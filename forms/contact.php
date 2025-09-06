<?php
// contact_process.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Helper: safe trim
function get_post($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Basic anti-bot hidden field (should be empty)
if (!empty($_POST['website'])) {
    // likely bot
    http_response_code(400);
    exit('Bad request');
}

$name    = substr(get_post('name'), 0, 100);
$email   = substr(get_post('email'), 0, 200);
$subject = substr(get_post('subject'), 0, 200);
$message = trim(get_post('message'));

// Server-side validation
$errors = [];

if (empty($name) || strlen($name) < 2) $errors[] = 'Name is required (min 2 chars).';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (empty($message) || strlen($message) < 10) $errors[] = 'Message is required (min 10 chars).';

if ($errors) {
    // Return a friendly error page or JSON depending on your app
    http_response_code(422);
    echo '<h2>There were errors</h2><ul>';
    foreach ($errors as $err) {
        echo '<li>' . htmlspecialchars($err) . '</li>';
    }
    echo '</ul><p><a href="javascript:history.back()">Go back</a></p>';
    exit;
}

// Prevent email header injection in subject/from
function is_header_injection($str){
    return preg_match("/[\r\n]/", $str);
}
if (is_header_injection($name) || is_header_injection($email) || is_header_injection($subject)) {
    http_response_code(400);
    exit('Bad request');
}

// Compose email
$to = 'you@yourdomain.com'; // <-- CHANGE to your destination email
$subjectLine = (!empty($subject) ? $subject : 'Website Contact Form') . ' — ' . $name;
$body = "You have a new contact form message:\n\n";
$body .= "Name: $name\n";
$body .= "Email: $email\n";
if ($subject) $body .= "Subject: $subject\n";
$body .= "Message:\n$message\n\n";
$body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
$body .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";

// Headers
$headers = [];
$headers[] = 'From: ' . 'no-reply@yourdomain.com'; // use a domain email on servers that require it
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$headers_str = implode("\r\n", $headers);

// Send
$sent = mail($to, $subjectLine, $body, $headers_str);

if ($sent) {
    // Replace with redirect or JSON response as desired
    echo '<h2>Message sent — thank you!</h2><p>We will get back to you soon.</p>';
} else {
    http_response_code(500);
    echo '<h2>Sorry, failed to send message.</h2><p>Please try again later.</p>';
}
?>
