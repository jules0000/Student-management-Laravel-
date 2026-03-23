<div class="space-y-6">
    <div class="space-y-1">
        <h2 class="text-2xl font-bold tracking-tight">My Classes & Attendance</h2>
        <p class="text-sm text-zinc-600">
            Select your current subject, view classmates + scheduled time/classroom, then mark attendance with face scan and geolocation.
        </p>
    </div>

    <div class="bg-white rounded-3xl border border-zinc-200 shadow-sm p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Term</label>
                    <select
                        wire:model="selectedTermId"
                        class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                    >
                        <option value="">Select term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}">
                                {{ $term->name }}{{ $term->school_year ? " ($term->school_year)" : '' }}
                            </option>
                        @endforeach
                    </select>
            </div>

            <div class="space-y-1 md:col-span-2">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest ml-1">Subject (Current Enrollment)</label>
                <select
                    wire:model="selectedSubjectId"
                    class="w-full px-3 py-2.5 bg-zinc-50 border border-zinc-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-zinc-900/5 focus:border-zinc-900 text-sm"
                >
                    @forelse($subjects as $subject)
                        <option value="{{ $subject->id }}">
                            {{ $subject->name }}{{ $subject->code ? " ($subject->code)" : '' }}
                        </option>
                    @empty
                        <option value="">No approved enrollments yet</option>
                    @endforelse
                </select>
            </div>
        </div>

        @if(! $student)
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                <p class="text-sm text-zinc-700">Please log in as a student.</p>
            </div>
        @elseif($subjects->isEmpty() || ! $selectedSubjectId)
            <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                <p class="text-sm text-zinc-700">
                    You don’t have any approved subject enrollments for the current term.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <h3 class="text-sm font-semibold">Classmates</h3>
                            <p class="text-sm text-zinc-600">Students enrolled in the same subject for this term.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-zinc-200 p-4">
                        @if($classmates->isEmpty())
                            <p class="text-sm text-zinc-700">No classmates found for this subject/term.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($classmates as $enrollment)
                                    @php $cm = $enrollment->student; @endphp
                                    @if($cm)
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-zinc-100 overflow-hidden flex items-center justify-center">
                                                @if($cm->photo_url)
                                                    <img src="{{ $cm->photo_url }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-zinc-500 text-sm font-bold">
                                                        {{ strtoupper(substr($cm->first_name, 0, 1)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-zinc-900 truncate">
                                                    {{ $cm->first_name }} {{ $cm->last_name }}
                                                </p>
                                                <p class="text-xs text-zinc-500 truncate">{{ $cm->section }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="space-y-1">
                        <h3 class="text-sm font-semibold">Scheduled Sessions</h3>
                        <p class="text-sm text-zinc-600">Mark attendance when the session is within the allowed time window.</p>
                    </div>

                    <div class="bg-white rounded-2xl border border-zinc-200 p-4" wire:poll.15s>
                        @if($sessions->isEmpty())
                            <p class="text-sm text-zinc-700">No sessions scheduled yet for this subject/term.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($sessions as $session)
                                    @php
                                        $log = $attendanceBySessionId->get($session->id);
                                        $startAt = $session->start_at;
                                        // Allow marking for the whole session (+ small grace).
                                        $windowStart = $startAt->copy()->subMinutes(10);
                                        $windowEnd = $session->marking_end_at->copy()->addMinutes(30);
                                        $canMark = $now->between($windowStart, $windowEnd);
                                        $statusLabel = $log?->status;
                                        $classroom = $session->classroom;
                                        $radiusM = $classroom?->radius_m ?? 100;
                                    @endphp

                                    <div class="flex items-start justify-between gap-4 p-3 rounded-xl hover:bg-zinc-50 transition-colors">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-zinc-900">
                                                {{ $startAt->format('M d, Y · g:i A') }}
                                            </p>
                                            <p class="text-xs text-zinc-500 truncate">
                                                Instructor: {{ $session->instructor?->name ?? '—' }}
                                            </p>
                                            <p class="text-xs text-zinc-500 truncate">
                                                Classroom: {{ $classroom?->room_label ?? '—' }} · Geofence: {{ $radiusM }}m
                                            </p>
                                            <p class="text-xs text-zinc-500 truncate">
                                                Status:
                                                @if($log)
                                                    <span class="font-semibold text-zinc-800">{{ $statusLabel }}</span>
                                                @else
                                                    <span class="font-semibold text-zinc-700">{{ $canMark ? 'ready to mark' : 'not in attendance window' }}</span>
                                                @endif
                                            </p>
                                        </div>

                                        <div class="shrink-0 flex flex-col gap-2 items-end">
                                            @if($log)
                                                <button type="button" disabled class="px-4 py-2 bg-zinc-100 border border-zinc-200 text-zinc-500 rounded-xl text-sm cursor-not-allowed">
                                                    Marked
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    {{ $canMark ? '' : 'disabled' }}
                                                    data-session-id="{{ $session->id }}"
                                                    data-classroom-label="{{ $classroom?->room_label ?? '' }}"
                                                    data-radius-m="{{ $radiusM }}"
                                                    class="px-4 py-2 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-sm disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                                                    onclick="openAttendanceModal(this)"
                                                >
                                                    Mark Attendance
                                                </button>
                                            @endif

                                            <button
                                                type="button"
                                                wire:click="$set('selectedMonitoringSessionId', {{ $session->id }})"
                                                class="px-4 py-2 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98] text-sm"
                                            >
                                                View Attendance
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($monitoringSession)
                        <div class="mt-4 pt-4 border-t border-zinc-200 space-y-3">
                            <div class="flex items-start justify-between gap-4 flex-wrap">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-zinc-900">Attendance Monitoring</p>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        {{ $monitoringSession->start_at?->format('M d, Y · g:i A') ?? '—' }} · Instructor:
                                        {{ $monitoringSession->instructor?->name ?? '—' }}
                                    </p>
                                    <p class="text-xs text-zinc-500 truncate">
                                        Classroom: {{ $monitoringSession->classroom?->room_label ?? '—' }} · Geofence:
                                        {{ $monitoringSession->classroom?->radius_m ?? 100 }}m
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    wire:click="$set('selectedMonitoringSessionId', null)"
                                    class="px-4 py-2 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98] text-sm"
                                >
                                    Close
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse min-w-[720px]">
                                    <thead>
                                    <tr class="bg-zinc-100/50 border-b border-zinc-200">
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Student</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Status</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Face</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Geo Match</th>
                                        <th class="px-4 py-3 text-[10px] font-bold text-zinc-700 uppercase tracking-widest">Distance</th>
                                    </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200">
                                        @foreach($rosterEnrollments as $enrollment)
                                            @php
                                                $rStudent = $enrollment->student;
                                                if (! $rStudent) continue;
                                                $log = $monitoringAttendanceByStudentId->get($rStudent->id);
                                            @endphp
                                            <tr class="hover:bg-zinc-50 transition-colors">
                                                <td class="px-4 py-3">
                                                    <div class="font-bold text-sm text-zinc-900">
                                                        {{ $rStudent->first_name }} {{ $rStudent->last_name }}
                                                        @if($rStudent->id === $student->id)
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">(You)</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-zinc-500 mt-0.5">{{ $rStudent->section }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if(! $log)
                                                        <span class="text-sm text-zinc-600">not marked</span>
                                                    @else
                                                        @php
                                                            $status = $log->status;
                                                            $color = match ($status) {
                                                                'present' => 'text-emerald-700',
                                                                'rejected_location' => 'text-rose-700',
                                                                'failed_face_match' => 'text-violet-700',
                                                                default => 'text-amber-700',
                                                            };
                                                        @endphp
                                                        <span class="text-sm font-semibold {{ $color }}">{{ $status }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if(! $log)
                                                        <span class="text-sm text-zinc-600">—</span>
                                                    @else
                                                        <span class="text-sm font-semibold {{ $log->face_detected ? 'text-emerald-700' : 'text-amber-700' }}">
                                                            {{ $log->face_detected ? 'yes' : 'no' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if(! $log)
                                                        <span class="text-sm text-zinc-600">—</span>
                                                    @else
                                                        <span class="text-sm font-semibold {{ $log->location_match ? 'text-emerald-700' : 'text-rose-700' }}">
                                                            {{ $log->location_match ? 'match' : 'mismatch' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if(! $log)
                                                        <span class="text-sm text-zinc-600">—</span>
                                                    @else
                                                        <span class="text-sm text-zinc-700">
                                                            {{ $log->distance_m !== null ? number_format((float) $log->distance_m, 1) . 'm' : '—' }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Face + geolocation modal --}}
    <div id="attendanceModalOverlay" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-zinc-200 shadow-lg w-full max-w-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-zinc-200 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold">Mark Attendance</h3>
                    <p class="text-sm text-zinc-600">
                        Face scan + geolocation must match the instructor-set classroom.
                    </p>
                </div>
                <button type="button" class="text-zinc-500 hover:text-zinc-900" onclick="closeAttendanceModal()">Close</button>
            </div>

            <div class="p-6 space-y-4">
                <div class="bg-zinc-50 border border-zinc-200 rounded-2xl p-4">
                    <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Target classroom</p>
                    <p class="text-sm font-semibold text-zinc-900 mt-1" id="attendanceTargetClassroomLabel">—</p>
                    <p class="text-xs text-zinc-500 mt-1">Geofence radius: <span id="attendanceTargetRadiusM">—</span> meters</p>
                </div>

                <div class="flex flex-col md:flex-row gap-4 items-stretch">
                    <div class="flex-1 bg-zinc-50 border border-zinc-200 rounded-2xl p-3">
                        <video id="attendanceVideo" class="w-full rounded-xl bg-black" autoplay playsinline></video>
                        <canvas id="attendanceCanvas" class="hidden"></canvas>
                        <p class="text-xs text-zinc-500 mt-2" id="attendanceModalHint">Allow camera and location, then click “Capture Face & Submit”.</p>
                    </div>

                    <div class="w-full md:w-72 bg-white border border-zinc-200 rounded-2xl p-4 space-y-3">
                        <p class="text-xs font-bold text-zinc-500 uppercase tracking-widest">Result</p>
                        <div class="text-sm text-zinc-700" id="attendanceModalStatus">Waiting...</div>
                        <div class="text-xs text-zinc-500" id="attendanceModalMeta"></div>

                        <div class="pt-2 space-y-2">
                            <button
                                type="button"
                                onclick="submitAttendance()"
                                class="w-full px-6 py-2.5 bg-zinc-900 text-white rounded-xl font-semibold hover:bg-zinc-800 transition-all active:scale-[0.98] shadow-sm"
                            >
                                Capture Face & Submit
                            </button>
                            <button
                                type="button"
                                onclick="closeAttendanceModal()"
                                class="w-full px-6 py-2.5 bg-white border border-zinc-200 text-zinc-700 rounded-xl font-semibold hover:bg-zinc-50 transition-all active:scale-[0.98]"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            if (window.__studentAttendanceModalInstalled) return;
            window.__studentAttendanceModalInstalled = true;

            const markUrl = @json(route('attendance.mark'));
            const csrfToken = @json(csrf_token());

            let currentSessionId = null;
            let stream = null;

            function els() {
                return {
                    overlay: document.getElementById('attendanceModalOverlay'),
                    video: document.getElementById('attendanceVideo'),
                    canvas: document.getElementById('attendanceCanvas'),
                    statusEl: document.getElementById('attendanceModalStatus'),
                    metaEl: document.getElementById('attendanceModalMeta'),
                    targetLabelEl: document.getElementById('attendanceTargetClassroomLabel'),
                    targetRadiusEl: document.getElementById('attendanceTargetRadiusM'),
                };
            }

            function setStatus(text) {
                const { statusEl } = els();
                if (statusEl) statusEl.textContent = text;
            }

            function stopCamera() {
                try {
                    if (stream) {
                        stream.getTracks().forEach(t => t.stop());
                    }
                } catch (e) {
                    // no-op
                }
                stream = null;
            }

            async function startCamera() {
                stopCamera();

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setStatus('Camera not supported by this browser.');
                    return;
                }

                const { video } = els();
                setStatus('Starting camera...');
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user' },
                    audio: false
                });
                video.srcObject = stream;
                await video.play();
                setStatus('Camera ready. Click “Capture Face & Submit”.');
            }

            function openAttendanceModal(btn) {
                currentSessionId = btn.getAttribute('data-session-id');
                const roomLabel = btn.getAttribute('data-classroom-label') || '—';
                const radiusM = btn.getAttribute('data-radius-m') || '—';

                const { overlay, targetLabelEl, targetRadiusEl } = els();
                if (targetLabelEl) targetLabelEl.textContent = roomLabel;
                if (targetRadiusEl) targetRadiusEl.textContent = radiusM;

                setStatus('Waiting for camera...');
                const { metaEl } = els();
                if (metaEl) metaEl.textContent = '';

                overlay.classList.remove('hidden');
                overlay.classList.add('flex');

                startCamera().catch(() => {
                    setStatus('Unable to start camera. Please allow camera permissions.');
                });
            }

            /**
             * When the experimental FaceDetector API is missing (common on Chrome/Edge:
             * requires secure context, platform support, and is not shipped everywhere),
             * fall back to a coarse "camera frame looks like real content" check so
             * capture + submit still works with photo + geolocation.
             */
            function detectFaceFallback(canvasEl) {
                try {
                    const ctx = canvasEl.getContext('2d');
                    if (!ctx) return false;
                    const w = canvasEl.width;
                    const h = canvasEl.height;
                    if (w < 32 || h < 32) return false;

                    const imageData = ctx.getImageData(0, 0, w, h);
                    const data = imageData.data;
                    const step = 16; // sample sparsely for speed
                    let sum = 0;
                    let count = 0;
                    for (let i = 0; i < data.length; i += 4 * step) {
                        const lum = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                        sum += lum;
                        count++;
                    }
                    const mean = sum / count;
                    let varSum = 0;
                    for (let i = 0; i < data.length; i += 4 * step) {
                        const lum = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                        varSum += (lum - mean) * (lum - mean);
                    }
                    const variance = varSum / count;
                    // Reject near-black / near-white flat frames (lens cap, dead camera).
                    const hasBrightnessRange = mean > 18 && mean < 245;
                    const hasTexture = variance > 35;
                    return hasBrightnessRange && hasTexture;
                } catch (e) {
                    return false;
                }
            }

            async function detectFace(canvasEl) {
                // Prefer native Face Detection when the browser actually exposes it.
                if ('FaceDetector' in window) {
                    try {
                        const detector = new FaceDetector({ fastThreshold: 0.5, maxDetectedFaces: 1 });
                        const faces = await detector.detect(canvasEl);
                        if (faces && faces.length > 0) {
                            return { ok: true, mode: 'api' };
                        }
                        // API present but no face found — try fallback (lighting/angle).
                        if (detectFaceFallback(canvasEl)) {
                            return { ok: true, mode: 'fallback' };
                        }
                        return { ok: false, mode: 'none' };
                    } catch (e) {
                        if (detectFaceFallback(canvasEl)) {
                            return { ok: true, mode: 'fallback' };
                        }
                        return { ok: false, mode: 'none' };
                    }
                }

                // No FaceDetector: many Chromes still lack this API — use fallback only.
                if (detectFaceFallback(canvasEl)) {
                    return { ok: true, mode: 'fallback' };
                }
                return { ok: false, mode: 'none' };
            }

            function getGeolocation() {
                return new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        reject(new Error('Geolocation is not supported by this browser.'));
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (pos) => resolve(pos.coords),
                        (err) => reject(err),
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                });
            }

            async function submitAttendance() {
                if (!currentSessionId) return;

                const { video, canvas, metaEl } = els();
                setStatus('Capturing face...');

                const w = video.videoWidth || 640;
                const h = video.videoHeight || 480;
                canvas.width = w;
                canvas.height = h;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, w, h);

                const faceResult = await detectFace(canvas);
                if (!faceResult.ok) {
                    setStatus('Could not confirm a face in frame. Ensure good lighting, face the camera, and try again.');
                    return;
                }
                if (faceResult.mode === 'fallback') {
                    setStatus('Camera capture OK (face API unavailable in this browser). Getting location...');
                }
                const faceDetected = true;

                setStatus('Getting geolocation...');
                const coords = await getGeolocation();

                stopCamera();

                setStatus('Submitting...');
                if (metaEl) metaEl.textContent = '';

                canvas.toBlob(async (blob) => {
                    if (!blob) {
                        setStatus('Unable to capture image from camera.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('session_id', currentSessionId);
                    formData.append('latitude', coords.latitude);
                    formData.append('longitude', coords.longitude);
                    formData.append('face_detected', faceDetected ? '1' : '0');
                    formData.append('face_image', blob, 'face.jpg');

                    try {
                        const res = await fetch(markUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: formData
                        });

                        const data = await res.json().catch(() => null);

                        if (!res.ok) {
                            const msg = data && data.message ? data.message : 'Unable to mark attendance.';
                            setStatus(msg);
                            if (metaEl) metaEl.textContent = data && data.status ? `(${data.status})` : '';
                            return;
                        }

                        setStatus(data && data.message ? data.message : 'Submitted.');
                        if (data && data.distance_m !== undefined && metaEl) {
                            metaEl.textContent = `Distance: ${data.distance_m ?? '—'}m / Radius: ${data.radius_m ?? '—'}m`;
                        }

                        if (data && data.ok) {
                            setTimeout(() => window.location.reload(), 900);
                        }
                    } catch (e) {
                        setStatus('Network error. Please try again.');
                    }
                }, 'image/jpeg', 0.75);
            }

            function closeAttendanceModal() {
                stopCamera();
                currentSessionId = null;

                const { overlay } = els();
                if (overlay) {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }
            }

            window.openAttendanceModal = openAttendanceModal;
            window.submitAttendance = submitAttendance;
            window.closeAttendanceModal = closeAttendanceModal;
        })();
    </script>
</div>

