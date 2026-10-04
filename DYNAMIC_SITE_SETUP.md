# 1+H 动态站点上线步骤

## 1. 配置数据库

在西部数码虚拟主机后台创建 MySQL 数据库后，把配置模板复制一份（`config.php` 含密码，不进 git）：

```text
cp includes/config.example.php includes/config.php
```

然后编辑 `includes/config.php`。**已上线的站点不要用新文件覆盖服务器上现有的 `config.php`。**

把以下配置改为服务商提供的信息（也可以用环境变量 DB_HOST / DB_NAME / DB_USER / DB_PASS 传入）：

```php
define('DB_HOST', '数据库地址');
define('DB_NAME', '数据库名');
define('DB_USER', '数据库用户名');
define('DB_PASS', '数据库密码');
define('DB_PORT', '3306');
```

## 2. 初始化 / 升级数据表

上传代码到 `/wwwroot/` 后，浏览器访问：

```text
https://你的域名/install/
```

- **新站**：点击“创建数据表并导入示例数据”。
- **已上线的站点**：install 页面只对已登录的管理员开放，点击“升级数据表”即可补齐新版本需要的表（不会改动已有项目和文案）。即使不点，后台首次打开时也会自动升级。

数据表：

- `admins` 管理员
- `projects` / `project_images` 项目与图片
- `site_settings` 首页轮播、团队、页面文案等配置
- `inquiries` 联系表单留言（2026-10 新增）

首次初始化的后台账号密码见 `includes/config.php` 中的 `ONEH_BOOTSTRAP_ADMIN_USER` / `ONEH_BOOTSTRAP_ADMIN_PASS`。**登录后立即到「后台 → 工具 → 修改后台密码」改成强密码。**

## 3. 后台

```text
https://你的域名/admin.php
```

| 菜单 | 用途 |
|---|---|
| 项目与首页 | 项目增删改、多图上传、首页轮播 / Project Index / Selected Works、团队成员 |
| 页面文案 | 首页、About、团队页、项目列表页、项目详情页、Contact 的所有文字与配图；每页 SEO 标题和描述；站点网址、备案号、留言提醒邮箱 |
| 留言 | Contact 页面表单提交的留言，可标记已读、归档、删除、导出 CSV |
| 工具 | 批量生成图片压缩版本、修改后台密码、站点地图入口 |

项目状态说明：

- `Published`：前台显示
- `Draft`：草稿，不显示
- `Hidden`：隐藏，不显示
- `Sample`：示意数据，不显示；当前无正式项目时前台会用示例兜底

上线正式内容后，建议把示例项目改为 `Hidden` 或删除。

## 4. 图片上传与压缩

后台上传图片会保存到 `project-uploads/`，需要确保这个目录在虚拟主机上可写。

- 上传时自动加 © 水印，并自动生成 640px / 1280px 的压缩版本（保存在 `project-uploads/_thumbs/`）。
- 前台按屏幕尺寸自动选择合适的图片，手机端流量约为原来的 1/5。
- 旧图片（包括 `img/` 下的静态图）可在「后台 → 工具 → 生成压缩版本」一键补齐。**首次上线新版本后请点一次。**

项目里的“排序”是前台列表顺序：数字越小越靠前。建议使用 `10、20、30` 这样的间隔。

## 5. 联系表单

- Contact 页面的表单直接提交到网站（`api/inquiry.php`），留言保存在数据库，后台「留言」查看。
- 防垃圾：隐藏蜜罐字段、3 秒内提交拦截、签名令牌、同一 IP 每小时最多 5 条。
- 如在「页面文案 → 站点与 SEO」填写了提醒邮箱，并且主机支持 PHP `mail()`，新留言会同时发邮件提醒；不支持时不影响留言保存。

## 6. SEO

- 每个页面的标题、描述可在「页面文案」中修改；项目详情页自动使用项目名称、简介和首图。
- 自动输出 canonical、Open Graph 分享信息和结构化数据（Organization / CreativeWork）。
- 站点地图 `https://你的域名/sitemap.xml` 自动包含所有已发布项目，可提交到百度 / 必应 / Google 站长平台。
- 「页面文案 → 站点与 SEO」里的“站点网址”需要与正式域名一致；`robots.txt` 里的 Sitemap 地址也请同步修改。

## 7. 服务器配置（.htaccess）

根目录 `.htaccess` 已配置：

- `sitemap.xml` 映射到 `sitemap.php`
- 禁止访问 `includes/`、`deploy/`、`bak/` 及 `.md/.sql` 等文件
- 禁止在 `project-uploads/` 执行 PHP
- Gzip 压缩与静态资源长缓存（CSS/JS 链接自带文件修改时间作为版本号，改文件后浏览器自动取新版，无需手动改 `?v=`）

如果主机不是 Apache（例如 Nginx / IIS），需要在主机面板中设置等效规则。

## 8. 安全建议

- 正式上线前开启 SSL。
- `includes/config.php` 里的默认数据库密码和初始管理员密码只用于本地 / NAS 测试，线上务必改掉。
- 定期备份 MySQL 数据库和 `project-uploads/` 目录。
