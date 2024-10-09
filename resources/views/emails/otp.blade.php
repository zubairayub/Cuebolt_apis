<!-- resources/views/emails/otp.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
</head>
<body>
    <h1>Hello,</h1>
    <p>Your OTP code is: <strong>{{ $otp }}</strong></p>
    <p>Please use this code to verify your account. The code is valid for 10 minutes.</p>
    <p>Thank you for using our service!</p>
</body>
</html>
