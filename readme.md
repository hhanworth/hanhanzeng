<!--
 * @Author: hhan
 * @Date: 2025-04-29 21:22:44
 * @LastEditTime: 2025-04-29 21:39:52
 * @Description: 自述文件
-->
## 文件夹结构
- data：存放资源文件，用于分享下载。
- images：存放网页图像文件。
- index.html
- hook.php
- stylesheet.css

## hook

配合项目设置中Webhooks功能，push项目时触发外部事件（发送POST请求到 http://xxx/hook.php）。   
即：When the specified events happen, we'll send a POST request to each of the URLs you provide.  

#### 开启 webhooks

登陆github后，到项目/settings/Webhooks中新建。  
填写 Payload URL 为 http://xxx/hook.php    
Secret 填写你自己在hook.php中指定的字符串  

#### hook.php
被触发时  
1. 验证 Secret  
2. 从 github 下载项目zip  
3. 删除web服务器 . 目录下除hook.php的所有文件/文件夹。 
4. 解压、复制、清理  