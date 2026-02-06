#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
ROOT_DIR=$(cd "${SCRIPT_DIR}/../../../.." && pwd)
DEFAULT_SOURCE="${ROOT_DIR}/themes/tentapress/tailwind"

vendor=""
theme=""
name=""
source=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        --vendor)
            vendor="${2:-}"
            shift 2
            ;;
        --theme)
            theme="${2:-}"
            shift 2
            ;;
        --name)
            name="${2:-}"
            shift 2
            ;;
        --source)
            source="${2:-}"
            shift 2
            ;;
        -h|--help)
            cat <<'EOF'
Usage: clone-theme --vendor <vendor> --theme <theme> [--source <path>]

Options:
  --vendor   Vendor namespace (folder name under themes/)
  --theme    Theme name (folder name under themes/<vendor>/)
  --name     Display name (defaults to Title Case + " Theme")
  --source   Source theme path (defaults to themes/tentapress/tailwind)
EOF
            exit 0
            ;;
        *)
            echo "Unknown argument: $1" >&2
            exit 1
            ;;
    esac
 done

if [[ -z "${vendor}" ]]; then
    read -r -p "Vendor (folder under themes/): " vendor
fi

if [[ -z "${theme}" ]]; then
    read -r -p "Theme (folder name): " theme
fi

if [[ -z "${name}" ]]; then
    read -r -p "Display name (optional): " name
fi

if [[ -z "${source}" ]]; then
    read -r -p "Source theme path [${DEFAULT_SOURCE}]: " source
    source="${source:-$DEFAULT_SOURCE}"
fi

if [[ -z "${vendor}" || -z "${theme}" ]]; then
    echo "Vendor and theme are required." >&2
    exit 1
fi

if [[ "${vendor}" =~ [A-Z] || "${theme}" =~ [A-Z] ]]; then
    echo "Vendor and theme must be lowercase (use kebab-case if needed)." >&2
    exit 1
fi

if [[ ! -d "${source}" ]]; then
    echo "Source theme path not found: ${source}" >&2
    exit 1
fi

if [[ ! -f "${source}/tentapress.json" ]]; then
    echo "Source theme missing tentapress.json: ${source}" >&2
    exit 1
fi

dest="${ROOT_DIR}/themes/${vendor}/${theme}"

if [[ -e "${dest}" ]]; then
    echo "Destination already exists: ${dest}" >&2
    exit 1
fi

mkdir -p "${ROOT_DIR}/themes/${vendor}"

if command -v rsync >/dev/null 2>&1; then
    rsync -a \
        --exclude 'bin/clone.sh' \
        --exclude 'node_modules' \
        --exclude 'build' \
        --exclude '.git' \
        --exclude '.DS_Store' \
        "${source}/" "${dest}/"
else
    cp -R "${source}" "${dest}"
    rm -f "${dest}/bin/clone.sh"
    rm -rf "${dest}/node_modules" "${dest}/build" "${dest}/.git" "${dest}/.DS_Store"
fi

studly() {
    local input="$1"
    local out=""
    local part
    IFS='-_/ ' read -r -a parts <<< "$input"
    for part in "${parts[@]}"; do
        [[ -z "$part" ]] && continue
        out+=$(printf '%s' "$part" | awk '{print toupper(substr($0,1,1)) substr($0,2)}')
    done
    printf '%s' "$out"
}

title_case() {
    local input="$1"
    local out=""
    local part
    IFS='-_/ ' read -r -a parts <<< "$input"
    for part in "${parts[@]}"; do
        [[ -z "$part" ]] && continue
        part="${part:0:1}"${part:1}
        if [[ -z "$out" ]]; then
            out="$part"
        else
            out+=" $part"
        fi
    done
    printf '%s' "$out"
}

vendor_ns=$(studly "$vendor")
theme_ns=$(studly "$theme")
provider_class="${theme_ns}ThemeServiceProvider"
provider_namespace="${vendor_ns}\\Themes\\${theme_ns}"
provider_full="${provider_namespace}\\${provider_class}"

theme_title=$(title_case "$theme")
display_name="${name:-${theme_title} Theme}"

php -r "
\$path = '$dest/tentapress.json';
\$data = json_decode(file_get_contents(\$path), true, 512, JSON_THROW_ON_ERROR);
\$data['id'] = '${vendor}/${theme}';
\$data['name'] = '${display_name}';
\$data['version'] = '0.0.1';
\$data['provider'] = '${provider_full}';
\$data['provider_path'] = 'src/${provider_class}.php';
file_put_contents(\$path, json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
"

data_version="0.0.1"

php -r "
\$path = '$dest/composer.json';
\$data = json_decode(file_get_contents(\$path), true, 512, JSON_THROW_ON_ERROR);
\$data['name'] = '${vendor}/theme-${theme}';
\$data['autoload']['psr-4'] = [
    '${provider_namespace}\\\\' => 'src/'
];
\$data['extra']['laravel']['providers'] = ['${provider_full}'];
file_put_contents(\$path, json_encode(\$data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);
"

if [[ -f "${dest}/src/TailwindThemeServiceProvider.php" ]]; then
    mv "${dest}/src/TailwindThemeServiceProvider.php" "${dest}/src/${provider_class}.php"
fi

if [[ -f "${dest}/src/${provider_class}.php" ]]; then
    tmp_file=$(mktemp)
    sed -E \
        -e "s/^namespace[[:space:]]+TentaPress\\\\\\\\Themes\\\\\\\\Tailwind;/namespace ${provider_namespace};/" \
        -e "s/^final[[:space:]]+class[[:space:]]+TailwindThemeServiceProvider/final class ${provider_class}/" \
        -e "s/^class[[:space:]]+TailwindThemeServiceProvider/class ${provider_class}/" \
        -e "s#themes/tentapress/tailwind/hot#themes/${vendor}/${theme}/hot#g" \
        "${dest}/src/${provider_class}.php" > "${tmp_file}"
    mv "${tmp_file}" "${dest}/src/${provider_class}.php"
fi

if [[ -f "${dest}/vite.config.js" ]]; then
    tmp_file=$(mktemp)
    sed -e "s#themes/tentapress/tailwind/build#themes/${vendor}/${theme}/build#g" \
        -e "s#public/themes/tentapress/tailwind/build#public/themes/${vendor}/${theme}/build#g" \
        -e "s#public/themes/tentapress/tailwind/hot#public/themes/${vendor}/${theme}/hot#g" \
        "${dest}/vite.config.js" > "${tmp_file}"
    mv "${tmp_file}" "${dest}/vite.config.js"
fi

if [[ -d "${dest}/views" ]]; then
    while IFS= read -r -d '' file; do
        tmp_file=$(mktemp)
        sed -e "s#themes/tentapress/tailwind/build#themes/${vendor}/${theme}/build#g" "$file" > "$tmp_file"
        mv "$tmp_file" "$file"
    done < <(find "${dest}/views" -type f -name '*.blade.php' -print0)
fi

if [[ -f "${dest}/README.md" ]]; then
    tmp_file=$(mktemp)
    sed -e "s#tentapress/tailwind#${vendor}/${theme}#g" "${dest}/README.md" > "${tmp_file}"
    mv "${tmp_file}" "${dest}/README.md"

    tmp_file=$(mktemp)
    awk \
        -v id="${vendor}/${theme}" \
        -v name="${display_name}" \
        -v version="${data_version}" \
        -v title="${display_name}" \
        'BEGIN { has_name = 0 }
         NR==1 && $0 ~ /^# / { print "# " title; next }
         /^\|[[:space:]]*Name[[:space:]]*\|/ { has_name = 1 }
         {
           if ($0 ~ /^\|[[:space:]]*ID[[:space:]]*\|/) {
             print "| ID       | `" id "` |"
             if (has_name == 0) {
               print "| Name    | " name " |"
             }
             next
           }
           if ($0 ~ /^\|[[:space:]]*Name[[:space:]]*\|/) {
             print "| Name    | " name " |"
             next
           }
           if (version != "" && $0 ~ /^\|[[:space:]]*Version[[:space:]]*\|/) {
             print "| Version | " version " |"
             next
           }
           print
         }' "${dest}/README.md" > "${tmp_file}"
    mv "${tmp_file}" "${dest}/README.md"
fi

echo "Theme cloned to ${dest}"
