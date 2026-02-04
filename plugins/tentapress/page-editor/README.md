# Page Editor Plugin

Notion-style page editor for TentaPress with precompiled assets and per-plugin build.

## Build Assets (Bun)

From the plugin folder:

```bash
bun install
bun run build
```

Or run the helper script from anywhere:

```bash
plugins/tentapress/page-editor/bin/build.sh
```

## Build Assets (npm)

If you are not using Bun:

```bash
npm install
npm run build
```

## Output

Compiled assets are written to the plugin package:

```
plugins/tentapress/page-editor/build/
```

The admin editor view loads assets using the plugin manifest and the
`@tpPluginStyles` / `@tpPluginScripts` Blade directives.

On plugin enable/cache rebuild, assets are copied into the app public folder:

```
public/plugins/tentapress/page-editor/build/
```
