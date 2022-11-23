<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!--[if !mso]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <![endif]-->

    <!--[if (mso 16)]>
    <style type="text/css">
        a {
            text-decoration: none;
        }

        span {
            vertical-align: middle;
        }
    </style>
    <![endif]-->

    <style type="text/css">
        #outlook a {
            padding: 0;
        }

        a {
            text-decoration: none;
            word-break: break-word;
        }

        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        img {
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            vertical-align: middle;
            background-color: transparent;
            max-width: 100%;
        }

        p {
            display: block;
            margin: 0;
            line-height: inherit;
            /*font-size: inherit;*/
        }

        div.viwec-responsive {
            display: inline-block;
        }

        small {
            display: block;
            font-size: 13px;
        }

        #viwec-transferred-content small {
            display: inline;
        }

        #viwec-transferred-content td {
            vertical-align: top;
        }

        td.viwec-row {
            background-repeat: no-repeat;
            background-size: cover;
            background-position: top;
        }
    </style>

    <!--[if mso]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->


    <!--[if mso | IE]>
    <style type="text/css">
        .viwec-responsive {
            width: 100% !important;
        }

        small {
            display: block;
            font-size: 13px;
        }

        table {
            font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
        }
    </style>
    <![endif]-->

    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
        @import url(https://fonts.googleapis.com/css?family=Oswald:300,400,500,700);
    </style>

    <style type="text/css">

        @media only screen and (min-width: <?php echo esc_attr($responsive);?>px) {
            a {
                text-decoration: none;
            }

            td {
                overflow: hidden;
            }

            table {
                font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
            }

            div.viwec-responsive {
                display: inline-block;
            }

            .viwec-responsive-min-width {
                min-width: <?php echo esc_attr($width);?>px;
            }
        }

        @media only screen and (max-width: <?php echo esc_attr($responsive);?>px) {
            a {
                text-decoration: none;
            }

            td {
                overflow: hidden;
            }

            table {
                font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
            }

            img {
                padding-bottom: 10px;
            }

            .viwec-responsive, .viwec-responsive table, .viwec-button-responsive {
                width: 100% !important;
                min-width: 100%;
            }

            #viwec-transferred-content img {
                width: 100% !important;
            }

            .viwec-no-full-width-on-mobile {
                min-width: auto !important;
            }

            .viwec-responsive-padding {
                padding: 0 !important;
            }

            .viwec-mobile-hidden {
                display: none !important;
            }

            .viwec-responsive-center, .viwec-responsive-center p {
                text-align: center !important;
            }

            .viwec-mobile-50 {
                width: 50% !important;
            }

            .viwec-center-on-mobile p {
                text-align: center !important;
            }
        }

        <?php echo wp_kses_post( apply_filters('viwec_after_render_style','') )?>
    </style>

</head>

<body vlink="#FFFFFF" <?php echo $direction == 'rtl' ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">

<div id="wrapper" style="box-sizing:border-box;padding:0;margin:0;<?php echo esc_attr( $bg_style ); ?>">
    <table border="0" cellpadding="0" cellspacing="0" height="100%" align="center" width="100%" style="margin: 0;">
        <tbody>
        <tr>
            <td style="padding: 20px 10px;">
                <table border="0" cellpadding="0" cellspacing="0" height="100%" align="center" width="<?php echo esc_attr( $width ) ?>"
                       style="font-size: 15px; margin: 0 auto; padding: 0; border-collapse: collapse;font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;">
                    <tbody>
                    <tr>
                        <td align="center" valign="top" id="body_content" style="<?php echo esc_attr( $bg_style ); ?>background-size:cover;">
                            <div class="viwec-responsive-min-width">
