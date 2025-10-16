# Repository Guidelines

## 项目结构与模块组织
- 根目录：`index.html`（主页）、`stylesheet.css`（全站样式）、`subPageByMd.html`（Markdown 子页）。
- 资源：`images/`（图片）、`data/`（下载与文章资源）。
- 部署：`hook.php`（配合 GitHub Webhooks 的自动更新脚本）。
- 说明：`readme.md`（自述）、`CLAUDE.md`（说明文档）。

## 构建、测试与本地开发
- 本地预览：`open index.html` 或 `python3 -m http.server 8000` 后访问 `http://localhost:8000`。
- 部署流程：提交并 `git push` 到默认分支 → 触发 GitHub Webhooks → 服务器端 `hook.php` 拉取并覆盖站点。
- 配置提示：在仓库设置中配置 Webhooks，`Secret` 与 `hook.php` 保持一致。

## 编码风格与命名约定
- 缩进与格式：2 空格缩进；尽量使用 CSS 类，避免行内样式与过时属性（如 `bgcolor`）。
- 命名：类名使用 kebab-case（如 `note-cell`、`note-meta`）；图片与资源文件名小写、连字符分隔。
- 资源组织：图片放 `images/`，可下载与示例文件放 `data/`；引用使用相对路径。

## 测试指引
- 手动检查：
  - 移动端与桌面端布局是否正常（视口缩放检查）。
  - 链接有效、图片可加载、控制台无报错、`alt` 文本完备。
- 若新增脚本/工具：建议新增 `tests/` 存放最小化检查脚本（可选），在 PR 中说明运行方式。

## 提交与 Pull Request 规范
- 提交信息：简短、祈使句、中文优先，例如：
  - `样式: 统一卡片阴影与留白`
  - `内容: 更新 Research 段落与链接`
- PR 要求：描述改动目的与范围、列出核心文件路径、附样式变更截图、关联 Issue（如有）。
- 原子化改动：避免在同一 PR 中混入无关重构或资源清理。

## 安全与配置提示（可选）
- 保密：`hook.php` 的 `Secret` 不应提交到仓库或暴露在截图中。
- 外链脚本：仅使用可信来源，避免引入未知脚本；必要时注明来源与用途。

## 面向智能代理的提示（可选）
- 仅修改必要文件；优先通过 CSS 类替代行内样式；大改前使用 `rg` 搜索引用影响面。
- 提交前自查移动端表现；所有输出、注释与 PR 描述使用中文。

