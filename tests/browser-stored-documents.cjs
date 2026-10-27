const{chromium}=require('playwright');const assert=require('node:assert/strict');const fs=require('node:fs/promises');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8080';if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Solo pruebas locales.');
(async()=>{const b=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});let p,original;try{
 const c=await b.newContext();await c.route('https://**/*',r=>r.abort());p=await c.newPage();await p.goto(base+'/#/admin');await p.locator('#lg-u').fill('admin');await p.locator('#lg-p').fill('PruebaOriginal-2026!');await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user&&typeof DB!=='undefined'&&DB?.config);
 const pdf='%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n% ARCHIVO REAL QA PISCO\n%%EOF';
 original=await p.evaluate(()=>structuredClone(DB.documentos[0]));
 const meta=await p.evaluate(async({pdf,id})=>{const file=await portalUpload(new File([pdf],'original-qa.pdf',{type:'application/pdf'}));const d=DB.documentos.find(x=>x.id===id);d.archivo=file;d.refModulo='';d.estado='Publicado';if(!saveDB())throw Error('No se publicó el adjunto');return file;},{pdf,id:original.id});
 const g=await b.newContext();await g.route('https://**/*',r=>r.abort());const guest=await g.newPage();await guest.goto(base+'/#/transparencia/documentos-gestion');
 await guest.evaluate(()=>{window.open=(url)=>{window.__openedDocument=url;return null;};});
 await guest.locator('[data-v="'+original.id+'"]').click();const opened=await guest.evaluate(()=>window.__openedDocument);
 assert.ok(opened.includes('id='+meta.fileId));assert.ok(!opened.startsWith('blob:'));
 const response=await g.request.get(base+opened);assert.equal(response.status(),200);assert.equal((await response.body()).toString(),pdf);
 const event=guest.waitForEvent('download');await guest.locator('[data-d="'+original.id+'"]').click();const download=await event;assert.equal((await fs.readFile(await download.path())).toString(),pdf);
 assert.equal(await guest.evaluate(()=>docBlob({url:'/assets/archivo-inexistente.pdf',nombre:'faltante.pdf'})===null),true);
 console.log('OK visor y descarga públicos conservan los bytes del PDF subido; no se fabrica un PDF de ejemplo para referencias del servidor');
}finally{if(p&&original)await p.evaluate(original=>{DB.documentos=DB.documentos.map(d=>d.id===original.id?original:d);if(!saveDB())throw Error('No se restauró el documento');},original);await b.close();}})().catch(e=>{console.error(e);process.exitCode=1});
