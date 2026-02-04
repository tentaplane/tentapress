# Block Markdown Editor Plugin

Markdown block editor for TentaPress with precompiled assets and per-plugin build.

## Build Assets (Bun)

From the plugin folder:

```bash
bun install
bun run build
```

Or run the helper script:

```bash
plugins/tentapress/block-markdown-editor/bin/build.sh
```

## Build Assets (npm)

If you are not using Bun:

```bash
npm install
npm run build
```

## Output

Compiled assets are written to:

```
plugins/tentapress/block-markdown-editor/build/
```

On plugin enable/cache rebuild, assets are copied into:

```
public/plugins/tentapress/block-markdown-editor/build/
```
