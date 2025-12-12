# 星跃短链接系统用户功能更新指南

## 更新概述

本次更新为星跃短链接系统添加了完整的用户管理功能，包括：
- 用户注册和登录
- 用户个人面板
- 用户管理自己的短链接
- 管理员权限控制

## 更新步骤

### 1. 备份数据库
在执行更新之前，请务必备份现有数据库：
```sql
mysqldump -u username -p database_name > backup.sql
```

### 2. 运行数据库更新脚本
访问 `http://your-domain/update_database.php` 来更新数据库结构。

该脚本将：
- 检查并更新 `users` 表结构
- 确保 `go_to_url` 表包含 `uid` 字段
- 创建 `user_sessions` 表（用于会话管理）
- 验证管理员账户

### 3. 删除更新脚本
更新完成后，请删除 `update_database.php` 文件以确保安全。

## 新增文件

### 用户相关页面
- `register.php` - 用户注册页面
- `user_panel.php` - 用户个人面板
- `logout.php` - 退出登录功能

### Ajax处理文件
- `ajax/get_user_data.php` - 获取用户短链接数据
- `ajax/delete_user_data.php` - 删除用户短链接

### 更新脚本
- `update_database.php` - 数据库更新脚本
- `UPDATE_GUIDE.md` - 本更新指南

## 修改的文件

### 核心功能文件
- `login.php` - 支持普通用户和管理员登录
- `add.php` - 支持用户登录状态的短链接创建
- `new.php` - 根据登录状态显示不同按钮
- `admin.php` - 更新权限检查和用户信息显示

## 功能特性

### 用户注册
- 用户名：3-20个字符，支持字母、数字和下划线
- 密码：至少6个字符
- 邮箱：可选，用于后续功能扩展

### 用户登录
- 支持普通用户和管理员登录
- 自动跳转到对应面板
- 记录最后登录时间

### 用户面板
- 查看自己创建的短链接
- 删除自己的短链接
- 分页显示
- 响应式设计

### 权限控制
- 用户只能管理自己的短链接
- 管理员可以管理所有短链接
- 基于会话的身份验证

## 数据库变更

### users 表结构
```sql
CREATE TABLE `users` (
  `uid` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(64) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `email` VARCHAR(100) DEFAULT '',
  `ugroup` VARCHAR(32) DEFAULT 'user',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL,
  `status` TINYINT DEFAULT 1 COMMENT '1:正常 0:禁用',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### go_to_url 表变更
- 确保 `uid` 字段存在（用于关联用户）
- 现有数据的 `uid` 默认为 0（表示匿名用户）

### user_sessions 表（新增）
```sql
CREATE TABLE `user_sessions` (
  `session_id` VARCHAR(128) NOT NULL,
  `user_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## 安全注意事项

1. **删除更新脚本**：更新完成后立即删除 `update_database.php`
2. **文件权限**：确保配置文件 `config.php` 不可被外部访问
3. **会话安全**：系统使用 PHP 会话进行身份验证
4. **输入验证**：所有用户输入都经过验证和转义

## 兼容性说明

- 现有短链接数据完全兼容
- 匿名用户（uid=0）创建的短链接仍然有效
- 管理员账户保持不变
- 现有 API 接口保持兼容

## 故障排除

### 常见问题

1. **数据库更新失败**
   - 检查数据库连接配置
   - 确认数据库用户权限
   - 查看错误日志

2. **用户无法登录**
   - 检查 users 表是否正确创建
   - 确认密码加密方式（MD5）
   - 检查会话配置

3. **权限问题**
   - 确认文件权限设置
   - 检查目录可写权限
   - 验证 .htaccess 配置

### 技术支持

如遇到问题，请检查：
1. PHP 错误日志
2. MySQL 错误日志
3. Web 服务器访问日志

## 后续计划

未来版本可能包含：
- 邮箱验证功能
- 密码重置功能
- 用户统计面板
- 短链接点击统计
- API 密钥管理
- 二级域名支持

---

**重要提醒**：在生产环境部署前，请在测试环境充分测试所有功能。