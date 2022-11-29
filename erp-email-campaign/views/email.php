<!DOCTYPE html>
<html dir="<?php echo is_rtl() ? 'rtl' : 'ltr'?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo get_bloginfo( 'name', 'display' ); ?></title>

    <?php if ( !empty( $primary_styles ) ): ?>
        <link rel="stylesheet" href="<?php echo $primary_styles; ?>">
    <?php endif; ?>

    <style type="text/css"><?php echo $responsive_styles; ?></style>

</head>
<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <div id="email-template-container">
        <?php echo $html; ?>
    </div>

    <?php if ( !empty( $tracker_image ) ): ?>
        <img src="<?php echo $tracker_image; ?>" alt="" title="" width="1" height="1">
    <?php endif; ?>
</body>
</html>
