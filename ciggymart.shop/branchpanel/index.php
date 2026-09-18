<?php 
require('../config.php');
include(BRANCH_PATH_ADMIN_INCLUDE.'/head.php');
?>
<style>
    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0f172a 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    .modern-login-wrapper {
        width: 100%;
        max-width: 440px;
        padding: 20px;
    }
    .modern-login-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        padding: 40px 35px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .login-brand {
        text-align: center;
        margin-bottom: 30px;
    }
    .login-brand-icon {
        width: 60px;
        height: 60px;
        line-height: 60px;
        background: #2563eb;
        color: #ffffff;
        border-radius: 14px;
        font-size: 26px;
        display: inline-block;
        margin-bottom: 12px;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
    }
    .login-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .login-subtitle {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }
    .form-control-modern {
        height: 48px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 10px 16px;
        font-size: 14.5px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .form-control-modern:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        outline: none;
    }
    .btn-login {
        height: 48px;
        background: #2563eb;
        border: none;
        border-radius: 8px;
        color: #ffffff;
        font-size: 15px;
        font-weight: 700;
        width: 100%;
        transition: all 0.2s;
        margin-top: 10px;
    }
    .btn-login:hover {
        background: #1d4ed8;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
    }
    .login-footer-links {
        text-align: center;
        margin-top: 25px;
        font-size: 13px;
        color: #94a3b8;
    }
</style>

<div class="modern-login-wrapper">
    <div class="modern-login-card">
        <div class="login-brand">
            <div class="login-brand-icon"><i class="fa fa-truck"></i></div>
            <h2 class="login-title">Branch Operations Portal</h2>
            <div class="login-subtitle">Consignment Booking & Billing Access</div>
        </div>

        <form id="branchLoginForm">
            <div class="form-group" style="margin-bottom:18px;">
                <label style="font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Branch Registered Email</label>
                <div class="input-group">
                    <span class="input-group-addon" style="background:#f8fafc; border-color:#cbd5e1;"><i class="fa fa-envelope text-muted"></i></span>
                    <input type="email" id="buser" name="buser" class="form-control form-control-modern" placeholder="e.g. raipur@keshriexpress.com" required autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:22px;">
                <label style="font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">Branch Password</label>
                <div class="input-group">
                    <span class="input-group-addon" style="background:#f8fafc; border-color:#cbd5e1;"><i class="fa fa-lock text-muted"></i></span>
                    <input type="password" id="password" name="password" class="form-control form-control-modern" placeholder="Enter branch password" required>
                </div>
            </div>

            <button type="button" id="loginBtn" class="btn btn-login">
                <i class="fa fa-sign-in"></i> Sign In to Branch Panel
            </button>

            <div class="alert alert-danger" id="loginMsg" style="display:none; margin-top:18px; border-radius:8px; font-size:13px; padding:10px 14px;"></div>
        </form>

        <div class="login-footer-links">
            <a href="<?php echo SITE_URL; ?>/" style="color:#2563eb; font-weight:600;"><i class="fa fa-arrow-left"></i> Back to Main Portal</a> &bull;
            <a href="<?php echo BRANCH_PATH_ADMIN_LINK; ?>/../adminpanel/" style="color:#64748b;">Admin Console</a>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $("#loginMsg").hide();

    $(document).on("keydown", function(e) {
        if (e.keyCode === 13) {
            $("#loginBtn").click();
        }
    });

    $("#loginBtn").click(function(){
        var btn = $(this);
        $("#loginMsg").hide().text('');
        var email = $("#buser").val().trim();
        var password = $("#password").val().trim();

        if (!email) {
            $("#loginMsg").html('<i class="fa fa-exclamation-circle"></i> Please enter your registered branch email').slideDown();
            return;
        }
        if (!password) {
            $("#loginMsg").html('<i class="fa fa-exclamation-circle"></i> Please enter your branch password').slideDown();
            return;
        }

        btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Authenticating...');

        $.ajax({
            url: 'check_branch_login.php',
            type: 'POST',
            data: { email: email, password: password },
            success: function(data) {
                if (data.trim() === "true") {
                    btn.html('<i class="fa fa-check"></i> Success! Redirecting...');
                    window.location.href = "home.php";
                } else {
                    btn.prop("disabled", false).html('<i class="fa fa-sign-in"></i> Sign In to Branch Panel');
                    $("#loginMsg").html('<i class="fa fa-times-circle"></i> Invalid Branch Email or Password.').slideDown();
                }
            },
            error: function() {
                btn.prop("disabled", false).html('<i class="fa fa-sign-in"></i> Sign In to Branch Panel');
                $("#loginMsg").html('<i class="fa fa-exclamation-triangle"></i> Network error occurred. Please try again.').slideDown();
            }
        });
    });
});
</script>
</body>
</html>
