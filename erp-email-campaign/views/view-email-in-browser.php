<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $email_subject . ' - ' . get_bloginfo( 'name', 'display' ); ?></title>
    <link rel="stylesheet" href="<?php echo WPERP_EMAIL_CAMPAIGN_ASSETS . '/css/email-template-styles.css'; ?>">
    <style><?php echo $responsive_styles; ?></style>
</head>
<body>
    <div id="email-template-container">
        <?php echo $html; ?>
    </div>
</body>
</html>
