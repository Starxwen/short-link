<?php
session_start();
include './config.php';

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
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户面板 - 星跃短链接</title>
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
    </style>
</head>

<body>
    <div class="user-header">
        <h1><i class="fas fa-link"></i> 星跃短链接用户面板</h1>
        <div class="user-info">
            <span>欢迎，<?php echo htmlspecialchars($username); ?></span>
            <button class="logout-btn" onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> 退出登录</button>
        </div>
    </div>
    
    <div class="user-container">
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
                    success: function (response) {
                        var data = response;
                        var tbody = $('#data-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        if (data.rows.length === 0) {
                            // 显示空状态
                            tbody.append('<tr><td colspan="6" class="empty-state"><i class="fas fa-inbox"></i><p>您还没有创建任何短链接</p><p><a href="new.php" style="color: var(--primary-color);">立即创建第一个短链接</a></p></td></tr>');
                            $('#pagination').empty();
                            return;
                        }

                        data.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.num));
                            tr.append($('<td class="url-cell"></td>').text(decodeURIComponent(escape(window.atob(row.url)))));
                            tr.append($('<td></td>').text('<?php include './config.php'; echo $my_url; ?>' + row.short_url));
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
    </script>
</body>
</html>