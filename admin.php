<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理 - 星跃短链接生成器</title>
    <link rel="stylesheet" href="https://cdn.staticfile.org/layui/2.5.6/css/layui.min.css" media="all">
    <script src="https://cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.bootcdn.net/ajax/libs/layui/2.5.6/layui.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .layui-container {
            width: 90%;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            /* 确保表格列宽固定 */
        }

        th {
            text-align: center;
        }

        td {
            text-align: left;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
            /* 顶部对齐，以便在换行时内容不显得拥挤 */
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination button {
            padding: 5px 10px;
            margin: 0 5px;
            cursor: pointer;
        }

        /* 针对URL列的样式调整 */
        #data-table th:nth-child(2),
        #data-table td:nth-child(2) {
            width: 50%;
            /* 根据需要调整列宽 */
            word-wrap: break-word;
            /* 允许长单词或URL在必要时换行 */
            word-break: break-all;
            /* 在任意位置换行，包括长单词内 */
            white-space: normal;
            /* 允许空白符换行 */
            overflow: hidden;
            /* 隐藏超出单元格的内容（通常与换行结合使用） */
            text-overflow: ellipsis;
            /* 如果内容仍然超出，则显示省略号（但这在换行时通常不需要） */
        }

        /* 其他列的样式可以保持不变，或者根据需要调整 */
        #data-table th:nth-child(1),
        #data-table td:nth-child(1) {
            width: 5%;
            text-align: center;
        }

        #data-table th:nth-child(3),
        #data-table td:nth-child(3) {
            width: 20%;
        }

        #data-table th:nth-child(4),
        #data-table td:nth-child(4) {
            width: 10%;
            text-align: center;
        }

        #data-table th:nth-child(5),
        #data-table td:nth-child(5) {
            width: 10%;
            text-align: center;
        }

        #data-table th:nth-child(6),
        #data-table td:nth-child(6) {
            width: 5%;
            text-align: center;
        }

        #data-table th:nth-child(7),
        #data-table td:nth-child(7) {
            width: 5%;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="layui-container">
        <h2>admin后台</h2>
        <div class="table-container">
            <table id="data-table">
                <thead>
                    <tr>
                        <th>编号</th>
                        <th>URL地址</th>
                        <th>短链接</th>
                        <th>用户IP地址</th>
                        <th>添加日期</th>
                        <th>用户id</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 数据行将通过JavaScript动态插入 -->
                </tbody>
            </table>
        </div>
        <div class="pagination" id="pagination"></div>
    </div>

    <script>
        $(document).ready(function () {
            var currentPage = 1;
            var perPage = 12; // 每页显示的数据条数

            function loadData(page) {
                $.ajax({
                    type: "POST",
                    url: "ajax/get_data.php",
                    data: { page: page, perPage: perPage },
                    success: function (response) {
                        var data = response;//JSON.parse(response);
                        var tbody = $('#data-table tbody');
                        tbody.empty(); // 清空当前表格内容

                        data.rows.forEach(function (row) {
                            var tr = $('<tr></tr>');
                            tr.append($('<td></td>').text(row.num));
                            tr.append($('<td></td>').text(decodeURIComponent(escape(window.atob(row.url)))));
                            tr.append($('<td></td>').text('<?php include './config.php';
                            echo $my_url; ?>' + row.short_url));
                            tr.append($('<td></td>').text(row.ip));
                            tr.append($('<td></td>').text(row.add_date));
                            tr.append($('<td></td>').text(row.uid));
                            var deleteBtn = $('<button></button>').text('删除').data('num', row.num).click(function () {
                                deleteData($(this).data('num'));
                            });
                            tr.append($('<td></td>').append(deleteBtn));
                            tbody.append(tr);
                        });

                        // 更新分页按钮
                        var pagination = $('#pagination');
                        pagination.empty();
                        for (var i = 1; i <= data.totalPages; i++) {
                            var btn = $('<button></button>').text(i).click(function () {
                                loadData($(this).text());
                            });
                            if (i === page) {
                                btn.addClass('layui-btn-disabled');
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

            loadData(currentPage); // 加载第一页数据
        });
    </script>

</body>

</html>