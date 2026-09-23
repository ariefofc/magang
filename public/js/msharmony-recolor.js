/**
 * MS Harmony - AI Kebaya Recolor Engine v2.0
 * Method  : Canvas API + HSL Smart Color Replacement
 * Engine  : Client-side (no server required for preview)
 * Preserves: face, shadows, highlights, accessories, embroidery
 *            Only changes the fabric/kain kebaya color
 */
const MSHarmonyRecolor = (() => {

  /* ── COLOR DATABASE [hue_degrees, saturation 0-1, lightness 0-1] ── */
  const COLORS = {
    'sage green':[140,.36,.54],'sage':[140,.36,.54],
    'emerald green':[145,.66,.36],'emerald':[145,.66,.36],
    'mint green':[153,.42,.70],'mint':[153,.42,.70],
    'olive green':[76,.40,.38],'olive':[76,.40,.38],
    'forest green':[130,.55,.28],
    'hijau sage':[140,.36,.54],'hijau emerald':[145,.66,.36],
    'hijau mint':[153,.42,.70],'hijau':[125,.65,.44],'green':[125,.65,.44],
    'navy blue':[220,.72,.26],'navy':[220,.72,.26],
    'royal blue':[225,.75,.45],'royal navy':[220,.72,.26],
    'sky blue':[200,.65,.65],'teal':[177,.62,.38],
    'biru navy':[220,.72,.26],'biru':[210,.75,.52],'blue':[210,.75,.52],
    'dusty rose':[345,.33,.67],'dusty pink':[345,.28,.72],
    'rose gold':[350,.43,.72],'rose':[350,.50,.68],
    'blush pink':[350,.38,.82],'blush':[350,.38,.82],
    'hot pink':[330,.80,.58],'pink':[340,.56,.74],'merah muda':[340,.56,.74],
    'coral pink':[10,.72,.70],'coral':[10,.72,.66],
    'burgundy':[350,.70,.30],'maroon':[355,.72,.27],
    'red':[0,.82,.52],'merah':[0,.82,.52],
    'terracotta':[15,.62,.52],'terakota':[15,.62,.52],
    'burnt orange':[22,.80,.48],'peach':[20,.58,.80],
    'orange':[25,.87,.60],'apricot':[30,.65,.75],
    'lavender':[270,.32,.73],'purple':[278,.66,.48],
    'lilac':[275,.30,.75],'violet':[265,.72,.52],'ungu':[278,.66,.48],
    'champagne':[42,.40,.86],'gold':[42,.84,.58],
    'ivory':[40,.40,.90],'cream':[40,.44,.87],
    'nude beige':[28,.32,.76],'nude':[20,.30,.76],
    'beige':[34,.28,.80],'taupe':[30,.18,.60],'sand':[38,.35,.78],
    'white pearl':[40,.12,.93],'white':[0,.05,.94],'putih':[0,.05,.94],
    'silver':[0,.06,.72],'gray':[0,.05,.55],'grey':[0,.05,.55],
    'abu-abu':[0,.05,.55],'abu':[0,.05,.55],
    'black':[0,.05,.08],'hitam':[0,.05,.08],
    'chocolate':[22,.66,.27],'coklat':[22,.60,.38],'brown':[22,.56,.40],
    'mustard':[46,.78,.52],'lemon':[60,.75,.80],
    'yellow':[55,.82,.70],'kuning':[55,.82,.70],
  };

  function parseColor(text) {
    const t = text.toLowerCase().trim();
    const isDark   = /\b(dark|deep|tua|gelap|dull)\b/.test(t);
    const isLight  = /\b(light|pale|muda|soft|terang)\b/.test(t);
    const isPastel = /\bpastel\b/.test(t);
    const isMuted  = /\b(muted|dusty|abu|subtle|redup)\b/.test(t);
    let best = null, bestLen = 0;
    for (const [name, hsl] of Object.entries(COLORS)) {
      if (t.includes(name) && name.length > bestLen) { best=[...hsl]; bestLen=name.length; }
    }
    if (!best) { for (const w of t.split(/[\s,+&]+/)) { if(COLORS[w]){best=[...COLORS[w]];break;} } }
    if (!best) best=[140,.36,.54];
    let [hDeg,s,l]=best, h=hDeg/360;
    if(isDark)   { l=Math.max(.10,l-.20); s=Math.min(1,s+.10); }
    if(isLight)  { l=Math.min(.92,l+.15); s=Math.max(.08,s-.20); }
    if(isPastel) { l=Math.min(.88,l+.12); s=Math.max(.10,s-.22); }
    if(isMuted)  { s=Math.max(.10,s-.14); }
    return [h,s,l];
  }

  function rgbToHsl(r,g,b) {
    const mx=Math.max(r,g,b),mn=Math.min(r,g,b),l=(mx+mn)/2;
    if(mx===mn)return[0,0,l];
    const d=mx-mn, s=l>.5?d/(2-mx-mn):d/(mx+mn);
    let h;
    switch(mx){case r:h=((g-b)/d+(g<b?6:0))/6;break;case g:h=((b-r)/d+2)/6;break;default:h=((r-g)/d+4)/6;}
    return[h%1,s,l];
  }

  function hslToRgb(h,s,l) {
    if(s===0)return[l,l,l];
    const hue2rgb=(p,q,t)=>{if(t<0)t+=1;if(t>1)t-=1;if(t<1/6)return p+(q-p)*6*t;if(t<1/2)return q;if(t<2/3)return p+(q-p)*(2/3-t)*6;return p;};
    const q=l<.5?l*(1+s):l+s-l*s,p=2*l-q;
    return[hue2rgb(p,q,h+1/3),hue2rgb(p,q,h),hue2rgb(p,q,h-1/3)];
  }

  function isSkinTone(h,s,l){return h>=0&&h<=.09&&s>=.14&&s<=.66&&l>=.40;}

  function detectFabricColor(data,W,H) {
    const yS=Math.floor(H*.22),yE=Math.floor(H*.90);
    const xS=Math.floor(W*.08),xE=Math.floor(W*.92);
    const counts=new Float32Array(36),satAcc=new Float32Array(36),ligAcc=new Float32Array(36);
    for(let y=yS;y<yE;y+=3){
      for(let x=xS;x<xE;x+=3){
        const i=(y*W+x)*4;
        const r=data[i]/255,g=data[i+1]/255,b=data[i+2]/255;
        const[h,s,l]=rgbToHsl(r,g,b);
        if(s<.09||l<.08||l>.93||isSkinTone(h,s,l))continue;
        const bkt=Math.floor(h*36)%36;
        counts[bkt]++;satAcc[bkt]+=s;ligAcc[bkt]+=l;
      }
    }
    let best=0;for(let i=1;i<36;i++)if(counts[i]>counts[best])best=i;
    const cnt=counts[best]||1;
    return{h:(best+.5)/36,s:satAcc[best]/cnt,l:ligAcc[best]/cnt};
  }

  async function recolorImage(imageSrc,colorPrompt,onProgress) {
    return new Promise((resolve,reject)=>{
      const img=new Image();
      img.onload=()=>{
        try{
          onProgress&&onProgress(8);
          const MAX_W=920;
          const scale=img.naturalWidth>MAX_W?MAX_W/img.naturalWidth:1;
          const W=Math.round(img.naturalWidth*scale),H=Math.round(img.naturalHeight*scale);
          const canvas=document.createElement('canvas');
          canvas.width=W;canvas.height=H;
          const ctx=canvas.getContext('2d');
          ctx.drawImage(img,0,0,W,H);
          onProgress&&onProgress(18);
          const imgData=ctx.getImageData(0,0,W,H),data=imgData.data;
          const fab=detectFabricColor(data,W,H);
          onProgress&&onProgress(32);
          const[tH,tS,tL]=parseColor(colorPrompt);
          onProgress&&onProgress(42);
          const HUE_THRESH=0.148;
          for(let i=0;i<data.length;i+=4){
            const r=data[i]/255,g=data[i+1]/255,b=data[i+2]/255;
            const[h,s,l]=rgbToHsl(r,g,b);
            if(l<.07||l>.94||s<.07||isSkinTone(h,s,l))continue;
            let hd=Math.abs(h-fab.h);if(hd>.5)hd=1-hd;
            if(hd>=HUE_THRESH)continue;
            const weight=Math.pow(1-hd/HUE_THRESH,1.6);
            const newL=Math.max(.04,Math.min(.96,tL+(l-fab.l)*.88));
            const newS=Math.max(.06,Math.min(1,tS+(s-fab.s)*.55));
            const[nr,ng,nb]=hslToRgb(tH,newS,newL);
            data[i]=Math.round((r+(nr-r)*weight)*255);
            data[i+1]=Math.round((g+(ng-g)*weight)*255);
            data[i+2]=Math.round((b+(nb-b)*weight)*255);
          }
          onProgress&&onProgress(90);
          ctx.putImageData(imgData,0,0);
          onProgress&&onProgress(100);
          resolve(canvas.toDataURL('image/jpeg',.93));
        }catch(err){reject(err);}
      };
      img.onerror=()=>reject(new Error('Gagal memuat gambar kebaya'));
      img.src=imageSrc;
    });
  }

  return{recolorImage,parseColor,detectFabricColor};
})();
