"""
Local face identity verification API for Laravel attendance.

Compares two face images (reference = enrollment photo, probe = live capture)
using DeepFace embeddings. Open-source; runs on your own machine/server.

Security: bind to 127.0.0.1 in production and set FACE_VERIFY_SERVICE_SECRET.
"""

from __future__ import annotations

import io
import os

import cv2
import numpy as np
from deepface import DeepFace
from fastapi import FastAPI, File, HTTPException, Request, UploadFile
from fastapi.responses import JSONResponse
from PIL import Image

app = FastAPI(title="Face verify", version="1.0.0")

API_SECRET = os.environ.get("FACE_VERIFY_SERVICE_SECRET", "").strip()
MODEL_NAME = os.environ.get("DEEPFACE_MODEL", "Facenet512")
DETECTOR_BACKEND = os.environ.get("DEEPFACE_DETECTOR", "opencv")


def _bytes_to_bgr(data: bytes) -> np.ndarray:
    """Decode JPEG/PNG/WebP/etc. to BGR for OpenCV / DeepFace."""
    try:
        pil = Image.open(io.BytesIO(data)).convert("RGB")
        rgb = np.array(pil)
        return cv2.cvtColor(rgb, cv2.COLOR_RGB2BGR)
    except Exception as exc:  # noqa: BLE001
        raise ValueError(f"Could not decode image: {exc}") from exc


@app.middleware("http")
async def check_secret(request: Request, call_next):
    if not API_SECRET:
        return await call_next(request)
    if request.url.path in ("/health", "/docs", "/openapi.json", "/redoc"):
        return await call_next(request)
    sent = request.headers.get("x-face-verify-secret", "")
    if sent != API_SECRET:
        return JSONResponse({"detail": "Unauthorized"}, status_code=401)
    return await call_next(request)


@app.get("/health")
def health():
    return {"ok": True, "model": MODEL_NAME}


@app.post("/verify")
async def verify(
    reference: UploadFile = File(..., description="Enrollment / profile photo"),
    probe: UploadFile = File(..., description="Live capture from attendance"),
):
    ref_bytes = await reference.read()
    prob_bytes = await probe.read()
    if len(ref_bytes) < 256 or len(prob_bytes) < 256:
        raise HTTPException(400, "Image too small or empty")

    try:
        ref_img = _bytes_to_bgr(ref_bytes)
        prob_img = _bytes_to_bgr(prob_bytes)
    except ValueError as e:
        raise HTTPException(400, str(e)) from e

    try:
        result = DeepFace.verify(
            img1_path=ref_img,
            img2_path=prob_img,
            model_name=MODEL_NAME,
            detector_backend=DETECTOR_BACKEND,
            enforce_detection=True,
            align=True,
        )
    except Exception as e:  # noqa: BLE001 — DeepFace raises many types for "no face"
        detail = str(e).strip() or "Face verification failed"
        raise HTTPException(
            422,
            detail=detail,
        ) from e

    verified = bool(result.get("verified", False))
    distance = float(result.get("distance", 1.0))
    threshold = float(result.get("threshold", 0.0))

    return {
        "verified": verified,
        "distance": distance,
        "threshold": threshold,
        "model": MODEL_NAME,
    }


