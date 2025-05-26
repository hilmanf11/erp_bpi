<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $config->name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="<?= $config->favicon ?>" type="image/png" sizes="16x16">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login/css/util.css') ?>">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login/css/main.css?' . time()) ?>">
    <!--===============================================================================================-->
</head>

<body style="background-color: #666666;">
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <form class="login100-form validate-form" method="post">
                    <center>
                        <img src="<?= $config->logo ?>" width="250">
                    </center>
                    <br>
                    <?= $message ?>
                    <h3>FORGOT PASSWORD</h3>
                    <p>Please enter your Email to Reset Password</p>
                    <br>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="email" placeholder="Email" name="email" required autofocus>
                    </div>
                    <br>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" style="margin-bottom: 10px;" type="submit">
                            Send Email
                        </button>
                        <a href="<?= base_url('login') ?>">Back Login ?</a>
                    </div>
                </form>
                <div class="login100-more" style="background-image: url('<?= $config->image ?>');"></div>
            </div>
        </div>
    </div>
</body>

</html>