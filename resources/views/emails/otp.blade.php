<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        p{
            padding: 2px 0px!important;
            margin: 2px 0px!important;
        }
    </style>
</head>

<body>
    <div>
        <p>Hi ,</p><br>
        <p>Thanks for choosing [Company Name]! We're excited to have you on board.</p><br>
        <p>To complete your registration, please enter the following one-time password (OTP) in the registration form:
        </p><br>
        <p>OTP: {{ $otp }}</p><br>
        <p>This code will expire in 10 Minutes. If you don't enter it within that time, you'll need to request a new
            one.</p><br>
        <p>Didn't request this code? No worries, just ignore this email. Your information is safe with us.</p><br>
        <p>We're looking forward to seeing you inside!</p><br>
        <p>Sincerely,</p><br>
        <p>The [Company Name] Team</p><br>
    </div>
</body>

</html>
