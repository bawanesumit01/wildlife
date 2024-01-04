<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        p {
            padding: 2px 0px !important;
            margin: 2px 0px !important;
        }
    </style>
</head>

<body>
    <div>
        <p>Hi {{ $user->name }},</p><br>
        <p>We've received a request to reset your password for your [Company Name] account.</p><br>
        <p>To proceed, please enter the following one-time password (OTP) in the password reset form:</p><br>
        <p>OTP: {{ $otp }}</p><br>
        <p>This code will expire in [expiration time]. If you don't enter it within that time, you'll need to request a new one.</p><br>
        <p>Didn't request a password reset? If you didn't make this request, please let us know immediately to ensure your account's security..</p><br>
        <p>Stay safe online,</p><br>
        <p>The [Company Name] Team</p><br>
    </div>
</body>

</html>
