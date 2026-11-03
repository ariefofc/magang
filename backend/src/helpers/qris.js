/**
 * QRIS Dynamic Helper - Port dari PHP ke Node.js
 * EMVCo TLV parser/builder + CRC-16/CCITT-FALSE
 */
const QRCode = require('qrcode');

const TAG_INITIATION_METHOD = '01';
const TAG_AMOUNT = '54';
const TAG_CRC = '63';
const DYNAMIC_MODE = '12';
const CRC_LENGTH = '04';

function crc16(data) {
  let crc = 0xFFFF;
  for (let i = 0; i < data.length; i++) {
    crc ^= (data.charCodeAt(i) << 8);
    for (let j = 0; j < 8; j++) {
      if (crc & 0x8000) {
        crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
      } else {
        crc = (crc << 1) & 0xFFFF;
      }
    }
  }
  return crc;
}

function crc16Hex(crc) {
  return crc.toString(16).toUpperCase().padStart(4, '0');
}

function parseTlv(payload) {
  const fields = [];
  let i = 0;
  const len = payload.length;

  while (i + 4 <= len) {
    const tag = payload.substring(i, i + 2);
    if (tag === TAG_CRC) break;
    const length = parseInt(payload.substring(i + 2, i + 4), 10);
    if (i + 4 + length > len) break;
    fields.push({ tag, value: payload.substring(i + 4, i + 4 + length) });
    i += 4 + length;
  }

  return fields;
}

function buildTlv(fields) {
  return fields.map(f =>
    f.tag + String(f.value.length).padStart(2, '0') + f.value
  ).join('');
}

function generateQrisDynamic(staticPayload, amount) {
  staticPayload = staticPayload.trim();

  if (!staticPayload) {
    throw new Error('Payload QRIS kosong. Isi QRIS_STATIC_STRING di .env.');
  }
  if (amount <= 0) {
    throw new Error('Nominal harus lebih dari 0.');
  }

  const fields = parseTlv(staticPayload);
  if (!fields.length) {
    throw new Error('Gagal parse payload QRIS. Format EMVCo TLV tidak valid.');
  }

  // Set tag 01 to dynamic mode
  let foundTag01 = false;
  for (const f of fields) {
    if (f.tag === TAG_INITIATION_METHOD) {
      f.value = DYNAMIC_MODE;
      foundTag01 = true;
      break;
    }
  }
  if (!foundTag01) {
    fields.unshift({ tag: TAG_INITIATION_METHOD, value: DYNAMIC_MODE });
  }

  // Set/insert tag 54 (amount)
  const amountStr = String(amount);
  let has54 = false;
  for (const f of fields) {
    if (f.tag === TAG_AMOUNT) {
      f.value = amountStr;
      has54 = true;
      break;
    }
  }
  if (!has54) {
    const newFields = [];
    let inserted = false;
    for (const f of fields) {
      if (!inserted && f.tag > '53') {
        newFields.push({ tag: TAG_AMOUNT, value: amountStr });
        inserted = true;
      }
      newFields.push(f);
    }
    if (!inserted) newFields.push({ tag: TAG_AMOUNT, value: amountStr });
    fields.length = 0;
    fields.push(...newFields);
  }

  const body = buildTlv(fields) + TAG_CRC + CRC_LENGTH;
  return body + crc16Hex(crc16(body));
}

async function toBase64Png(payload, size = 280) {
  try {
    const dataUrl = await QRCode.toDataURL(payload, {
      width: size,
      margin: 1,
      errorCorrectionLevel: 'M',
    });
    return dataUrl;
  } catch (e) {
    // Fallback to API
    return `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(payload)}&size=${size}x${size}&format=png&ecc=M&margin=10`;
  }
}

function validateQris(payload) {
  if (payload.length < 8) return false;
  const crcTagPos = payload.lastIndexOf('6304');
  if (crcTagPos === -1) return false;

  const dataForCrc = payload.substring(0, crcTagPos + 4);
  const expectedCrc = payload.substring(crcTagPos + 4, crcTagPos + 8).toUpperCase();
  const actualCrc = crc16Hex(crc16(dataForCrc));

  return actualCrc === expectedCrc;
}

function decodeQris(payload) {
  const labels = {
    '00': 'Payload Format Indicator',
    '01': 'Initiation Method (11=Statis, 12=Dinamis)',
    '26': 'Merchant Account — DANA/QRIS',
    '52': 'Merchant Category Code',
    '53': 'Currency (360=IDR)',
    '54': '*** Transaction Amount [TERKUNCI] ***',
    '58': 'Country Code',
    '59': 'Merchant Name',
    '60': 'Merchant City',
    '61': 'Postal Code',
    '62': 'Additional Data',
    '63': 'CRC16 Checksum',
  };

  return parseTlv(payload).map(f => ({
    tag: f.tag,
    len: f.value.length,
    value: f.value,
    label: labels[f.tag] || `Tag ${f.tag}`,
  }));
}

module.exports = {
  crc16,
  crc16Hex,
  parseTlv,
  buildTlv,
  generateQrisDynamic,
  toBase64Png,
  validateQris,
  decodeQris,
};
