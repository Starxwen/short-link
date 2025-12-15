<?php
session_start();
include './config.php';
include './includes/Settings.php';

// 检查用户是否已登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 如果是管理员，重定向到管理面板
if (isset($_SESSION['user_group']) && $_SESSION['user_group'] === 'admin') {
    header('Location: admin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 获取用户邮箱验证状态
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
$user_email_verified = true;
$user_email = '';

if ($conn) {
    mysqli_query($conn, "set names utf8");
    $user_sql = "SELECT email, email_verified FROM users WHERE uid = $user_id";
    $user_result = mysqli_query($conn, $user_sql);
    
    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user_data = mysqli_fetch_assoc($user_result);
        $user_email = $user_data['email'];
        $user_email_verified = isset($user_data['email_verified']) ? $user_data['email_verified'] : 1;
    }
    mysqli_close($conn);
}

// 获取系统设置
$site_name = Settings::getSiteName();
$site_url = Settings::getSiteUrl();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户面板 - <?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/layui/2.5.6/layui.js"></script>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #9b59b6;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --text-color: #333;
            --text-light: #777;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Microsoft YaHei', sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .user-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .user-header h1 {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .user-container {
            padding: 25px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title i {
            color: var(--primary-color);
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 15px;
            font-weight: 600;
            border-bottom: 1px solid #e0e0e0;
            color: var(--dark-color);
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .data-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .url-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .short-url {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: var(--transition);
        }
        
        .action-btn.delete {
            color: #e74c3c;
        }
        
        .action-btn.delete:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: var(--transition);
        }
        
        .page-btn:hover {
            background: #f0f0f0;
        }
        
        .page-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .create-btn {
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .create-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        }
        
        @media (max-width: 768px) {
            .user-container {
                padding: 15px;
            }
            
            .user-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .data-table {
                font-size: 14px;
            }
            
            .data-table th, .data-table td {
                padding: 10px 8px;
            }
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(255, 193, 7, 0.2);
        }
        
        .alert-warning i {
            color: #856404;
            font-size: 24px;
            margin-top: 2px;
        }
        
        .alert-warning div {
            flex: 1;
        }
        
        .alert-warning strong {
            color: #856404;
            font-size: 16px;
            display: block;
            margin-bottom: 8px;
        }
        
        .alert-warning p {
            color: #856404;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .btn-resend-email {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #212529;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-resend-email:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(255, 193, 7, 0.4);
        }
        
        @media (max-width: 768px) {
            .alert-warning {
                flex-direction: column;
                text-align: center;
            }
            
            .alert-warning i {
                align-self: center;
            }
        }
    </style>
</head>

<body>
    <div class="user-header">
        <h1><i class="fas fa-link"></i> <?php echo htmlspecialchars($site_name); ?>用户面板</h1>
        <div class="user-info">
            <span>欢迎，<?php echo htmlspecialchars($username); ?></span>
            <button class="logout-btn" onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> 退出登录</button>
        </div>
    </div>
    
    <div class="user-container">
        <?php if (!$user_email_verified && !empty($user_email)): ?>
        <div class="alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>邮箱未验证</strong>
                <p>您的邮箱地址尚未验证，验证后可以享受更多功能。请检查您的邮箱并点击验证链接，或重新发送验证邮件。</p>
                <button class="btn-resend-email" onclick="resendVerificationEmail()">重新发送验证邮件</button>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="page-title">
            <i class="fas fa-tachometer-alt"></i> 我的短链接
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> 我的链接列表</h2>
                <button class="create-btn" onclick="location.href='new.php'">
                    <i class="fas fa-plus"></i> 创建新链接
                </button>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table" id="data-table">
                        <thead>
                            <tr>
                                <th width="5%">编号</th>
                                <th width="40%">URL地址</th>
                                <th width="20%">短链接</th>
                                <th width="15%">添加日期</th>
                                <th width="10%">点击次数</th>
                                <th width="10%">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 数据行将通过JavaScript动态插入 -->
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination" id="pagination">
                    <!-- 分页按钮将通过JavaScript动态生成 -->
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var perPage = 10; // 每页显示的数据条数

            function loadData(page) {
                $.ajax({
                    type: "POST",
                    url: "ajax/get_user_data.php",
                    data: { page: page, perPage: perPage, user_id: <?php echo $user_id; ?> },
                    dataType: 'json',
                    success: function (response) {
                        var tbody = $('#data-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        // 检查响应是否包含错误
                        if (response.error) {
                            tbody.append('<tr><td colspan="6" style="text-align: center;">' + response.error + '</td></tr>');
                            $('#pagination').empty();
                            return;
                        }

                        // 检查是否有数据
                        if (!response.rows || response.rows.length === 0) {
                            // 显示空状态
                            tbody.append('<tr><td colspan="6" class="empty-state"><i class="fas fa-inbox"></i><p>您还没有创建任何短链接</p><p><a href="new.php" style="color: var(--primary-color);">立即创建第一个短链接</a></p></td></tr>');
                            $('#pagination').empty();
                            return;
                        }

                        response.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.num));
                            tr.append($('<td class="url-cell"></td>').text(decodeURIComponent(escape(window.atob(row.url)))));
                            tr.append($('<td></td>').text('<?php echo $site_url; ?>' + row.short_url));
                            tr.append($('<td></td>').text(row.add_date));
                            tr.append($('<td></td>').text(row.click_count || 0));
                            var deleteBtn = $('<button class="action-btn delete" title="删除"><i class="fas fa-trash"></i></button>').data('num', row.num).click(function () {
                                deleteData($(this).data('num'));
                            });
                            tr.append($('<td></td>').append(deleteBtn));
                            tbody.append(tr);
                        });

                        // 更新分页按钮
                        var pagination = $('#pagination');
                        pagination.empty();
                        for (var i = 1; i <= data.totalPages; i++) {
                            var btn = $('<button class="page-btn"></button>').text(i).click(function () {
                                loadData($(this).text());
                            });
                            if (i === page) {
                                btn.addClass('active');
                            }
                            pagination.append(btn);
                        }
                    },
                    error: function (error) {
                        alert("加载数据失败: " + error);
                    }
                });
            }

            function deleteData(num) {
                if (confirm("确定要删除这条记录吗？")) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/delete_user_data.php",
                        data: { num: num, user_id: <?php echo $user_id; ?> },
                        success: function (response) {
                            if (response === 'success') {
                                loadData(currentPage);
                            } else {
                                alert("删除失败: " + response);
                            }
                        },
                        error: function (error) {
                            alert("删除数据失败: " + error);
                        }
                    });
                }
            }

            loadData(currentPage); // 加载第一页数据
        });
        
        // 重新发送验证邮件
        function resendVerificationEmail() {
            $.ajax({
                type: 'POST',
                url: 'ajax/resend_verification_email.php',
                data: { user_id: <?php echo $user_id; ?> },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                    } else {
                        alert(response.error || '发送失败，请稍后重试');
                        // 在控制台输出调试信息
                        if (response.debug) {
                            console.log('邮件设置调试信息:', response.debug);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert('发送失败，请稍后重试');
                    console.log('AJAX错误:', status, error);
                }
            });
        }
    </script>
</body>
</html>