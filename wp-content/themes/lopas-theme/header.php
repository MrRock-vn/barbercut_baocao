<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo home_url('/'); ?>">
            Barber Spa
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="navbar-nav ms-auto align-items-center gap-2 d-flex flex-row justify-content-end w-100">
                <a class="btn btn-outline-light btn-sm mx-1" href="<?php echo home_url('/'); ?>">Trang chủ</a>
                <?php if (is_user_logged_in()): ?>
                    <a class="btn btn-outline-light btn-sm mx-1" href="<?php echo home_url('/my-bookings/'); ?>">Lịch hẹn</a>
                    <a class="btn btn-outline-light btn-sm mx-1" href="<?php echo home_url('/dashboard/'); ?>">Tài khoản</a>
                    <?php if (current_user_can('manage_options')): ?>
                    <a class="btn btn-outline-light btn-sm mx-1" href="<?php echo home_url('/owner-dashboard/'); ?>">Quản lý</a>
                    <?php endif; ?>
                    <a class="btn btn-danger btn-sm mx-1" href="<?php echo wp_logout_url(home_url('/')); ?>">Đăng xuất</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm mx-1" href="<?php echo home_url('/login/'); ?>">Đăng nhập</a>
                    <a class="btn btn-danger btn-sm mx-1" href="<?php echo home_url('/register/'); ?>">Đăng ký</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="site-content">
