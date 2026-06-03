#!/bin/bash
# Validate Kubernetes manifests for liberu-billing.
# Usage: ./k8s/validate.sh [development|production]
#
# Requires: kubectl (with kustomize support), kubeconform (optional).

set -e

OVERLAY="${1:-production}"
K8S_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RESET='\033[0m'

pass() { echo -e "${GREEN}✓${RESET} $*"; }
fail() { echo -e "${RED}✗${RESET} $*"; ERRORS=$((ERRORS + 1)); }
warn() { echo -e "${YELLOW}⚠${RESET} $*"; }

ERRORS=0

echo "Validating overlay: ${OVERLAY}"
echo ""

# 1 — kubectl present
if ! command -v kubectl >/dev/null 2>&1; then
    fail "kubectl not found. Install it from https://kubernetes.io/docs/tasks/tools/"
    exit 1
fi
pass "kubectl found: $(kubectl version --client 2>/dev/null | grep 'Client Version' | awk '{print $3}' || echo 'unknown')"

# 2 — overlay directory exists
if [[ ! -d "${K8S_DIR}/overlays/${OVERLAY}" ]]; then
    fail "Overlay '${OVERLAY}' not found at ${K8S_DIR}/overlays/${OVERLAY}"
    echo "Available overlays: $(ls "${K8S_DIR}/overlays/" 2>/dev/null | tr '\n' ' ')"
    exit 1
fi
pass "Overlay directory exists: overlays/${OVERLAY}"

# 3 — base directory exists
if [[ ! -d "${K8S_DIR}/base" ]]; then
    fail "Base directory not found at ${K8S_DIR}/base"
    exit 1
fi
pass "Base directory exists: base/"

# 4 — kustomize build (dry-run render)
echo ""
echo "Running: kubectl kustomize overlays/${OVERLAY}"
if RENDERED=$(kubectl kustomize "${K8S_DIR}/overlays/${OVERLAY}" 2>&1); then
    pass "kustomize build succeeded"
    RESOURCE_COUNT=$(echo "${RENDERED}" | grep -c '^kind:' || true)
    echo "    Rendered ${RESOURCE_COUNT} resource(s)"
else
    fail "kustomize build failed:"
    echo "${RENDERED}"
fi

# 5 — kubectl apply dry-run (requires a reachable cluster; skip if unavailable)
echo ""
echo "Running: kubectl apply --dry-run=client"
if kubectl cluster-info >/dev/null 2>&1; then
    if kubectl apply -k "${K8S_DIR}/overlays/${OVERLAY}" --dry-run=client >/dev/null 2>&1; then
        pass "kubectl apply --dry-run=client succeeded"
    else
        fail "kubectl apply --dry-run=client failed"
        kubectl apply -k "${K8S_DIR}/overlays/${OVERLAY}" --dry-run=client 2>&1 | head -30
    fi
else
    warn "No cluster reachable — skipping live dry-run (kustomize render still validated above)"
fi

# 6 — kubeconform / kubeval (optional schema validation)
echo ""
for tool in kubeconform kubeval; do
    if command -v "${tool}" >/dev/null 2>&1; then
        echo "Running: ${tool}"
        if echo "${RENDERED}" | ${tool} -strict 2>&1; then
            pass "${tool} schema validation passed"
        else
            fail "${tool} schema validation failed"
        fi
        break
    fi
done

# 7 — secrets placeholder check
echo ""
if echo "${RENDERED}" | grep -q 'CHANGE_ME'; then
    warn "Placeholder secrets detected — replace CHANGE_ME values before production deploy"
fi

# Summary
echo ""
if [[ ${ERRORS} -eq 0 ]]; then
    echo -e "${GREEN}All validations passed.${RESET}"
    exit 0
else
    echo -e "${RED}${ERRORS} validation(s) failed.${RESET}"
    exit 1
fi
