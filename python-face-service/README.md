# Face verification (Python)

Local HTTP service used by Laravel to compare a live capture to the student profile photo. 

```bash
cd python-face-service
python -m venv .venv
# Windows: .venv\Scripts\activate
# Unix:    source .venv/bin/activate
pip install -r requirements.txt
set FACE_VERIFY_SERVICE_SECRET=your-secret   # Windows; use export on Unix
python -m uvicorn main:app --host 127.0.0.1 --port 8765
```
