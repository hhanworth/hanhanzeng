# CLAUDE.md

此文件为 Claude Code (claude.ai/code) 在本仓库中处理代码时的指导。

## 常用命令
- 本项目为静态网站，无构建、lint 或测试命令。开发时直接编辑 HTML/CSS/JS/PHP 文件。
- 本地预览：打开 index.html 在浏览器中查看；若需测试 hook.php，使用 `php -S localhost:8000` 启动本地服务器。
- 部署：通过 GitHub Webhook 触发 hook.php 自动更新服务器文件（详见 readme.md）。

## 代码架构概述
- 项目基于 Jon Barron 的个人网站模板二次开发，主要为静态 HTML 页面，用于展示个人信息、研究笔记。
- 核心结构：
  - index.html：主页面，包含个人信息、研究日志，使用表格布局和 CSS 样式。
  - subPageByMd.html：子页面 Markdown 渲染器，使用 marked.js 库从 data/ 目录加载 .md 文件，支持相对路径图像（如 md-pic/ 子目录）。
  - data/：资源目录，存放 Markdown 内容（如 jpeg.md，描述 JPEG 编解码、DCT 变换实现）和附件（如 RTF 文档）。
  - images/：图像资源。
  - hook.php：GitHub Webhook 处理器，验证 Secret 后下载仓库 ZIP、清理并部署文件到服务器。
  - stylesheet.css：全局样式。
- 工作流：编辑文件后 push 到 GitHub，Webhook 触发 hook.php 自动部署；子页面通过 URL 参数（如 ?file=data/jpeg）加载 Markdown 内容，实现纯前端渲染。