<?php
session_start();
include './config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 检查是否是管理员或具有管理员权限的用户
if (!isset($_SESSION['user_group']) || ($_SESSION['user_group'] !== 'admin' && $_SESSION['user_id'] !== 0)) {
    header('Location: user_panel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 星跃短链接生成器</title>
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
        
        .admin-header {
            background: linear-gradient(135deg, var(--dark-color) 0%, var(--primary-color) 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .admin-header h1 {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-header .user-info {
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
        
        .admin-container {
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
        
        .action-btn.view {
            color: var(--primary-color);
            margin-right: 5px;
        }
        
        .action-btn.view:hover {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .action-btn.edit {
            color: #f39c12;
            margin-right: 5px;
        }
        
        .action-btn.edit:hover {
            background-color: rgba(243, 156, 18, 0.1);
        }
        
        .text-muted {
            color: var(--text-light);
            font-size: 0.85em;
        }
        
        .user-urls-container {
            padding: 15px;
        }
        
        .layui-badge {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 3px;
        }
        
        .layui-bg-blue {
            background-color: #1E9FFF !important;
        }
        
        .layui-bg-red {
            background-color: #FF5722 !important;
        }
        
        @media (max-width: 768px) {
            .admin-container {
                padding: 15px;
            }
            
            .admin-header {
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
    <div class="admin-header">
        <h1><i class="fas fa-link"></i> 星跃短链接后台管理</h1>
        <div class="user-info">
            <span>欢迎，<?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <button class="logout-btn" onclick="location.href='logout.php'"><i class="fas fa-sign-out-alt"></i> 退出登录</button>
        </div>
    </div>
    
    <div class="admin-container">
        <!-- 标签页导航 -->
        <div class="layui-tab layui-tab-brief" lay-filter="adminTab">
            <ul class="layui-tab-title">
                <li class="layui-this"><i class="fas fa-link"></i> 链接数据管理</li>
                <li><i class="fas fa-users"></i> 用户管理</li>
            </ul>
            <div class="layui-tab-content">
                <!-- 链接数据管理标签页 -->
                <div class="layui-tab-item layui-show">
                    <div class="page-title">
                        <i class="fas fa-tachometer-alt"></i> 链接数据管理
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-table"></i> 链接列表</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="data-table" id="data-table">
                                    <thead>
                                        <tr>
                                            <th width="5%">编号</th>
                                            <th width="35%">URL地址</th>
                                            <th width="18%">短链接</th>
                                            <th width="10%">用户IP</th>
                                            <th width="12%">添加日期</th>
                                            <th width="10%">用户</th>
                                            <th width="5%">操作</th>
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
                
                <!-- 用户管理标签页 -->
                <div class="layui-tab-item">
                    <div class="page-title">
                        <i class="fas fa-users"></i> 用户管理
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2><i class="fas fa-table"></i> 用户列表</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-container">
                                <table class="data-table" id="users-table">
                                    <thead>
                                        <tr>
                                            <th width="10%">用户ID</th>
                                            <th width="20%">用户名</th>
                                            <th width="25%">邮箱</th>
                                            <th width="15%">用户组</th>
                                            <th width="15%">链接数量</th>
                                            <th width="15%">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- 数据行将通过JavaScript动态插入 -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="pagination" id="users-pagination">
                                <!-- 分页按钮将通过JavaScript动态生成 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var perPage = 12; // 每页显示的数据条数
            var usersCurrentPage = 1;
            var usersPerPage = 10; // 用户列表每页显示的数据条数

            // 初始化LayUI标签页
            layui.use('element', function(){
                var element = layui.element;
                
                // 监听标签切换事件
                element.on('tab(adminTab)', function(data){
                    if(data.index === 0) {
                        // 链接数据管理标签页
                        loadData(currentPage);
                    } else if(data.index === 1) {
                        // 用户管理标签页
                        loadUsers(usersCurrentPage);
                    }
                });
            });

            // 加载链接数据
            function loadData(page) {
                $.ajax({
                    type: "POST",
                    url: "ajax/get_data.php",
                    data: { page: page, perPage: perPage },
                    success: function (response) {
                        var data = response;
                        var tbody = $('#data-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        data.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.num));
                            tr.append($('<td class="url-cell"></td>').text(decodeURIComponent(escape(window.atob(row.url)))));
                            tr.append($('<td></td>').text('<?php include './config.php'; echo $my_url; ?>' + row.short_url));
                            tr.append($('<td></td>').text(row.ip));
                            tr.append($('<td></td>').text(row.add_date));
                            
                            // 显示用户ID和用户名
                            var userCell = $('<td></td>');
                            if (row.uid == 0) {
                                userCell.text('游客');
                            } else {
                                userCell.html('ID: ' + row.uid + '<br><small class="text-muted">加载中...</small>');
                                // 异步获取用户名
                                $.ajax({
                                    type: "POST",
                                    url: "ajax/get_username.php",
                                    data: { uid: row.uid },
                                    success: function (response) {
                                        if (response.username) {
                                            userCell.html('ID: ' + row.uid + '<br><small class="text-muted">' + response.username + '</small>');
                                        } else {
                                            userCell.html('ID: ' + row.uid + '<br><small class="text-muted">未知用户</small>');
                                        }
                                    }
                                });
                            }
                            tr.append(userCell);
                            
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
                                currentPage = parseInt($(this).text());
                                loadData(currentPage);
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

            // 删除链接数据
            function deleteData(num) {
                if (confirm("确定要删除这条记录吗？")) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/delete_data.php",
                        data: { num: num },
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

            // 加载用户列表
            function loadUsers(page) {
                $.ajax({
                    type: "POST",
                    url: "ajax/get_users.php",
                    data: { page: page, perPage: usersPerPage },
                    success: function (response) {
                        var data = response;
                        var tbody = $('#users-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        data.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.uid));
                            tr.append($('<td></td>').text(row.username));
                            tr.append($('<td></td>').text(row.email));
                            
                            // 用户组显示
                            var groupCell = $('<td></td>');
                            var groupBadge = $('<span class="layui-badge"></span>');
                            if (row.ugroup === 'admin') {
                                groupBadge.addClass('layui-bg-red').text('管理员');
                            } else {
                                groupBadge.addClass('layui-bg-blue').text('普通用户');
                            }
                            groupCell.append(groupBadge);
                            tr.append(groupCell);
                            
                            tr.append($('<td></td>').text(row.url_count));
                            
                            // 操作按钮
                            var actionCell = $('<td></td>');
                            var viewBtn = $('<button class="action-btn view" title="查看用户链接"><i class="fas fa-eye"></i></button>').data('uid', row.uid).data('username', row.username).click(function () {
                                viewUserUrls($(this).data('uid'), $(this).data('username'));
                            });
                            actionCell.append(viewBtn);
                            
                            // 防止编辑管理员账户和自己
                            if (row.uid !== 0 && row.uid !== <?php echo $_SESSION['user_id']; ?>) {
                                var editBtn = $('<button class="action-btn edit" title="编辑用户"><i class="fas fa-edit"></i></button>').data('uid', row.uid).data('username', row.username).data('email', row.email).data('ugroup', row.ugroup).click(function () {
                                    editUser($(this).data('uid'), $(this).data('username'), $(this).data('email'), $(this).data('ugroup'));
                                });
                                actionCell.append(editBtn);
                            }
                            
                            // 防止删除管理员账户和自己
                            if (row.uid !== 0 && row.uid !== <?php echo $_SESSION['user_id']; ?>) {
                                var deleteBtn = $('<button class="action-btn delete" title="删除用户"><i class="fas fa-trash"></i></button>').data('uid', row.uid).data('username', row.username).click(function () {
                                    deleteUser($(this).data('uid'), $(this).data('username'));
                                });
                                actionCell.append(deleteBtn);
                            }
                            
                            tr.append(actionCell);
                            tbody.append(tr);
                        });

                        // 更新分页按钮
                        var pagination = $('#users-pagination');
                        pagination.empty();
                        for (var i = 1; i <= data.totalPages; i++) {
                            var btn = $('<button class="page-btn"></button>').text(i).click(function () {
                                usersCurrentPage = parseInt($(this).text());
                                loadUsers(usersCurrentPage);
                            });
                            if (i === page) {
                                btn.addClass('active');
                            }
                            pagination.append(btn);
                        }
                    },
                    error: function (error) {
                        alert("加载用户数据失败: " + error);
                    }
                });
            }

            // 查看用户链接
            function viewUserUrls(uid, username) {
                // 创建模态框显示用户链接
                layui.use('layer', function(){
                    var layer = layui.layer;
                    
                    layer.open({
                        type: 1,
                        title: '用户 ' + username + ' 的链接列表',
                        area: ['90%', '80%'],
                        content: '<div class="user-urls-container">' +
                                '<div class="table-container">' +
                                '<table class="data-table" id="user-urls-table">' +
                                '<thead>' +
                                '<tr>' +
                                '<th width="5%">编号</th>' +
                                '<th width="40%">URL地址</th>' +
                                '<th width="20%">短链接</th>' +
                                '<th width="10%">用户IP</th>' +
                                '<th width="15%">添加日期</th>' +
                                '<th width="5%">操作</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>' +
                                '<tr><td colspan="6" style="text-align: center;">加载中...</td></tr>' +
                                '</tbody>' +
                                '</table>' +
                                '</div>' +
                                '<div class="pagination" id="user-urls-pagination"></div>' +
                                '</div>',
                        success: function(layero, index){
                            // 设置当前查看的用户ID
                            currentUserId = uid;
                            // 加载用户链接数据
                            loadUserUrls(uid, 1);
                        }
                    });
                });
            }

            // 加载用户链接数据
            function loadUserUrls(uid, page) {
                $.ajax({
                    type: "POST",
                    url: "ajax/get_user_urls.php",
                    data: { user_id: uid, page: page, perPage: 10 },
                    success: function (response) {
                        var data = response;
                        var tbody = $('#user-urls-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        if (data.error) {
                            tbody.append('<tr><td colspan="6" style="text-align: center;">' + data.error + '</td></tr>');
                            return;
                        }

                        data.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.num));
                            tr.append($('<td class="url-cell"></td>').text(decodeURIComponent(escape(window.atob(row.url)))));
                            tr.append($('<td></td>').text('<?php include './config.php'; echo $my_url; ?>' + row.short_url));
                            tr.append($('<td></td>').text(row.ip));
                            tr.append($('<td></td>').text(row.add_date));
                            
                            var deleteBtn = $('<button class="action-btn delete" title="删除"><i class="fas fa-trash"></i></button>').data('num', row.num).click(function () {
                                deleteUserUrl($(this).data('num'));
                            });
                            tr.append($('<td></td>').append(deleteBtn));
                            tbody.append(tr);
                        });

                        // 更新分页按钮
                        var pagination = $('#user-urls-pagination');
                        pagination.empty();
                        for (var i = 1; i <= data.totalPages; i++) {
                            var btn = $('<button class="page-btn"></button>').text(i).click(function () {
                                loadUserUrls(uid, $(this).text());
                            });
                            if (i === page) {
                                btn.addClass('active');
                            }
                            pagination.append(btn);
                        }
                    },
                    error: function (error) {
                        $('#user-urls-table tbody').html('<tr><td colspan="6" style="text-align: center;">加载失败: ' + error + '</td></tr>');
                    }
                });
            }

            // 删除用户链接
            function deleteUserUrl(num) {
                if (confirm("确定要删除这条记录吗？")) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/delete_data.php",
                        data: { num: num },
                        success: function (response) {
                            if (response === 'success') {
                                // 重新加载当前页面的用户链接
                                var activePage = $('#user-urls-pagination .page-btn.active').text();
                                loadUserUrls(currentUserId, activePage);
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

            // 删除用户
            function deleteUser(uid, username) {
                if (confirm("确定要删除用户 " + username + " 吗？\n\n注意：这将同时删除该用户的所有短链接数据！")) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/delete_user.php",
                        data: { uid: uid },
                        success: function (response) {
                            if (response === 'success') {
                                loadUsers(usersCurrentPage);
                            } else {
                                alert("删除失败: " + response);
                            }
                        },
                        error: function (error) {
                            alert("删除用户失败: " + error);
                        }
                    });
                }
            }

            // 编辑用户信息
            function editUser(uid, username, email, ugroup) {
                layui.use('layer', function(){
                    var layer = layui.layer;
                    
                    layer.open({
                        type: 1,
                        title: '编辑用户信息',
                        area: ['500px', '400px'],
                        content: '<div style="padding: 20px;">' +
                                '<form id="edit-user-form" lay-filter="editUserForm">' +
                                '<div class="layui-form-item">' +
                                '<label class="layui-form-label">用户ID</label>' +
                                '<div class="layui-input-block">' +
                                '<input type="text" name="uid" value="' + uid + '" readonly class="layui-input">' +
                                '</div>' +
                                '</div>' +
                                '<div class="layui-form-item">' +
                                '<label class="layui-form-label">用户名</label>' +
                                '<div class="layui-input-block">' +
                                '<input type="text" name="username" value="' + username + '" required lay-verify="required" class="layui-input">' +
                                '</div>' +
                                '</div>' +
                                '<div class="layui-form-item">' +
                                '<label class="layui-form-label">邮箱</label>' +
                                '<div class="layui-input-block">' +
                                '<input type="email" name="email" value="' + email + '" required lay-verify="required|email" class="layui-input">' +
                                '</div>' +
                                '</div>' +
                                '<div class="layui-form-item">' +
                                '<label class="layui-form-label">用户组</label>' +
                                '<div class="layui-input-block">' +
                                '<select name="ugroup" lay-verify="required">' +
                                '<option value="user" ' + (ugroup === 'user' ? 'selected' : '') + '>普通用户</option>' +
                                '<option value="admin" ' + (ugroup === 'admin' ? 'selected' : '') + '>管理员</option>' +
                                '</select>' +
                                '</div>' +
                                '</div>' +
                                '<div class="layui-form-item">' +
                                '<div class="layui-input-block">' +
                                '<button type="submit" class="layui-btn" lay-submit lay-filter="editUserSubmit">保存修改</button>' +
                                '<button type="button" class="layui-btn layui-btn-primary" onclick="layer.closeAll()">取消</button>' +
                                '</div>' +
                                '</div>' +
                                '</form>' +
                                '</div>',
                        success: function(layero, index){
                            // 渲染表单
                            layui.use('form', function(){
                                var form = layui.form;
                                form.render();
                                
                                // 监听提交
                                form.on('submit(editUserSubmit)', function(data){
                                    $.ajax({
                                        type: "POST",
                                        url: "ajax/update_user.php",
                                        data: data.field,
                                        success: function (response) {
                                            if (response.success) {
                                                layer.msg('用户信息更新成功', {icon: 1});
                                                layer.closeAll();
                                                loadUsers(usersCurrentPage);
                                            } else {
                                                layer.msg('更新失败: ' + (response.error || '未知错误'), {icon: 2});
                                            }
                                        },
                                        error: function (error) {
                                            layer.msg('更新失败: ' + error, {icon: 2});
                                        }
                                    });
                                    return false; // 阻止表单跳转
                                });
                            });
                        }
                    });
                });
            }

            // 全局变量，用于存储当前查看的用户ID
            var currentUserId = 0;

            // 加载第一页数据
            loadData(currentPage);
        });
    </script>
</body>
</html>