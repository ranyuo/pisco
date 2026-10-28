/* Verificación institucional y de edición: ejecutar solo en una instalación local de pruebas. */
const {chromium}=require('playwright');
const fs=require('fs'),assert=require('assert/strict'),path=require('path');
const root=path.resolve(__dirname,'..'),results=path.join(__dirname,'.results');fs.mkdirSync(results,{recursive:true});
const seed=JSON.parse(fs.readFileSync(path.join(root,'portal-private/data/portal.json')));
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8083';
if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Solo servidor local de pruebas.');
if(!process.env.PISCO_TEST_CREDENTIALS)throw Error('Define PISCO_TEST_CREDENTIALS con un archivo privado de credenciales QA.');
(async()=>{const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',headless:true,args:['--no-sandbox']});const context=await browser.newContext({reducedMotion:'reduce',viewport:{width:1365,height:900},acceptDownloads:true});await context.route('https://**/*',r=>r.abort());const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));let checked=0;
const routes=['/','/programas',...seed.programas.map(p=>'/programas/'+p.slug),'/nosotros/presentacion','/nosotros/resena-historica','/nosotros/mision-vision','/nosotros/organizacion','/nosotros/reconocimientos','/nosotros/plana-jerarquica','/nosotros/plana-docente','/nosotros/campus-e-informes','/admision','/matricula','/becas','/transparencia/documentos-gestion','/transparencia/tupa','/transparencia/licenciamiento','/transparencia/estadisticas','/transparencia/inversiones','/transparencia/convocatorias','/noticias',...seed.noticias.slice(0,3).map(n=>'/noticias/'+n.slug),'/servicios','/servicios/mesa-de-partes','/servicios/libro-de-reclamaciones','/servicios/consulta-certificados','/servicios/bolsa-laboral','/contacto','/politica-de-privacidad','/terminos-de-uso'];
for(const route of routes){await page.goto(base+'/#'+route);await page.waitForTimeout(120);const body=await page.locator('body').innerText();assert.ok(body.length>300,route);assert.ok(!/Warning:|Fatal error:|\[object Object\]|undefined|No se encontró|Página no encontrada/.test(body),route+' invalid content');
const main=await page.locator('main').innerText();assert.ok(main.length>40,route+' empty main');assert.equal(errors.length,0,route+': '+errors.join(';'));
if(route==='/programas')assert.equal(await page.evaluate(()=>document.querySelectorAll('a[href^="#/programas/"]').length)>=8,true);
if(route==='/becas')assert.ok(body.includes('30 beneficiario'),body.slice(-1000));
if(route==='/transparencia/tupa')assert.ok(body.includes('74 concepto'));
if(route==='/matricula')assert.ok(body.includes('Matrícula de ingresantes'));
if(route==='/nosotros/plana-docente'){assert.ok(await page.locator('.pz-tcard').count()>0,'El directorio docente no puede estar vacío');await page.getByRole('button',{name:'Ficha pública',exact:true}).first().click();assert.ok((await page.locator('[role=dialog]').innerText()).includes('Información académica'));}
if(route==='/nosotros/plana-jerarquica')assert.ok(body.includes('Doris Elizabeth'));
checked++;console.log('OK',route);
}
await page.goto(base+'/#/');await page.waitForTimeout(300);await page.screenshot({path:path.join(results,'chincha-desktop.png'),fullPage:true});
await page.setViewportSize({width:390,height:844});
for(const route of ['/','/programas','/programas/construccion-civil','/becas','/transparencia/tupa','/nosotros/plana-docente','/contacto']){await page.goto(base+'/#'+route);await page.waitForTimeout(150);const width=await page.evaluate(()=>({body:document.documentElement.scrollWidth,viewport:innerWidth}));assert.ok(width.body<=width.viewport+1,route+' mobile overflow '+JSON.stringify(width));checked++;}
await page.goto(base+'/#/');await page.screenshot({path:path.join(results,'chincha-mobile.png'),fullPage:true});
await page.setViewportSize({width:1365,height:900});await page.goto(base+'/#/admin');const creds=JSON.parse(fs.readFileSync(process.env.PISCO_TEST_CREDENTIALS));
await page.locator('#lg-u').fill('admin');await page.locator('#lg-p').fill(creds.password);await page.locator('[data-login] button[type=submit]').click();await page.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user?.usuario==='admin');
for(const route of ['/admin/programas','/admin/docentes','/admin/becas','/admin/documentos','/admin/tupa','/admin/locales','/admin/config']){await page.goto(base+'/#'+route);await page.waitForTimeout(130);assert.equal(errors.length,0,errors.join(';'));checked++;console.log('OK',route);}
// Round trip through the real editor and a separate public session.
const first=seed.noticias[0];await page.goto(base+'/#/admin/noticias/'+first.id);await page.locator('[data-cf] [name=titulo]').fill(first.titulo+' · QA');await page.getByRole('button',{name:'Guardar cambios',exact:true}).click();await page.waitForTimeout(500);
const guest=await context.newPage();await guest.goto(base+'/#/noticias/'+first.slug);assert.ok((await guest.locator('main').innerText()).includes(first.titulo+' · QA'));await page.goto(base+'/#/admin/noticias/'+first.id);await page.locator('[data-cf] [name=titulo]').fill(first.titulo);await page.getByRole('button',{name:'Guardar cambios',exact:true}).click();checked++;
// Original PDFs and brochure downloads use real local files, not generated sample PDFs.
await page.goto(base+'/#/transparencia/tupa');const download=page.waitForEvent('download');await page.evaluate(()=>downloadDoc(DB.tupaDocs.tupa));const file=await download;assert.ok(file.suggestedFilename().endsWith('.pdf'));assert.equal(await file.failure(),null);checked++;
if(creds.install_key){await page.goto(base+'/actualizar.php');await page.locator('[name=owner_key]').fill(creds.install_key);await page.locator('[name=import_chincha]').check();await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'Actualizar conservando los datos'}).click()]);assert.ok((await page.locator('body').innerText()).includes('Se importó el contenido público de Chincha'));checked++;console.log('OK importación desde actualizar.php');}
await browser.close();console.log(JSON.stringify({checked,jsErrors:errors},null,2));})().catch(e=>{console.error(e);process.exit(1)});
