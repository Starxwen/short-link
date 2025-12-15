# 🌟 星跃短链接系统

<div align="center">

![星跃短链接系统](https://img.shields.io/badge/星跃短链接系统-v2.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.2%2B-green.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)
![License](https://img.shields.io/badge/License-MIT-yellow.svg)

[演示网站](https://u.xwyue.com) | [在线文档](#) | [更新日志](#更新历史) | [问题反馈](#)

</div>

## 📖 项目介绍

星跃短链接系统是一个功能强大、界面美观的短链接生成与管理平台。它能够将冗长的URL地址转换为简洁易记的短链接，方便用户在各种场景下分享和使用。

### ✨ 主要特点

- 🔗 **一键生成**：输入长链接，即刻生成短链接
- 👥 **用户系统**：完整的用户注册、登录和管理功能
- 📊 **数据统计**：链接点击次数统计和数据分析
- 🎨 **美观界面**：现代化UI设计，响应式布局
- 🔒 **安全可靠**：完善的权限控制和数据保护
- 📱 **移动友好**：完美适配各种移动设备
- 🔄 **URL跳转**：支持自定义短链接和自动跳转
- 📧 **邮件验证**：支持邮箱验证和密码找回功能

## 🏗️ 系统架构

### 技术栈

- **后端语言**：PHP 7.2+
- **数据库**：MySQL 5.7+
- **前端框架**：jQuery + LayUI
- **样式框架**：CSS3 + Font Awesome
- **服务器要求**：Apache/Nginx + PHP-FPM

### 系统结构

```
short-link/
├── admin.php              # 管理员后台
├── user_panel.php         # 用户面板
├── new.php                # 首页/生成页面
├── register.php           # 用户注册
├── login.php              # 用户登录
├── logout.php             # 退出登录
├── add.php                # API接口
├── index.php              # 短链接跳转
├── config.php             # 配置文件
├── captcha.php            # 验证码生成
├── verify_email.php       # 邮箱验证
├── update.php             # 系统更新
├── ajax/                  # AJAX处理目录
│   ├── get_data.php       # 获取数据
│   ├── get_user_data.php  # 获取用户数据
│   ├── delete_data.php    # 删除数据
│   └── ...                # 其他AJAX文件
├── includes/              # 核心类库
│   ├── Settings.php       # 系统设置
│   ├── Mailer.php         # 邮件发送
│   └── AliyunMailer.php   # 阿里云邮件
├── install/               # 安装目录
│   ├── install.php        # 安装程序
│   └── index.html         # 安装向导
├── js/                    # JavaScript文件
└── user/                  # 用户目录
```

## 🚀 快速开始

### 环境要求

- PHP 7.2 或更高版本
- MySQL 5.7 或更高版本
- Apache 或 Nginx 服务器
- 支持URL重写（.htaccess）

### 安装步骤

1. **下载项目**
   ```bash
   git clone https://gitee.com/jsy-1/short-url.git
   # 或下载发行版ZIP文件
   ```

2. **上传文件**
   - 将项目文件上传到服务器或虚拟主机
   - 确保目录权限正确（755）

3. **配置数据库**
   - 创建MySQL数据库
   - 记录数据库连接信息

4. **填写配置**
   - 编辑 `config.php` 文件
   - 填写数据库连接信息
   - 设置管理员账户信息

5. **运行安装**
   - 访问 `http://your-domain/install/install.php`
   - 按照安装向导完成安装
   - 安装成功后删除 `install` 目录

6. **开始使用**
   - 访问 `new.php` 开始使用系统
   - 使用管理员账户登录后台管理

### 配置说明

`config.php` 文件配置示例：

```php
<?php
// 数据库配置
$dbhost = 'localhost';     // 数据库主机
$dbuser = 'your_username'; // 数据库用户名
$dbpass = 'your_password'; // 数据库密码
$dbname = 'short_url';     // 数据库名称

// 管理员配置
$admin_username = 'admin'; // 管理员用户名
$admin_password = md5('123456'); // 管理员密码(MD5)

// 网站配置
$my_url = 'https://your-domain.com/'; // 网站根URL
?>
```

## 📚 功能说明

### 用户功能

- **注册登录**：用户可以注册账户并登录系统
- **链接管理**：创建、查看、删除自己的短链接
- **数据统计**：查看链接点击次数和访问统计
- **个人中心**：管理个人信息和账户设置

### 管理功能

- **用户管理**：查看、编辑、删除用户账户
- **链接管理**：管理所有用户的短链接
- **系统设置**：配置网站基本信息和邮件设置
- **数据统计**：查看系统整体使用情况

### 系统特性

- **权限控制**：基于用户组的权限管理
- **邮件验证**：支持邮箱验证和密码找回
- **API接口**：提供RESTful API接口
- **安全防护**：SQL注入防护和XSS防护
- **缓存优化**：数据库查询缓存和页面缓存

## 🔧 高级配置

### URL重写规则

**Apache (.htaccess)**：
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9]+)$ index.php?id=$1 [L]
```

**Nginx**：
```nginx
location / {
    try_files $uri $uri/ /index.php?id=$uri&$args;
}
```

### 邮件配置

系统支持SMTP邮件发送，可在后台管理中配置：

- SMTP服务器地址
- SMTP端口和加密方式
- 发件人邮箱和密码
- 邮件模板设置

### 安全建议

1. **删除安装目录**：安装完成后立即删除 `install` 目录
2. **设置文件权限**：确保配置文件不可被外部访问
3. **定期备份数据**：定期备份数据库和重要文件
4. **更新系统**：及时更新到最新版本
5. **使用HTTPS**：启用SSL证书保护数据传输

## 📖 使用指南

### 创建短链接

1. 访问网站首页
2. 在输入框中输入长链接
3. 点击"生成短链接"按钮
4. 复制生成的短链接使用

### 用户注册登录

1. 点击"注册"按钮
2. 填写用户名、邮箱和密码
3. 完成邮箱验证（如启用）
4. 使用账户信息登录系统

### 管理短链接

1. 登录用户账户
2. 进入"我的链接"页面
3. 查看已创建的短链接列表
4. 可以删除不需要的链接

### 管理员操作

1. 使用管理员账户登录
2. 进入管理后台
3. 管理用户和链接数据
4. 配置系统设置

## 🔄 更新历史

### v2.0 (最新版本)
- ✨ 新增完整的用户系统
- ✨ 新增用户注册和登录功能
- ✨ 新增用户个人面板
- ✨ 新增邮箱验证功能
- ✨ 新增链接点击统计
- ✨ 优化界面设计和用户体验
- 🔧 修复已知问题
- 🔧 提升系统安全性

### v1.5
- ✨ 优化安装过程
- ✨ 改进URL跳转方式
- ✨ 优化管理界面
- 🔧 修复显示问题

### v1.0
- 🎉 项目初始版本
- ✨ 基本短链接生成功能
- ✨ 简单的管理界面

## 🛠️ 开发计划

### 即将推出
- [ ] 二级域名支持
- [ ] 链接有效期设置
- [ ] 批量生成短链接
- [ ] 自定义短链接别名
- [ ] 链接访问统计图表
- [ ] API密钥管理
- [ ] 移动端APP

### 长期规划
- [ ] 多语言支持
- [ ] 链接分组管理
- [ ] 高级统计分析
- [ ] 第三方登录集成
- [ ] 企业版功能

## 🤝 贡献指南

我们欢迎所有形式的贡献！

### 如何贡献

1. Fork 本项目
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

### 贡献类型

- 🐛 Bug修复
- ✨ 新功能开发
- 📝 文档改进
- 🎨 界面优化
- 🔧 性能优化

## 📄 许可证

本项目采用 MIT 许可证 - 查看 [LICENSE](LICENSE) 文件了解详情。

## 🙏 致谢

- 感谢原作者提供的初始版本
- 感谢所有贡献者的努力
- 感谢开源社区的支持

## 📞 联系我们

- **项目地址**：https://gitee.com/jsy-1/short-url
- **演示网站**：https://u.xwyue.com
- **问题反馈**：[Issues](https://gitee.com/jsy-1/short-url/issues)
- **技术支持**：[星跃云](https://www.xwyue.com)

---

<div align="center">

**© 2026 星跃云 - 专业的云计算服务**

[![星跃云](https://img.shields.io/badge/星跃云-云计算服务-blue.svg)](https://www.xwyue.com)

</div>
