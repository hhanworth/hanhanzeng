<!--
 * @Author: hhan
 * @Date: 2025-04-29 21:22:44
 * @LastEditTime: 2025-04-30 21:19:22
 * @Description: 自述文件
-->

## 文件夹结构

- data：存放资源文件，用于分享下载。
- images：存放网页图像文件。
- index.html
- hook.php
- stylesheet.css
- CV：由简历仓库根目录的 `main2.tex` 导出的静态网页简历，对应 `/CV/`。

## 网页简历与视频

网页简历直接访问 `/CV/`，该目录的静态文件由现有部署流程一起发布。
`/CV` 使用静态服务器的目录首页规则访问或跳转到 `/CV/`，无需新增后端路由。

简历内容源位于本机 `/Volumes/GVE-1T/Document/CV/main2.tex`，排版以同目录的 `main2.pdf` 为准。
`resume.svg` 完整保留原 PDF 的矢量文字、配色、照片、线条和位置；`index.html` 提供可选择的文字层及正确的联系链接。
页面按 A4 原比例缩放、打印，并提供 `resume.pdf` 原文件下载。

更新 LaTeX 源文件并重新编译 PDF 后执行（本机需要 Poppler 和 MuPDF）：

```bash
python3 /Volumes/GVE-1T/Document/CV/resume/export_html.py --source /Volumes/GVE-1T/Document/CV/main2.tex --output /Users/han/Desktop/jonbarron.github.io-master/CV
```

导出脚本会校验 PDF 与 TeX 内容，更新 HTML、SVG 和 PDF；外层样式与文字层适配代码在 `CV/style.css`、`CV/resume.js` 中维护。
提交并推送本仓库 `main` 分支后，通过已有同步流程上线。

首页 halo cubic 视频直接使用 `https://doc.hhan.top/cubiccub.mov`，设置 `autoplay muted loop playsinline`，并保留原生控件供暂停或手动播放。浏览器设置或省电模式仍可能限制自动播放。

## hook

配合项目设置中 Webhooks 功能，push 项目时触发外部事件（发送 POST 请求到 http://xxx/hook.php）。  
即：When the specified events happen, we'll send a POST request to each of the URLs you provide.

### 开启 webhooks

登陆 github 后，到项目/settings/Webhooks 中新建。  
填写 Payload URL 为 http://xxx/hook.php  
Secret 填写你自己在 hook.php 中指定的字符串

## hook.php

被触发时

1. 验证 Secret
2. 从 github 下载项目 zip
3. 删除 web 服务器 . 目录下除 hook.php 的所有文件/文件夹。
4. 解压、复制、清理



## subPageByMd.html
``` html
    <a href="subPageByMd.html?file=data/jpeg" ,target="_blank"><span class="papertitle">
        jpeg编解码压缩
    </span> </a>
```
调用子页面时传输“jpeg”，即文件名信息。subPageByMd纯前端直接渲染Markdown内容。
