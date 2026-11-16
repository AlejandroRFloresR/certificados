/**
 * Limpia la foto o el escaneo de una firma en el navegador (no sale del sistema):
 * 1. normaliza la luz tomando el papel como blanco de referencia (fotos grisáceas);
 * 2. vuelve transparente lo claro (papel) y conserva lo oscuro (tinta), con bordes suaves;
 * 3. opcionalmente pinta la tinta de negro o azul;
 * 4. recorta los márgenes vacíos.
 * Devuelve un File PNG listo para subir, o null si no encontró trazos.
 */

const MAX_SIDE = 1400;   // achica fotos grandes de celular: alcanza de sobra para el PDF
const SOFT_EDGE = 45;    // ancho de la transición tinta → transparente (evita bordes serruchados)
const PADDING = 12;      // margen que se deja alrededor de la firma al recortar

export const INKS = {
    original: null,
    black: [25, 25, 30],
    blue: [10, 45, 120],
};

function loadImage(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => { URL.revokeObjectURL(url); resolve(img); };
        img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('No se pudo leer la imagen.')); };
        img.src = url;
    });
}

/** Brillo de referencia del papel: el percentil 90 de la luminancia (ignora reflejos sueltos) */
function paperBrightness(lum) {
    const histogram = new Uint32Array(256);
    for (let i = 0; i < lum.length; i++) histogram[lum[i]]++;
    const target = lum.length * 0.9;
    let count = 0;
    for (let v = 0; v < 256; v++) {
        count += histogram[v];
        if (count >= target) return Math.max(v, 1);
    }
    return 255;
}

/**
 * @param {File} file   imagen original (PNG o JPG)
 * @param {object} opts { strength: 0-100 (más alto = limpia más), ink: 'original' | 'black' | 'blue' }
 */
export async function cleanSignature(file, { strength = 50, ink = 'original' } = {}) {
    const img = await loadImage(file);
    const scale = Math.min(1, MAX_SIDE / Math.max(img.naturalWidth, img.naturalHeight));
    const width = Math.round(img.naturalWidth * scale);
    const height = Math.round(img.naturalHeight * scale);

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    ctx.drawImage(img, 0, 0, width, height);

    const image = ctx.getImageData(0, 0, width, height);
    const px = image.data;

    // Luminancia de cada píxel (las partes ya transparentes cuentan como papel)
    const lum = new Uint8ClampedArray(width * height);
    for (let i = 0, p = 0; i < px.length; i += 4, p++) {
        lum[p] = px[i + 3] < 16 ? 255 : 0.299 * px[i] + 0.587 * px[i + 1] + 0.114 * px[i + 2];
    }

    const paper = paperBrightness(lum);
    // Más "limpieza" = umbral más bajo = solo sobrevive lo bien oscuro
    const threshold = 235 - strength * 1.2;
    const inkColor = INKS[ink];

    let minX = width, minY = height, maxX = -1, maxY = -1;

    for (let p = 0, i = 0; p < lum.length; p++, i += 4) {
        const normalized = Math.min(255, (lum[p] / paper) * 255);
        // 0 = papel, 1 = tinta plena
        const coverage = Math.min(1, Math.max(0, (threshold - normalized) / SOFT_EDGE));
        const alpha = Math.round(coverage * 255);

        px[i + 3] = alpha;
        if (alpha > 0 && inkColor) {
            px[i] = inkColor[0];
            px[i + 1] = inkColor[1];
            px[i + 2] = inkColor[2];
        }

        if (alpha > 40) {
            const x = p % width;
            const y = (p - x) / width;
            if (x < minX) minX = x;
            if (x > maxX) maxX = x;
            if (y < minY) minY = y;
            if (y > maxY) maxY = y;
        }
    }

    if (maxX < 0) return null; // no quedó ningún trazo

    ctx.putImageData(image, 0, 0);

    // Recortar alrededor de la firma
    const x0 = Math.max(0, minX - PADDING);
    const y0 = Math.max(0, minY - PADDING);
    const cropW = Math.min(width, maxX + PADDING + 1) - x0;
    const cropH = Math.min(height, maxY + PADDING + 1) - y0;

    const out = document.createElement('canvas');
    out.width = cropW;
    out.height = cropH;
    out.getContext('2d').drawImage(canvas, x0, y0, cropW, cropH, 0, 0, cropW, cropH);

    const blob = await new Promise((resolve) => out.toBlob(resolve, 'image/png'));
    const baseName = file.name.replace(/\.[^.]+$/, '') || 'firma';

    return new File([blob], `${baseName}-sin-fondo.png`, { type: 'image/png' });
}
