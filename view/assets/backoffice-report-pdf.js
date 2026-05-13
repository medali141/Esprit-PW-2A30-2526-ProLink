/**
 * Export PDF depuis un bloc DOM (capture html2canvas + jsPDF, pagination automatique).
 * Dépendances : html2canvas 1.x, jspdf 2.x (window.jspdf.jsPDF).
 */
(function (global) {
    'use strict';

    /**
     * @param {{ buttonId: string, rootId: string, footerLine?: string, fileName?: string, loadingText?: string, scale?: number }} opts
     */
    function bind(opts) {
        if (!opts || !opts.buttonId || !opts.rootId) return;
        var btn = document.getElementById(opts.buttonId);
        var target = document.getElementById(opts.rootId);
        if (!btn || !target) return;
        if (typeof html2canvas === 'undefined' || !global.jspdf || !global.jspdf.jsPDF) return;

        var footer = opts.footerLine || 'ProLink';
        var fileName = opts.fileName || 'prolink-export.pdf';
        var loadingText = opts.loadingText || 'Génération PDF…';
        var scale = opts.scale != null ? opts.scale : 2.35;

        btn.addEventListener('click', function () {
            var oldText = btn.textContent;
            btn.disabled = true;
            btn.textContent = loadingText;

            function capture() {
                try {
                    target.scrollIntoView({ block: 'start', inline: 'nearest' });
                    global.window.scrollTo(0, 0);
                } catch (eIgnore) {}
                return html2canvas(target, {
                    scale: scale,
                    backgroundColor: '#ffffff',
                    logging: false,
                    useCORS: true,
                    scrollX: 0,
                    scrollY: 0,
                    onclone: function (clonedDoc) {
                        var root = clonedDoc.getElementById(opts.rootId);
                        var htmlEl = clonedDoc.documentElement;
                        var bodyEl = clonedDoc.body;
                        htmlEl.classList.remove('dark-mode');
                        bodyEl.classList.remove('dark-mode');
                        htmlEl.classList.add('pl-pdf-snapshot-html');
                        htmlEl.style.colorScheme = 'light';
                        if (bodyEl) {
                            bodyEl.style.backgroundColor = '#ffffff';
                            bodyEl.style.color = '#0f172a';
                        }
                        if (root) {
                            root.classList.add('pl-pdf-snapshot-root');
                            root.style.colorScheme = 'light';
                        }
                    },
                });
            }

            function afterFonts() {
                var p =
                    global.document.fonts && global.document.fonts.ready
                        ? global.document.fonts.ready.catch(function () {})
                        : global.Promise.resolve();
                return p.then(function () {
                    return new Promise(function (resolve) {
                        global.requestAnimationFrame(function () {
                            resolve(capture());
                        });
                    });
                });
            }

            afterFonts()
                .then(function (canvas) {
                    var jsPDF = global.jspdf.jsPDF;
                    var pdf = new jsPDF('p', 'mm', 'a4');
                    var pageWidth = 210;
                    var pageHeight = 297;
                    var marginX = 12;
                    var marginTop = 11;
                    var marginBottom = 14;
                    var innerW = pageWidth - marginX * 2;

                    function drawFooter(pagePdf) {
                        pagePdf.setFont('helvetica', 'normal');
                        pagePdf.setFontSize(8.5);
                        pagePdf.setTextColor(71, 85, 105);
                        pagePdf.text(footer, marginX, pageHeight - 6);
                    }

                    var usableHmm = pageHeight - marginTop - marginBottom;
                    var imgW = innerW;
                    var imgH_mm_total = (canvas.height * imgW) / canvas.width;
                    var imgData = canvas.toDataURL('image/png', 0.92);

                    if (imgH_mm_total <= usableHmm + 0.25) {
                        drawFooter(pdf);
                        pdf.addImage(imgData, 'PNG', marginX, marginTop, imgW, imgH_mm_total);
                    } else {
                        function slicePxForMm(hmm) {
                            return Math.floor((hmm * canvas.width) / innerW);
                        }

                        var remainingMm = imgH_mm_total;
                        var offsetPx = 0;
                        var pageIdx = 0;

                        while (remainingMm > 0.2 && pageIdx <= 48) {
                            if (pageIdx > 0) pdf.addPage();
                            drawFooter(pdf);

                            var slicePxCap = slicePxForMm(usableHmm);
                            var slicePx = Math.min(slicePxCap, canvas.height - offsetPx);
                            if (slicePx <= 0) break;

                            var pageCanvas = global.document.createElement('canvas');
                            pageCanvas.width = canvas.width;
                            pageCanvas.height = slicePx;
                            var ctx = pageCanvas.getContext('2d');
                            if (!ctx) break;
                            ctx.drawImage(
                                canvas,
                                0,
                                offsetPx,
                                canvas.width,
                                slicePx,
                                0,
                                0,
                                canvas.width,
                                slicePx
                            );

                            var pageImg = pageCanvas.toDataURL('image/png', 0.92);
                            var pageImgMm = (slicePx * innerW) / canvas.width;
                            pdf.addImage(pageImg, 'PNG', marginX, marginTop, imgW, pageImgMm);
                            remainingMm -= pageImgMm;
                            offsetPx += slicePx;
                            pageIdx++;
                        }
                    }

                    pdf.save(fileName);
                })
                .catch(function () {
                    global.alert('PDF : impossible de générer le document.');
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.textContent = oldText;
                });
        });
    }

    global.prolinkBindReportPdf = bind;
})(typeof window !== 'undefined' ? window : this);
