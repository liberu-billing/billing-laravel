#!/bin/bash
# Deploy liberu-billing to Kubernetes.
# Usage: ./k8s/deploy.sh [development|production] [--dry-run]

set -e

OVERLAY="${1:-production}"
DRY_RUN="${2:-}"
K8S_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -d "${K8S_DIR}/overlays/${OVERLAY}" ]]; then
    echo "Unknown overlay: ${OVERLAY}. Use 'development' or 'production'." >&2
    exit 1
fi

KUBECTL_ARGS=""
if [[ "${DRY_RUN}" == "--dry-run" ]]; then
    KUBECTL_ARGS="--dry-run=client"
    echo "DRY RUN mode — no changes will be applied"
fi

echo "Deploying to overlay: ${OVERLAY}"
kubectl apply -k "${K8S_DIR}/overlays/${OVERLAY}" ${KUBECTL_ARGS}

if [[ -z "${DRY_RUN}" ]]; then
    echo "Waiting for rollout..."
    kubectl rollout status deployment/liberu-billing-app \
        -n "$(kubectl kustomize "${K8S_DIR}/overlays/${OVERLAY}" | grep 'namespace:' | head -1 | awk '{print $2}')" \
        --timeout=5m
    echo "Deployment complete."
fi
