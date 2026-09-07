#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

find_php_bin() {
  local candidates=(
    "${PHP_BIN:-}"
    "/opt/plesk/php/8.4/bin/php"
    "/opt/plesk/php/8.3/bin/php"
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

  if command -v php >/dev/null 2>&1; then
    command -v php
    return 0
  fi

  return 1
}

PHP_EXEC="$(find_php_bin || true)"

if [[ -z "${PHP_EXEC}" ]]; then
  echo "ERRO: Nenhum binario PHP encontrado."
  echo "Defina PHP_BIN no task ou use caminho absoluto, ex: /opt/plesk/php/8.3/bin/php"
  exit 127
fi

cd "${PROJECT_DIR}"

"${PHP_EXEC}" artisan deploy:finalize "$@"
