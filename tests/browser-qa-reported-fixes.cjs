/* Regresiones de los tres hallazgos QA. Ejecutar después de parity.php en una base local _test. */
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs/promises');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8086';
if(!['localhost','127.0.0.1'].includes(new URL(base).hostname))throw Error('Solo servidor local de pruebas.');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
 try{
  const admin=await browser.newContext(),guest=await browser.newContext();
  for(const c of [admin,guest])await c.route('https://**/*',r=>r.abort());
  const p=await admin.newPage(),g=await guest.newPage(),errors=[];
  for(const q of [p,g])q.on('pageerror',e=>errors.push(e.message));
  const go=(q,path)=>q.goto(base+'/'+path,{waitUntil:'domcontentloaded'});
  const suffix=Date.now().toString(),password='Prueba-QA-2026!';
  await go(p,'#/admin');await p.locator('#lg-u').fill('admin');await p.locator('#lg-p').fill('PruebaOriginal-2026!');
  await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user?.rol==='admin');
  for(let n=1;n<=2;n++){
   await go(p,'#/admin/usuarios/nuevo');
   const username='qa-inactivo-'+suffix+'-'+n;
   for(const[name,value]of Object.entries({usuario:username,nombre:'Prueba QA inactiva '+n,correo:username+'@example.test',pass1:password,pass2:password}))await p.locator('[data-cf] [name='+name+']').fill(value);
   await p.locator('[name=rol]').selectOption('imagen');await p.locator('[name=estado]').selectOption('Inactivo');
   await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.waitForURL('**/#/admin/usuarios');await p.reload();
   assert.equal(await p.evaluate(u=>DB.usuarios.find(x=>x.usuario===u)?.estado,username),'Inactivo');
  }
  console.log('OK dos altas desde la interfaz conservan Inactivo al recargar.');
  const pages=[];
  for(let n=1;n<=2;n++){
   await go(p,'#/admin/paginas');await p.getByRole('link',{name:'Nueva página',exact:true}).click();
   const title='Página QA '+suffix+' caso '+n,slug='pagina-qa-'+suffix+(n===1?'-1':'-caso-2');
   await p.locator('[name=titulo]').fill(title);if(n===1)await p.locator('[name=slug]').fill(slug);
   await p.locator('[contenteditable=true]').fill('Contenido de prueba QA para comprobar creación y publicación persistentes.');
   await p.locator('[name=estado]').selectOption(n===1?'Publicada':'Borrador');
   await p.getByRole('button',{name:'Vista previa',exact:true}).click();
   assert.ok((await p.locator('[data-pvw]').innerText()).includes(title));
   await p.getByRole('button',{name:'Cerrar vista previa',exact:true}).click();
   await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.waitForURL('**/#/admin/paginas');await p.reload();
   const record=await p.evaluate(slug=>{const r=DB.paginas.find(x=>x.slug===slug);return{id:r.id,url:SPECS.paginas.preview(r)}},slug);
   assert.equal(record.url,'#/paginas/'+slug);pages.push({...record,title,slug});
   await go(g,record.url);assert.equal((await g.locator('main').innerText()).includes(title),n===1);
   if(n===2){
    assert.ok((await g.locator('main').innerText()).includes('Página no encontrada'));
    assert.equal(await g.locator('a[href="'+record.url+'"]').count(),0);
    await go(g,'#/buscar?q='+encodeURIComponent(title));assert.equal(await g.locator('main a[href="'+record.url+'"]').count(),0);
    await go(p,'#/admin/paginas/'+record.id);await p.locator('[name=estado]').selectOption('Publicada');
    await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.waitForURL('**/#/admin/paginas');
    await go(g,record.url);await g.reload();assert.ok((await g.locator('main').innerText()).includes(title));
   }
   await go(g,'#/');assert.ok(await g.locator('nav a[href="'+record.url+'"]').count()>0);
   await go(g,'#/buscar?q='+encodeURIComponent(title));assert.ok(await g.locator('main a[href="'+record.url+'"]').count()>0);
  }
  await go(g,'#/mapa-del-sitio');
  for(const page of pages)assert.ok(await g.locator('main a[href="'+page.url+'"]').count()>0);
  const download=g.waitForEvent('download');await g.locator('[data-sm]').click();const sitemap=await download;
  const xml=await fs.readFile(await sitemap.path(),'utf8');for(const page of pages)assert.ok(xml.includes('/paginas/'+page.slug));
  const fixedRoute=await p.evaluate(()=>SPECS.paginas.preview(DB.paginas.find(x=>x.clave==='presentacion')));assert.equal(fixedRoute,'#/nosotros/presentacion');
  await go(g,fixedRoute);assert.ok(!(await g.locator('main').innerText()).includes('Página no encontrada'));
  await go(p,'#/admin/paginas/'+pages[0].id);await p.locator('[name=estado]').selectOption('Borrador');
  await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.waitForURL('**/#/admin/paginas');
  await go(g,pages[0].url);await g.reload();assert.ok((await g.locator('main').innerText()).includes('Página no encontrada'));
  console.log('OK vista previa, rutas, navegación, buscador y sitemap; borradores privados y rutas institucionales conservadas.');
  await go(p,'#/admin/licenciamiento');
  const pdf=Buffer.from('%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF');
  for(let n=1;n<=2;n++){
   const label='Título editorial QA '+suffix+' '+n,original='original-qa-'+suffix+'-'+n+'.pdf';
   await p.locator('[data-radd=documentos]').click();await p.locator('[data-rows=documentos] [data-rc=nombre]').last().fill(label);
   await p.locator('[data-rows=documentos] input[type=file]').last().setInputFiles({name:original,mimeType:'application/pdf',buffer:pdf});
   await p.waitForFunction(()=>EDITOR_UPLOADS.size===0);
   const pending=await p.evaluate(()=>ADM.work.documentos.at(-1).doc);
   assert.equal((await guest.request.get(base+pending.url)).status(),403,'Un adjunto aún no publicado permanece privado');
   await p.locator('[data-sf]').getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.reload();
   assert.ok((await p.locator('[data-rows=documentos] [data-rc=nombre]').evaluateAll(es=>es.map(e=>e.value))).includes(label));
   const file=await p.evaluate(label=>{const r=DB.licenciamiento.documentos.find(x=>x.nombre===label);return r.doc},label);
   assert.equal(file.original,original);assert.equal(file.type,'application/pdf');
   const response=await admin.request.get(base+file.url);assert.equal(response.status(),200);assert.deepEqual(await response.body(),pdf);
   const published=await guest.request.get(base+file.url);assert.equal(published.status(),200);assert.deepEqual(await published.body(),pdf);
  }
  console.log('OK dos títulos de documentos persisten; los PDF conservan su nombre original, bytes y permisos.');
  assert.deepEqual(errors,[]);console.log('Regresiones QA de navegador aprobadas.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
