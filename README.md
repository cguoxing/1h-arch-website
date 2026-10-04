# 1+H Website · Integrated Design

[English](#english) · [中文](#中文)

A lightweight, database-driven portfolio website for an architecture & interior design practice — built with plain PHP + MySQL, no framework, deployable on any shared hosting.

一套为建筑与室内设计事务所打造的轻量级动态作品集网站——纯 PHP + MySQL，无框架依赖，普通虚拟主机即可部署。

Live site / 线上站点：<https://www.1h-arch.com>

---

<a id="english"></a>

## English

### Features

**Front end**

- Pages: Home, About, Team, Projects (archive), Project detail, Contact
- Cinematic home hero carousel, interactive *Project Index*, *Selected Works* grid
- Home sections rotate automatically (Hero / Project Index / Selected Works never repeat a project)
- Project archive with type filter, year-range filter, live search and "Load more" pagination
- Project gallery with image preloading, keyboard ← → and swipe support
- Smooth cross-page transitions (View Transitions API) and link prefetching (Speculation Rules)
- Responsive images: 640 / 1280 px WebP variants generated automatically, served via `srcset`, lazy loading below the fold
- SEO: per-page title & description, canonical, Open Graph, JSON-LD (Organization / CreativeWork), auto-generated `sitemap.xml`

**Admin (`/admin.php`)**

- Projects: create / edit / delete, multiple image upload (async, with watermark), drag-to-reorder, statuses (Published / Draft / Hidden / Sample)
- Home configuration: hero, project index, selected works, automatic rotation intervals
- Team members (founders & design team) with photos
- **Page content editor**: every text and image on Home / About / Team / Projects / Project / Contact, plus SEO fields — repeatable lists can be added, removed and reordered
- **Inquiries**: contact-form submissions stored in the database; mark read, archive, delete, export CSV; optional e-mail notification
- **Tools**: batch-generate image variants, change admin password

**安全**

- Prepared statements everywhere, all output escaped
- `password_hash` / `password_verify`, session fixation protection, login throttling
- CSRF tokens on every admin form and upload endpoint
- Contact form: honeypot, signed time token, per-IP rate limit
- Upload whitelist (JPG/PNG/WebP/GIF), random file names, PHP execution blocked in the upload directory
- `.htaccess` denies access to `includes/`, `deploy/`, `bak/` and sensitive file types

### Tech stack

| | |
|---|---|
| Server | PHP **7.1+** (8.x recommended) with `mysqli`; `gd` (with WebP) recommended; `mbstring` optional |
| Database | MySQL 5.7+ / MariaDB 10.x |
| Web server | Apache with `.htaccess` (mod_rewrite / headers / expires / deflate — all optional) |
| Front end | Vanilla HTML / CSS / JavaScript, no build step |

### Project structure

```text
├── index.php / about.php / team.php / projects.php / project.php / contact.php   # public pages
├── admin.php                 # projects, home rotation, team
├── admin-content.php         # page content, inquiries, tools
├── sitemap.php               # served as /sitemap.xml via .htaccess
├── api/
│   ├── upload.php            # async image upload (watermark + variants)
│   ├── inquiry.php           # contact form endpoint
│   └── thumbs.php            # batch image-variant generation
├── includes/
│   ├── config.example.php    # config template → copy to config.php (git-ignored)
│   ├── db.php                # connection, schema install / upgrade
│   ├── projects.php          # projects, settings, home rotation
│   ├── content.php           # editable page content (defaults + merge)
│   ├── layout.php            # shared <head>, header, footer, SEO
│   ├── images.php            # responsive image variants
│   ├── inquiries.php         # contact form logic
│   ├── security.php          # session, CSRF
│   └── auth.php / admin-ui.php / helpers.php
├── install/                  # first-time setup & schema upgrade
├── img/                      # static images
├── project-uploads/          # uploaded images (not tracked by git)
├── styles.css, motion-reference.css, *.js
└── DYNAMIC_SITE_SETUP.md     # detailed deployment guide (Chinese)
```

### Quick start (local)

```bash
# 0. Create your config from the template (config.php is git-ignored)
cp includes/config.example.php includes/config.php

# 1. Create a database
mysql -u root -e "CREATE DATABASE oneh_arch CHARACTER SET utf8mb4;
  CREATE USER 'oneh'@'localhost' IDENTIFIED BY 'change-me';
  GRANT ALL ON oneh_arch.* TO 'oneh'@'localhost';"

# 2. Start PHP's built-in server with connection settings as env vars
DB_HOST=127.0.0.1 DB_NAME=oneh_arch DB_USER=oneh DB_PASS=change-me \
  php -S 127.0.0.1:8080

# 3. Open http://127.0.0.1:8080/install/ and click "Create tables"
# 4. Sign in at http://127.0.0.1:8080/admin.php
#    (initial account: ONEH_BOOTSTRAP_ADMIN_USER / _PASS in includes/config.php,
#     default admin / change-this-password — change it in config.php before installing)
# 5. Change the password at Admin → Tools
```

> The built-in server ignores `.htaccess`, so `/sitemap.xml` is only available as `/sitemap.php` locally.

### Deploying to shared hosting

1. Upload all files to the web root (do **not** upload `.git/`).
2. Copy `includes/config.example.php` to `includes/config.php` and set the database credentials and initial admin password (or use environment variables).
3. Make sure `project-uploads/` is writable.
4. Visit `/install/` to create the tables, then sign in and **change the admin password**.
5. Admin → Tools → *Generate image variants* (once, for existing images).
6. Admin → Page content → *Site & SEO*: set the site URL; update the Sitemap line in `robots.txt`.

See [`DYNAMIC_SITE_SETUP.md`](DYNAMIC_SITE_SETUP.md) for the full guide.

### Customising for your own studio

- All page copy lives in the admin editor; defaults are in `includes/content.php`.
- Project types and year ranges: `oneh_type_labels()` / `oneh_period_labels()` in `includes/helpers.php`.
- Colours, typography and layout: `styles.css` (design tokens in `:root`) and `motion-reference.css`.
- Replace the logo (`img/new-logo.svg`), favicon and images in `img/`.

### License

Source code is released under the [MIT License](LICENSE).

The **1+H name, logo, brand identity, project photographs and written content** are © 上海易弘建筑工程设计有限公司 (1+H ARCH) and are **not** covered by the MIT License. Please replace them with your own before using this project for another website.

---

<a id="中文"></a>

## 中文

### 功能

**前台**

- 页面：首页、About、团队、项目列表、项目详情、联系
- 首页大图轮播、可交互的 Project Index、Selected Works 精选作品
- 首页三个区块自动轮换，且互不重复
- 项目列表：按类型 / 年份段筛选、实时搜索、「加载更多」分页
- 项目图集：预加载下一张，支持键盘左右键与手机滑动
- 页面切换淡入过渡（View Transitions），站内链接预取（Speculation Rules）
- 响应式图片：自动生成 640 / 1280 宽 WebP，按屏幕输出 `srcset`，首屏以外懒加载
- SEO：每页标题与描述、canonical、Open Graph 分享信息、结构化数据、自动生成 `sitemap.xml`

**后台（`/admin.php`）**

- 项目：增删改、多图异步上传（自动水印）、拖拽排序、状态（发布 / 草稿 / 隐藏 / 示例）
- 首页配置：首屏轮播、Project Index、Selected Works 及自动轮换周期
- 团队：合伙人与设计团队成员、照片
- **页面文案**：首页 / About / 团队 / 项目列表 / 项目详情 / Contact 的全部文字与配图及 SEO 字段，列表可增删、排序
- **留言**：联系表单提交保存到数据库，可标记已读、归档、删除、导出 CSV，可选邮件提醒
- **工具**：批量生成图片压缩版本、修改后台密码

**安全**

- 全部数据库访问使用预处理语句，页面输出统一转义
- 密码哈希存储、防会话固定、登录失败延时
- 后台表单与上传接口全部校验 CSRF 令牌
- 联系表单：蜜罐字段、签名时间令牌、按 IP 限频
- 上传格式白名单、随机文件名、上传目录禁止执行 PHP
- `.htaccess` 禁止访问 `includes/`、`deploy/`、`bak/` 及敏感文件

### 技术栈

| | |
|---|---|
| 服务端 | PHP **7.1+**（推荐 8.x），需 `mysqli`；推荐开启 `gd`（含 WebP）；`mbstring` 可选 |
| 数据库 | MySQL 5.7+ / MariaDB 10.x |
| Web 服务器 | Apache + `.htaccess`（rewrite / headers / expires / deflate 模块均为可选） |
| 前端 | 原生 HTML / CSS / JavaScript，无需构建 |

### 本地运行

```bash
# 0. 从模板创建配置文件（config.php 不进 git）
cp includes/config.example.php includes/config.php

# 1. 创建数据库
mysql -u root -e "CREATE DATABASE oneh_arch CHARACTER SET utf8mb4;
  CREATE USER 'oneh'@'localhost' IDENTIFIED BY 'change-me';
  GRANT ALL ON oneh_arch.* TO 'oneh'@'localhost';"

# 2. 用环境变量传入数据库信息，启动 PHP 内置服务器
DB_HOST=127.0.0.1 DB_NAME=oneh_arch DB_USER=oneh DB_PASS=change-me \
  php -S 127.0.0.1:8080

# 3. 打开 http://127.0.0.1:8080/install/ 点击创建数据表
# 4. 登录 http://127.0.0.1:8080/admin.php
#    （初始账号见 includes/config.php 中 ONEH_BOOTSTRAP_ADMIN_USER / _PASS，
#     默认 admin / change-this-password，建议安装前先在 config.php 中改掉）
# 5. 到「后台 → 工具」修改密码
```

> PHP 内置服务器不读取 `.htaccess`，本地请用 `/sitemap.php` 查看站点地图。

### 部署到虚拟主机

1. 把文件上传到网站根目录（不要上传 `.git/`）。
2. 把 `includes/config.example.php` 复制为 `includes/config.php`，填写数据库信息和初始管理员密码（也可用环境变量）。
3. 确保 `project-uploads/` 目录可写。
4. 访问 `/install/` 创建数据表，登录后台后**立即修改管理员密码**。
5. 「后台 → 工具 → 生成压缩版本」，为已有图片生成小图（只需一次）。
6. 「后台 → 页面文案 → 站点与 SEO」填写正式网址，并同步修改 `robots.txt` 中的 Sitemap 地址。

完整说明见 [`DYNAMIC_SITE_SETUP.md`](DYNAMIC_SITE_SETUP.md)。

### 改成你自己的事务所网站

- 页面文字全部可在后台修改，默认值位于 `includes/content.php`。
- 项目类型与年份段：`includes/helpers.php` 中的 `oneh_type_labels()` / `oneh_period_labels()`。
- 配色、字体与版式：`styles.css`（`:root` 中的设计变量）与 `motion-reference.css`。
- 替换 `img/` 中的 Logo（`new-logo.svg`）、favicon 和图片。

### 许可

源代码以 [MIT 许可证](LICENSE) 开源。

**1+H 名称、Logo、品牌视觉、项目照片及文字内容**版权归上海易弘建筑工程设计有限公司（1+H ARCH）所有，**不在** MIT 许可范围内。将本项目用于其他网站前，请替换为你自己的品牌与内容。
