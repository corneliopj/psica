#!/bin/bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

find_php_bin() {
  local command_candidates=(
    "php"
    "php83"
    "php8.3"
    "php82"
    "php8.2"
    "php81"
    "php8.1"
  )

  local candidates=(
    "${PHP_BIN:-}"
    "/opt/plesk/php/8.3/bin/php"
    "/opt/plesk/php/8.4/bin/php"
    "/opt/plesk/php/8.2/bin/php"
    "/opt/plesk/php/8.1/bin/php"
    "/usr/bin/php"
    "/usr/local/bin/php"
  )

  for bin in "${candidates[@]}"; do
    if [[ -n "${bin}" && -x "${bin}" ]]; then
      echo "${bin}"
      return 0
    fi
  done

  for cmd in "${command_candidates[@]}"; do
    if command -v "${cmd}" >/dev/null 2>&1; then
      command -v "${cmd}"
      return 0
    fi
  done

  return 1
}

PHP_EXEC="$(find_php_bin || true)"

if [[ -z "${PHP_EXEC}" ]]; then
  echo "ERRO: Nenhum binario PHP encontrado."
  echo "Defina PHP_BIN no task ou use caminho absoluto."
  echo "Dica: rode 'ls -1 /opt/plesk/php/*/bin/php /usr/bin/php /usr/local/bin/php 2>/dev/null'"
  exit 127
fi

cd "${PROJECT_DIR}"

echo "Usando PHP: ${PHP_EXEC}"
"${PHP_EXEC}" artisan deploy:finalize "$@"
