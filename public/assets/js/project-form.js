(function () {
    function initSelect2() {
        if (!window.jQuery || !jQuery.fn.select2) {
            return;
        }

        jQuery('.searchable-select').each(function () {
            var select = jQuery(this);

            select.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: select.data('placeholder') || 'Pilih data',
                allowClear: true
            });
        });
    }

    function replacePengirimOptions(options, selectedValue) {
        var select = document.getElementById('pengirim');

        if (!select) {
            return;
        }

        select.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih NPPBKC - Perusahaan';
        select.appendChild(placeholder);

        options.forEach(function (option) {
            var element = document.createElement('option');
            element.value = option;
            element.textContent = option;

            if (option === selectedValue) {
                element.selected = true;
            }

            select.appendChild(element);
        });

        if (window.jQuery && jQuery.fn.select2) {
            jQuery(select).trigger('change.select2');
        }
    }

    function readJsonFromBase64Element(id) {
        var element = document.getElementById(id);

        if (!element || !element.textContent) {
            return [];
        }

        try {
            return JSON.parse(window.atob(element.textContent));
        } catch (error) {
            return [];
        }
    }

    function readStringFromBase64Element(id) {
        var element = document.getElementById(id);

        if (!element || !element.textContent) {
            return '';
        }

        try {
            return window.atob(element.textContent);
        } catch (error) {
            return '';
        }
    }

    function initPengirimToggle() {
        var button = document.getElementById('toggle-pengirim-mode');
        var label = document.getElementById('pengirim-mode-label');
        var emptyLabel = document.getElementById('pengirim-empty-label');
        var select = document.getElementById('pengirim');
        var checkbox = document.getElementById('tanpa_pabrik_tujuan');
        var pdfInput = document.getElementById('surat_permohonan_pdf');
        var pdfButton = document.getElementById('extract-surat-permohonan');
        var disabledLabel = document.getElementById('surat-permohonan-disabled-label');
        var pdfPreview = document.getElementById('surat-permohonan-preview');
        var pdfPreviewEmpty = document.getElementById('surat-permohonan-preview-empty');
        var labelProjectGroup = document.getElementById('label-project-tanpa-pabrik-group');
        var labelProjectInput = document.getElementById('label_project_tanpa_pabrik');
        var initialPdfPreviewSrc = pdfPreview ? (pdfPreview.getAttribute('src') || '') : '';
        var aktifOptions = readJsonFromBase64Element('project-pengirim-aktif-options');
        var semuaOptions = readJsonFromBase64Element('project-pengirim-semua-options');
        var selectedValue = readStringFromBase64Element('project-selected-pengirim');
        var suratFieldIds = ['no_surat_permohonan', 'tgl_surat_permohonan', 'hal_surat_permohonan'];

        if (!button || !select) {
            return;
        }

        function updateTanpaPabrikState() {
            var withoutFactory = checkbox && checkbox.checked;
            var availableOptions = button.dataset.mode === 'semua' ? semuaOptions : aktifOptions;

            select.disabled = !!withoutFactory;
            button.disabled = !!withoutFactory;

            if (withoutFactory) {
                selectedValue = select.value || selectedValue;
                select.value = '';

                suratFieldIds.forEach(function (id) {
                    var field = document.getElementById(id);

                    if (!field) {
                        return;
                    }

                    field.value = '';
                    field.disabled = true;
                });

                if (pdfInput) {
                    pdfInput.value = '';
                    pdfInput.disabled = true;
                }

                if (pdfButton) {
                    pdfButton.disabled = true;
                }

                if (disabledLabel) {
                    disabledLabel.classList.remove('d-none');
                }

                if (labelProjectGroup) {
                    labelProjectGroup.classList.remove('d-none');
                }

                if (labelProjectInput) {
                    labelProjectInput.disabled = false;
                }

                setExtractStatus('', 'text-muted');

                if (window.jQuery && jQuery.fn.select2) {
                    jQuery(select).val(null).trigger('change');
                    jQuery(select).next('.select2-container').addClass('select2-container--disabled');
                }

                if (pdfPreview) {
                    pdfPreview.classList.add('d-none');
                    pdfPreview.setAttribute('src', '');
                }

                if (pdfPreviewEmpty) {
                    pdfPreviewEmpty.classList.remove('d-none');
                }

                if (label) {
                    label.classList.add('d-none');
                }

                if (emptyLabel) {
                    emptyLabel.classList.remove('d-none');
                }

                return;
            }

            replacePengirimOptions(availableOptions, selectedValue);

            suratFieldIds.forEach(function (id) {
                var field = document.getElementById(id);

                if (field) {
                    field.disabled = false;
                }
            });

            if (pdfInput) {
                pdfInput.disabled = false;
            }

            if (pdfButton && pdfInput) {
                pdfButton.disabled = !pdfInput.files.length;
            }

            if (disabledLabel) {
                disabledLabel.classList.add('d-none');
            }

            if (labelProjectGroup) {
                labelProjectGroup.classList.add('d-none');
            }

            if (labelProjectInput) {
                labelProjectInput.disabled = true;
            }

            if (pdfPreview && initialPdfPreviewSrc && !pdfInput.files.length) {
                pdfPreview.setAttribute('src', initialPdfPreviewSrc);
                pdfPreview.classList.remove('d-none');
            }

            if (pdfPreviewEmpty && initialPdfPreviewSrc && !pdfInput.files.length) {
                pdfPreviewEmpty.classList.add('d-none');
            }

            if (window.jQuery && jQuery.fn.select2) {
                jQuery(select).next('.select2-container').removeClass('select2-container--disabled');
            }

            if (label) {
                label.classList.remove('d-none');
            }

            if (emptyLabel) {
                emptyLabel.classList.add('d-none');
            }
        }

        if (selectedValue && aktifOptions.indexOf(selectedValue) === -1) {
            button.dataset.mode = 'semua';
            button.textContent = 'Tampilkan perusahaan aktif saja';

            if (label) {
                label.textContent = 'Menampilkan semua perusahaan.';
            }

            replacePengirimOptions(semuaOptions, selectedValue);
        }

        select.addEventListener('change', function () {
            if (select.value) {
                selectedValue = select.value;
            }
        });

        button.addEventListener('click', function () {
            var showAll = button.dataset.mode === 'aktif';
            selectedValue = select.value || selectedValue;

            if (showAll) {
                button.dataset.mode = 'semua';
                button.textContent = 'Tampilkan perusahaan aktif saja';

                if (label) {
                    label.textContent = 'Menampilkan semua perusahaan.';
                }

                replacePengirimOptions(semuaOptions, selectedValue);
                return;
            }

            button.dataset.mode = 'aktif';
            button.textContent = 'Tampilkan semua perusahaan';

            if (label) {
                label.textContent = 'Menampilkan perusahaan aktif.';
            }

            if (aktifOptions.indexOf(selectedValue) === -1) {
                selectedValue = '';
            }

            replacePengirimOptions(aktifOptions, selectedValue);
        });

        if (checkbox) {
            checkbox.addEventListener('change', updateTanpaPabrikState);
        }

        updateTanpaPabrikState();
    }

    function setExtractStatus(message, className) {
        var status = document.getElementById('surat-permohonan-extract-status');

        if (!status) {
            return;
        }

        status.className = 'form-text ' + (className || 'text-muted');
        status.textContent = message || '';
    }

    function fillIfPresent(id, value) {
        var input = document.getElementById(id);

        if (!input || !value) {
            return;
        }

        input.value = value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function initSuratPermohonanPreview(input) {
        var frame = document.getElementById('surat-permohonan-preview');
        var empty = document.getElementById('surat-permohonan-preview-empty');
        var previewUrl = '';

        if (!input || !frame || !empty) {
            return;
        }

        input.addEventListener('change', function () {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = '';
            }

            if (!input.files.length) {
                if (!frame.getAttribute('src')) {
                    frame.classList.add('d-none');
                    empty.classList.remove('d-none');
                }

                return;
            }

            previewUrl = URL.createObjectURL(input.files[0]);
            frame.setAttribute('src', previewUrl);
            frame.classList.remove('d-none');
            empty.classList.add('d-none');
        });
    }

    function initSuratPermohonanExtract() {
        var input = document.getElementById('surat_permohonan_pdf');
        var button = document.getElementById('extract-surat-permohonan');
        var url = readStringFromBase64Element('project-extract-url');
        var csrf = document.querySelector('meta[name="csrf-token"]');

        initSuratPermohonanPreview(input);

        if (!input || !button || !url || !csrf) {
            return;
        }

        input.addEventListener('change', function () {
            button.disabled = !input.files.length;
            setExtractStatus('', 'text-muted');
        });

        button.addEventListener('click', function () {
            if (!input.files.length) {
                setExtractStatus('Pilih PDF surat permohonan terlebih dahulu.', 'text-danger');
                return;
            }

            input.dispatchEvent(new Event('change', { bubbles: true }));

            var formData = new FormData();
            formData.append('surat_permohonan_pdf', input.files[0]);

            var controller = new AbortController();
            var timeout = window.setTimeout(function () {
                controller.abort();
            }, 20000);

            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin fa-sm mr-1"></i>Extracting';
            setExtractStatus('Sedang membaca PDF, maksimal 20 detik...', 'text-muted');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf.getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData,
                signal: controller.signal
            })
                .then(function (response) {
                    return response.json().then(function (json) {
                        if (!response.ok) {
                            throw json;
                        }

                        return json;
                    });
                })
                .then(function (json) {
                    var fields = json.fields || {};

                    fillIfPresent('no_surat_permohonan', fields.no_surat_permohonan);
                    fillIfPresent('tgl_surat_permohonan', fields.tgl_surat_permohonan);
                    fillIfPresent('hal_surat_permohonan', fields.hal_surat_permohonan);

                    if (fields.no_surat_permohonan || fields.tgl_surat_permohonan || fields.hal_surat_permohonan) {
                        setExtractStatus('Extract selesai' + (json.engine ? ' via ' + json.engine : '') + '. Silakan cek lagi sebelum disimpan.', 'text-success');
                        return;
                    }

                    if (!json.engine) {
                        setExtractStatus('Belum ada extractor PDF ringan yang tersedia. Isi manual dulu ya.', 'text-danger');
                        return;
                    }

                    if (!json.has_text) {
                        setExtractStatus('PDF berhasil diproses via ' + json.engine + ', tapi tidak ada teks terbaca. Kemungkinan PDF scan, isi manual dulu ya.', 'text-warning');
                        return;
                    }

                    setExtractStatus('PDF terbaca via ' + json.engine + ', tapi nomor/tanggal/hal belum ditemukan. Pola suratnya mungkin beda, isi manual dulu ya.', 'text-warning');
                })
                .catch(function (error) {
                    if (error && error.name === 'AbortError') {
                        setExtractStatus('Extract dihentikan karena lebih dari 20 detik. Isi manual dulu ya.', 'text-warning');
                        return;
                    }

                    setExtractStatus('Extract gagal. Isi data manual atau coba PDF lain.', 'text-danger');
                })
                .finally(function () {
                    window.clearTimeout(timeout);
                    button.disabled = !input.files.length;
                    button.innerHTML = button.dataset.originalText || 'Extract Data';
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSelect2();
        initPengirimToggle();
        initSuratPermohonanExtract();
    });
})();
