<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="javascript:void(0)" data-toggle="sidebar" class="nav-link nav-link-lg"><em class="fas fa-bars"></em></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="<?= base_url(); ?>" data-toggle="dropdown" class="nav-link dropdown-toggle  nav-link-lg nav-link-user">
                <span class="user_profile_icon"><i class="fa fa-user-circle" aria-hidden="true"></i> </span>
                <div class="d-sm-none d-lg-inline-block">Hi, <?= ucwords($this->session->userdata('authName')); ?></div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <?php if ($this->session->userdata('authStatus')) { ?>
                    <a href="<?php echo base_url(); ?>profile" class="dropdown-item has-icon">
                        <em class="fas fa-user"></em> Profile
                    </a>
                <?php } ?>
                <a href="<?php echo base_url(); ?>resetpassword" class="dropdown-item has-icon">
                    <em class="fas fa-key"></em> Reset Password
                </a>
                <a href="<?php echo base_url(); ?>logout" class="dropdown-item has-icon">
                    <em class="fas fa-sign-out-alt"></em> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
<div class="main-sidebar">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?= base_url(); ?>dashboard">
                <?php if (!empty($full_logo)) { ?>
                    <img src="<?= base_url() . LOGO_IMG_PATH . $full_logo['message']; ?>" alt="logo" width="150">
                <?php } ?>
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= base_url(); ?>dashboard">
                <?php if (!empty($half_logo)) { ?>
                    <img src="<?= base_url() . LOGO_IMG_PATH . $half_logo['message']; ?>" alt="logo" width="50">
                <?php } ?>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a class="nav-link" href="<?= base_url(); ?>dashboard"><em class="fas fa-home"></em> <span>Dashboard</span></a>
            </li>
            <?php if (has_permissions('read', 'categories') || has_permissions('read', 'subcategories') || has_permissions('read', 'category_order')) { ?>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link has-dropdown"><em class="fas fa-gift"></em><span>Categories</span></a>
                    <ul class="dropdown-menu">
                        <?php if (has_permissions('read', 'categories')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>main-category">Main Category</a></li>
                        <?php } ?>
                        <?php if (has_permissions('read', 'subcategories')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>sub-category">Sub Category</a></li>
                        <?php } ?>
                        <?php if (has_permissions('read', 'category_order')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>category-order">Category Order</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (has_permissions('create', 'questions') || has_permissions('read', 'questions')) { ?>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link has-dropdown"><em class="fas fa-trophy"></em><span>Questions</span></a>
                    <ul class="dropdown-menu">
                        <?php if (has_permissions('read', 'questions')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>create-questions">Create Questions</a></li>
                        <?php } ?>
                        <?php if (has_permissions('read', 'questions')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>manage-questions">View Questions</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'daily_quiz')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>daily-quiz"><em class="fas fa-question"></em> <span>Daily Quiz</span></a>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'manage_contest') || has_permissions('read', 'manage_contest_question') || has_permissions('read', 'import_contest_question')) { ?>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link has-dropdown"><em class="fas fa-gift"></em> <span>Contests</span></a>
                    <ul class="dropdown-menu">
                        <?php if (has_permissions('read', 'manage_contest')) { ?>
                            <li><a href="<?= base_url(); ?>contest"> Manage Contest</a></li>
                        <?php } ?>
                        <?php if (has_permissions('read', 'manage_contest_question')) { ?>
                            <li><a href="<?= base_url(); ?>contest-questions"> Manage Questions</a></li>
                        <?php } ?>
                        <?php if (has_permissions('update', 'import_contest_question')) { ?>
                            <li><a href="<?= base_url(); ?>contest-questions-import"> Import Questions</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'fun_n_learn')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url() ?>fun-n-learn"><em class="fas fa-book-open"></em> <span>Fun 'N' Learn</span></a>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'guess_the_word')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url() ?>guess-the-word"><em class="fas fa-atom"></em> <span>Guess The Word</span></a>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'users')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url() ?>users"><em class="fas fa-users"></em> <span>Users</span></a>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('authStatus')) { ?>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link has-dropdown"><em class="fas fa-th"></em><span>Leaderboard</span></a>
                    <ul class="dropdown-menu">
                        <li><a class="nav-link" href="<?= base_url(); ?>global-leaderboard">All</a></li>
                        <li><a class="nav-link" href="<?= base_url(); ?>monthly-leaderboard">Monthly</a></li>
                        <li><a class="nav-link" href="<?= base_url(); ?>daily-leaderboard">Daily</a></li>
                    </ul>
                </li>
            <?php } ?>
            <?php if (is_language_mode_enabled()) { ?>
                <?php if (has_permissions('read', 'languages')) { ?>
                    <li>
                        <a class="nav-link" href="<?= base_url() ?>languages"><em class="fas fa-language"></em> <span>Languages</span></a>
                    </li>
                <?php } ?>
            <?php } ?>
            <?php if (has_permissions('read', 'system_configuration')) { ?>
                <li class="nav-item dropdown">
                    <a href="javascript:void(0)" class="nav-link has-dropdown"><em class="fas fa-cog"></em><span>Settings</span></a>
                    <ul class="dropdown-menu">
                        <?php if (has_permissions('read', 'system_configuration')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>system-configurations">System Configurations</a></li>
                        <?php } ?>
                        <?php if ($this->session->userdata('authStatus')) { ?>
                            <li><a class="nav-link" href="<?= base_url(); ?>notification-settings">Notification Settings</a></li>
                            <li><a class="nav-link" href="<?= base_url(); ?>about-us">About Us</a></li>
                            <li><a class="nav-link" href="<?= base_url(); ?>contact-us">Contact Us</a></li>
                            <li><a class="nav-link" href="<?= base_url(); ?>instructions">How to Play</a></li>
                            <li><a class="nav-link" href="<?= base_url(); ?>privacy-policy">Privacy Policy</a></li>
                            <li><a class="nav-link" href="<?= base_url(); ?>terms-conditions">Terms Conditions</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>

            <?php if (has_permissions('read', 'question_report')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>question-reports"><em class="far fa-question-circle"></em> <span>Question Reports</span></a>
                </li>
            <?php } ?>
            <?php if (has_permissions('read', 'send_notification')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>send-notifications"><em class="fas fa-bullhorn"></em> <span>Send Notifications</span></a>
                </li>
            <?php } ?>
            <?php if (has_permissions('update', 'import_question')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>import-questions"><em class="fas fa-upload"></em> <span>Import Questions</span></a>
                </li>
            <?php } ?>
            <?php if ($this->session->userdata('authStatus')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>user-accounts-rights"><em class="fas fa-user"></em> <span>User Accounts and Rights</span></a>
                </li>
            <?php } ?>

            <?php if ($this->session->userdata('authStatus')) { ?>
                <li>
                    <a class="nav-link" href="<?= base_url(); ?>system-updates"><em class="fas fa-cloud-download-alt"></em> <span>System Update</span></a>
                </li>
            <?php } ?>
        </ul>
    </aside>
</div>