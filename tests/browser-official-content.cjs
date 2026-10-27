const {chromium}=require('playwright');
const assert=require('node:assert/strict');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',headless:true,args:['--no-sandbox']});
 try{
  const page=await browser.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.route('https://**/*',r=>r.abort());
  const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8080';
  assert.ok(['127.0.0.1','localhost'].includes(new URL(base).hostname));
  const routes=['/','/nosotros/presentacion','/nosotros/mision-vision','/nosotros/plana-jerarquica','/nosotros/plana-docente','/nosotros/campus-e-informes','/programas','/admision','/matricula','/transparencia/documentos-gestion','/transparencia/convocatorias','/noticias','/eventos','/contacto','/becas','/transparencia/estadisticas','/preguntas-frecuentes'];
  for(const r of routes){
   await page.goto(base+'/#'+r);await page.waitForTimeout(200);
   const text=await page.locator('#app').innerText();
   assert.ok(text.length>150,r+' tiene contenido');
   assert.ok(!text.includes('INSTITUTO PUBLICO DE ICA'),r+' sin identidad de demostración');
   assert.ok(!text.includes('Profesional de Ejemplo'),r+' sin personas ficticias');
   if(r==='/admision') assert.ok(text.includes('Proceso de Admisión 2026'));
   if(r==='/matricula') assert.ok(text.includes('Matrícula 2025'));
   if(r==='/transparencia/estadisticas') assert.ok(!text.includes('NaN'));
   console.log('OK',r);
  }
  await page.goto(base+'/#/programas');await page.waitForTimeout(200);
  const programs=await page.evaluate(()=>DB.programas.map(p=>p.slug));
  for(const slug of programs){await page.goto(base+'/#/programas/'+slug);await page.waitForTimeout(150);assert.ok((await page.locator('#app').innerText()).includes('Profesional'));console.log('OK programa',slug);}
  await page.goto(base+'/#/');await page.waitForTimeout(200);
  const state=await page.evaluate(()=>({name:DB.config.inst.nombre,news:DB.noticias.length,people:DB.personas.length,certs:DB.certificados.length,files:DB.documentos.map(d=>d.archivo),imgs:DB.slides.map(d=>d.imagen)}));
  assert.ok(state.name.includes('Pisco'));assert.equal(state.news,7);assert.equal(state.certs,0);assert.ok(state.people>40);
  for(const f of state.files){const r=await page.request.get(base+f.url);assert.equal(r.status(),200);assert.equal((await r.body()).subarray(0,5).toString(),'%PDF-');}
  for(const f of state.imgs){const r=await page.request.get(base+f);assert.equal(r.status(),200);assert.ok(r.headers()['content-type'].startsWith('image/'));}
  await page.setViewportSize({width:390,height:844});await page.reload();await page.waitForTimeout(300);
  assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Inicio adaptado a móvil');
  assert.deepEqual(errors,[]);console.log('OK datos oficiales, PDF, imágenes, móvil y ausencia de errores JavaScript');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
