<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="bg-light">
    <div class="container d-flex align-items-center min-vh-100">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4" id="register-section" style="display: none;">
                        <form id="register-form">
                            <h3 class="card-title text-center mb-4">สร้างบัญชีใหม่</h3>
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                                <input type="text" class="form-control" name="username" required
                                    autocomplete="username">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">บทบาท</label>
                                <select name="role" class="form-select" aria-label="Default select example">
                                    <option value="">--เลือกบทบาท--</option>
                                    <option value="General_user">ผู้ใช้งานทั่วไป</option>
                                    <option value="Business_owner">เจ้าของกิจการ</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" name="password" required
                                    autocomplete="password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">ยืนยันรหัสผ่าน</label>
                                <input type="password" class="form-control" name="confirm-password" required
                                    autocomplete="confirm-password">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"
                                    name="register_submit">สมัครสมาชิก</button>
                            </div>
                        </form>
                        <hr>
                        <p class="text-center">มีบัญชีอยู่แล้ว? <a href="#" id="login-link">เข้าสู่ระบบที่นี่</a></p>
                    </div>
                    <div class="card-body p-4" id="login-section">
                        <form id="login-form">
                            <h3 class="card-title text-center mb-4">เข้าสู่ระบบบัญชี</h3>
                            <div class="mb-3">
                                <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                                <input type="text" class="form-control" name="username" required
                                    autocomplete="current-username">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">รหัสผ่าน</label>
                                <input type="password" class="form-control" name="password" required
                                    autocomplete="current-password">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" name="login_submit">เข้าสู่ระบบ</button>
                            </div>
                        </form>
                        <hr>
                        <p class="text-center">สร้างบัญชีใหม่? <a href="#" id="register-link">สมัครสมาชิกที่นี่</a></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script>
        $(document).ready(function() {
            $('#login-link').on('click', function(e) {
                e.preventDefault();
                $('#login-section').show();
                $('#register-section').hide();
            });
            $('#register-link').on('click', function(e) {
                e.preventDefault();
                $('#login-section').hide();
                $('#register-section').show();
            });
            $('#register-form').on('submit', function(e) {
                e.preventDefault();
                let formData = $(this).serialize();
                formData += '&action=register';
                console.log(formData);
                $.ajax({
                    type: 'POST',
                    url: '../controls/check_register.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success === true) {
                            alert('สมัครสมาชิกสำเร็จ !' + response.message);
                            $('#login-form').show();
                            $('#register-form').hide();
                            window.location.href = 'main-menu.php';
                        } else {
                            alert('สมัครสมาชิกล้มเหลว: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        alert("เกิดข้อผิดพลาด: ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้");
                    }
                });
            });
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: '../controls/check_register.php' + '&action=login',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('เข้าสู่ระบบสำเร็จ');
                            window.location.href = 'main-menu.php';
                        } else {
                            alert('เข้าสู่ระบบล้มเหลว');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        alert("เกิดข้อผิดพลาด: ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้");
                    }
                });
            });
        });
    </script>
</body>

</html>