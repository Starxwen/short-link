# 短链接系统安装指南

## 概述

本目录包含了短链接系统的安装和升级脚本。经过分析，原始的安装脚本缺少一些重要的数据库字段和表，这些改进版本解决了这些问题。

## 文件说明

### 1. install.php
原始的安装脚本，存在数据库字段不完整的问题。

### 2. install_improved.php
改进的安装脚本，包含以下修复：

- **go_to_url 表**：添加了 `click_count` 字段用于统计点击次数
- **users 表**：
  - 将 `email` 字段长度从 VARCHAR(32) 扩展到 VARCHAR(255)
  - 添加了 `email_verified` 字段用于邮箱验证状态
  - 添加了 `verification_code` 字段用于存储验证码
  - 添加了 `verification_expires` 字段用于验证码过期时间
- **settings 表**：全新的系统设置表，用于存储系统配置

### 3. upgrade_database.php
数据库升级脚本，用于为已安装的系统添加缺失的字段和表。

### 4. index.html
安装向导页面，提供用户友好的安装界面。

## 安装方法

### 新安装

1. **备份原始安装脚本**（可选）：
   ```bash
   cp install.php install_original.php
   ```

2. **使用改进的安装脚本**：
   ```bash
   cp install_improved.php install.php
   ```

3. **通过Web界面安装**：
   - 访问 `install/` 目录
   - 按照安装向导填写数据库信息
   - 完成安装

### 已有系统升级

如果您的系统已经安装，但缺少某些功能，请使用升级脚本：

1. **访问升级脚本**：
   ```
   http://您的域名/install/upgrade_database.php
   ```

2. **确认升级**：
   - 脚本会自动检测缺失的字段和表
   - 自动添加缺失的数据库结构
   - 插入默认的系统设置

## 数据库结构对比

### 原始 vs 改进的 go_to_url 表

| 字段名 | 原始版本 | 改进版本 | 说明 |
|--------|----------|----------|------|
| num | ✓ | ✓ | 主键 |
| url | ✓ | ✓ | 原始URL |
| short_url | ✓ | ✓ | 短链接代码 |
| ip | ✓ | ✓ | IP地址 |
| add_date | ✓ | ✓ | 添加时间 |
| uid | ✓ | ✓ | 用户ID |
| click_count | ✗ | ✓ | 点击次数（新增） |

### 原始 vs 改进的 users 表

| 字段名 | 原始版本 | 改进版本 | 说明 |
|--------|----------|----------|------|
| uid | ✓ | ✓ | 主键 |
| username | ✓ | ✓ | 用户名 |
| password | ✓ | ✓ | 密码 |
| email | VARCHAR(32) | VARCHAR(255) | 邮箱地址（长度增加） |
| ugroup | ✓ | ✓ | 用户组 |
| email_verified | ✗ | ✓ | 邮箱验证状态（新增） |
| verification_code | ✗ | ✓ | 验证码（新增） |
| verification_expires | ✗ | ✓ | 验证码过期时间（新增） |

### 新增的 settings 表

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | INT | 主键 |
| setting_key | VARCHAR(100) | 设置键名 |
| setting_value | TEXT | 设置值 |
| setting_name | VARCHAR(100) | 设置显示名称 |
| category | VARCHAR(50) | 设置分类 |
| updated_at | TIMESTAMP | 更新时间 |

## 默认系统设置

改进的安装脚本会自动插入以下默认设置：

| 设置键名 | 默认值 | 说明 |
|----------|--------|------|
| site_name | 星跃短链接 | 网站名称 |
| site_url | 安装时填写的URL | 网站URL |
| allow_registration | 1 | 允许用户注册 |
| email_verification_required | 0 | 需要邮箱验证 |
| smtp_host | 空 | SMTP服务器 |
| smtp_port | 587 | SMTP端口 |
| smtp_username | 空 | SMTP用户名 |
| smtp_password | 空 | SMTP密码 |
| smtp_encryption | tls | 加密方式 |
| email_from_address | 空 | 发件人邮箱 |
| email_from_name | 空 | 发件人名称 |

## 功能影响

使用改进的安装脚本后，以下功能将完全可用：

1. **点击统计**：可以统计每个短链接的点击次数
2. **邮箱验证**：完整的邮箱注册和验证功能
3. **系统设置**：通过管理面板配置系统参数
4. **邮件发送**：SMTP邮件发送功能

## 注意事项

1. **备份数据**：在升级前请备份现有数据库
2. **权限检查**：确保Web服务器有权限写入配置文件
3. **安装锁**：安装完成后会创建 install.lock 文件
4. **删除安装目录**：安装完成后建议删除 install 目录

## 故障排除

### 安装失败
- 检查数据库连接信息是否正确
- 确保数据库用户有创建表和插入数据的权限
- 检查目录权限，确保可以写入配置文件

### 升级失败
- 检查 config.php 文件是否存在且配置正确
- 确保数据库用户有 ALTER TABLE 权限
- 查看错误信息，可能是字段已存在或其他SQL错误

### 功能异常
- 确认所有数据库字段都已正确添加
- 检查 settings 表中是否有必要的设置项
- 查看系统日志，确认没有PHP错误

## 技术支持

如果遇到问题，请检查：

1. PHP错误日志
2. MySQL错误日志
3. Web服务器错误日志

确保所有文件权限正确，数据库连接信息准确。