<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 font-weight-bold mb-0">
                {{ __('Manual ID Template Generator') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-4">
            <div class="row">
                <!-- Form Inputs -->
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0">ID Details</h5>
                        </div>
                        <div class="card-body">
                            <form id="idGeneratorForm" onsubmit="event.preventDefault();">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="pills-front-tab" data-bs-toggle="pill" data-bs-target="#pills-front" type="button" role="tab">Front Side</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="pills-back-tab" data-bs-toggle="pill" data-bs-target="#pills-back" type="button" role="tab">Back Side</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    <!-- FRONT TAB -->
                                    <div class="tab-pane fade show active" id="pills-front" role="tabpanel">
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">First Name</label>
                                                <input type="text" id="firstname" class="form-control" placeholder="e.g. Juan" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Middle Name</label>
                                                <input type="text" id="middlename" class="form-control" placeholder="e.g. Santos">
                                            </div>
                                            <div class="col-md-8">
                                                <label class="form-label small fw-bold">Last Name</label>
                                                <input type="text" id="lastname" class="form-control" placeholder="e.g. Dela Cruz" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">Suffix</label>
                                                <input type="text" id="suffix" class="form-control" placeholder="e.g. Jr.">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Employee ID</label>
                                            <input type="text" id="employee_id" class="form-control" placeholder="e.g. 1024" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Position</label>
                                            <input type="text" id="position" class="form-control" list="positionList" placeholder="Select or type position" required>
                                            <datalist id="positionList">
                                                @foreach($positions as $pos)
                                                    <option value="{{ $pos->name }}"></option>
                                                @endforeach
                                            </datalist>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Photo Upload</label>
                                            <input type="file" id="photo" class="form-control" accept="image/*">
                                            <div class="form-text">Choose an image file. It will be processed locally in memory.</div>
                                        </div>
                                    </div>

                                    <!-- BACK TAB -->
                                    <div class="tab-pane fade" id="pills-back" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">TIN</label>
                                            <input type="text" id="tin" class="form-control" placeholder="000-000-000-000">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">SSS</label>
                                            <input type="text" id="sss" class="form-control" placeholder="00-0000000-0">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Pag-IBIG</label>
                                            <input type="text" id="pag_ibig" class="form-control" placeholder="0000-0000-0000">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Expiry Year</label>
                                            <input type="number" id="expiry_year" class="form-control" value="{{ date('Y') + 2 }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="button" id="btnGenerate" class="btn btn-primary">
                                        <i class="bi bi-gear-fill me-1"></i> Generate ID Card (Base64)
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview and Base64 Output -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Preview & Output</h5>
                            <div>
                                <button type="button" id="btnDownloadFront" class="btn btn-sm btn-outline-primary me-2" disabled>
                                    <i class="bi bi-download"></i> Download Front
                                </button>
                                <button type="button" id="btnDownloadBack" class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-download"></i> Download Back
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Canvases (Used for drawing, styled to scale) -->
                            <div class="row text-center mb-4">
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted fw-bold mb-2">Front Side Preview</h6>
                                    <div class="border rounded p-2 bg-light d-flex justify-content-center align-items-center" style="min-height: 480px;">
                                        <canvas id="frontCanvas" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; display: none;"></canvas>
                                        <img id="frontImgPreview" alt="Front Preview" style="max-width: 100%; height: auto; border-radius: 8px; display: none;" />
                                        <span id="frontPlaceholder" class="text-muted small">Fill form and click "Generate ID Card"</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-muted fw-bold mb-2">Back Side Preview</h6>
                                    <div class="border rounded p-2 bg-light d-flex justify-content-center align-items-center" style="min-height: 480px;">
                                        <canvas id="backCanvas" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 8px; display: none;"></canvas>
                                        <img id="backImgPreview" alt="Back Preview" style="max-width: 100%; height: auto; border-radius: 8px; display: none;" />
                                        <span id="backPlaceholder" class="text-muted small">Fill form and click "Generate ID Card"</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Base64 Output Text -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Base64 Output (Front)</label>
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="copyBase64('frontBase64')">Copy Front Base64</button>
                                </div>
                                <textarea id="frontBase64" class="form-control font-monospace small" rows="3" readonly placeholder="Base64 data URI will appear here..."></textarea>
                            </div>

                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold mb-0">Base64 Output (Back)</label>
                                    <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="copyBase64('backBase64')">Copy Back Base64</button>
                                </div>
                                <textarea id="backBase64" class="form-control font-monospace small" rows="3" readonly placeholder="Base64 data URI will appear here..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function uppercaseFirstLetter(val) {
            return String(val || '').charAt(0).toUpperCase() + String(val || '').slice(1);
        }

        function middlenameToInitial(val) {
            if (!val) return '';
            return String(val).charAt(0).toUpperCase() + '.';
        }

        function copyBase64(elementId) {
            const textarea = document.getElementById(elementId);
            if (!textarea.value) return;
            textarea.select();
            navigator.clipboard.writeText(textarea.value);
            alert('Copied to clipboard!');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const btnGenerate = document.getElementById('btnGenerate');
            const frontCanvas = document.getElementById('frontCanvas');
            const backCanvas = document.getElementById('backCanvas');
            const frontImgPreview = document.getElementById('frontImgPreview');
            const backImgPreview = document.getElementById('backImgPreview');
            const frontPlaceholder = document.getElementById('frontPlaceholder');
            const backPlaceholder = document.getElementById('backPlaceholder');
            const frontBase64 = document.getElementById('frontBase64');
            const backBase64 = document.getElementById('backBase64');
            const btnDownloadFront = document.getElementById('btnDownloadFront');
            const btnDownloadBack = document.getElementById('btnDownloadBack');

            // Setup canvas dimensions
            frontCanvas.width = 1276 * 0.55;
            frontCanvas.height = 2032 * 0.55;
            backCanvas.width = 1276 * 0.55;
            backCanvas.height = 2032 * 0.55;

            let uploadedPhotoDataUrl = null;

            document.getElementById('photo').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        uploadedPhotoDataUrl = evt.target.result;
                    };
                    reader.readAsDataURL(file);
                } else {
                    uploadedPhotoDataUrl = null;
                }
            });

            btnGenerate.addEventListener('click', async () => {
                const firstname = document.getElementById('firstname').value.trim();
                const middlename = document.getElementById('middlename').value.trim();
                const lastname = document.getElementById('lastname').value.trim();
                const suffix = document.getElementById('suffix').value.trim();
                const employeeId = document.getElementById('employee_id').value.trim();
                const position = document.getElementById('position').value.trim();

                let fullName = [
                    uppercaseFirstLetter(firstname),
                    middlenameToInitial(middlename),
                    uppercaseFirstLetter(lastname),
                    uppercaseFirstLetter(suffix)
                ].filter(Boolean).join(' ').trim();

                // Generate Front
                await renderFront({
                    name: fullName,
                    position: position,
                    employeeId: employeeId,
                    photoDataUrl: uploadedPhotoDataUrl
                });

                // Generate Back
                await renderBack({
                    tin: document.getElementById('tin').value.trim() || 'n/a',
                    sss: document.getElementById('sss').value.trim() || 'n/a',
                    pagIbig: document.getElementById('pag_ibig').value.trim() || 'n/a',
                    expiryYear: document.getElementById('expiry_year').value.trim() || (new Date().getFullYear() + 2)
                });
            });

            function loadImage(src) {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => resolve(img);
                    img.onerror = (e) => reject(e);
                    img.src = src;
                });
            }

            async function renderFront(data) {
                const ctx = frontCanvas.getContext('2d');
                ctx.clearRect(0, 0, frontCanvas.width, frontCanvas.height);

                try {
                    // Draw Template Front Background
                    const templateImg = await loadImage('/employee/id_template/front');
                    let pheight = frontCanvas.width * (templateImg.height / templateImg.width);
                    ctx.drawImage(templateImg, 0, 0, frontCanvas.width, pheight);

                    // Name
                    ctx.fillStyle = 'blue';
                    ctx.font = '50px Arial';
                    let nameMetrics = ctx.measureText(data.name);
                    let nameX = (frontCanvas.width / 2) - (nameMetrics.width / 2);
                    ctx.fillText(data.name, nameX, 500);

                    // Position
                    ctx.fillStyle = 'grey';
                    ctx.font = '30px Arial';
                    let posMetrics = ctx.measureText(data.position);
                    let posX = (frontCanvas.width / 2) - (posMetrics.width / 2);
                    ctx.fillText(data.position, posX, 535);

                    // Draw Photo if available
                    if (data.photoDataUrl) {
                        try {
                            const photoImg = await loadImage(data.photoDataUrl);
                            ctx.drawImage(photoImg, 43, 100, photoImg.width, photoImg.height);
                        } catch (err) {
                            console.error('Failed to load user photo', err);
                        }
                    }

                    // Draw QR Code and ID
                    if (data.employeeId) {
                        try {
                            const qrUrl = '/generate-qrcode?d=' + encodeURIComponent(window.location.origin + '/public/employee/' + data.employeeId);
                            const qrImg = await loadImage(qrUrl);
                            let qrX = (frontCanvas.width / 2) - (290 / 2);
                            ctx.drawImage(qrImg, qrX, 583, 290, 290);
                        } catch (err) {
                            console.error('Failed to load QR code', err);
                        }

                        ctx.fillStyle = 'black';
                        ctx.font = '30px Arial';
                        let idText = String(data.employeeId).padStart(4, '0');
                        let idMetrics = ctx.measureText(idText);
                        let idX = (frontCanvas.width / 2) - (idMetrics.width / 2);
                        ctx.fillText(idText, idX, 572);
                    }

                    // Convert to base64
                    const dataUrl = frontCanvas.toDataURL('image/png');
                    frontBase64.value = dataUrl;
                    frontImgPreview.src = dataUrl;
                    frontImgPreview.style.display = 'block';
                    frontPlaceholder.style.display = 'none';

                    btnDownloadFront.disabled = false;
                    btnDownloadFront.onclick = () => {
                        const link = document.createElement('a');
                        link.download = `id_front_${data.employeeId || 'card'}.png`;
                        link.href = dataUrl;
                        link.click();
                    };
                } catch (e) {
                    console.error('Error rendering front ID:', e);
                }
            }

            async function renderBack(data) {
                const ctx = backCanvas.getContext('2d');
                ctx.clearRect(0, 0, backCanvas.width, backCanvas.height);

                try {
                    // Draw Template Back Background
                    const templateImg = await loadImage('/employee/id_template/back');
                    let pheight = backCanvas.width * (templateImg.height / templateImg.width);
                    ctx.drawImage(templateImg, 0, 0, backCanvas.width, pheight);

                    ctx.fillStyle = 'grey';
                    ctx.font = '30px Arial';

                    const tin = 'TIN: ' + data.tin;
                    let tinMetrics = ctx.measureText(tin);
                    let tinX = (backCanvas.width / 2) - (tinMetrics.width / 2);
                    ctx.fillText(tin, tinX, 750);

                    const sss = 'SSS: ' + data.sss;
                    let sssMetrics = ctx.measureText(sss);
                    let sssX = (backCanvas.width / 2) - (sssMetrics.width / 2);
                    ctx.fillText(sss, sssX, 800);

                    const pagIbig = 'Pag-IBIG: ' + data.pagIbig;
                    let pagIbigMetrics = ctx.measureText(pagIbig);
                    let pagIbigX = (backCanvas.width / 2) - (pagIbigMetrics.width / 2);
                    ctx.fillText(pagIbig, pagIbigX, 850);

                    ctx.fillStyle = 'black';
                    ctx.font = '30px Arial';
                    const expiry = 'EXP: ' + data.expiryYear;
                    let expiryMetrics = ctx.measureText(expiry);
                    let expiryX = (backCanvas.width / 2) - (expiryMetrics.width / 2);
                    ctx.fillText(expiry, expiryX, 920);

                    // Convert to base64
                    const dataUrl = backCanvas.toDataURL('image/png');
                    backBase64.value = dataUrl;
                    backImgPreview.src = dataUrl;
                    backImgPreview.style.display = 'block';
                    backPlaceholder.style.display = 'none';

                    btnDownloadBack.disabled = false;
                    btnDownloadBack.onclick = () => {
                        const link = document.createElement('a');
                        link.download = `id_back.png`;
                        link.href = dataUrl;
                        link.click();
                    };
                } catch (e) {
                    console.error('Error rendering back ID:', e);
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
