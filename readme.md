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


