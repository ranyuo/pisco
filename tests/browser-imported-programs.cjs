const{chromium}=require('playwright');const assert=require('node:assert/strict');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8080';if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Solo pruebas locales.');
(async()=>{const b=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});let p,original;try{
 const c=await b.newContext();await c.route('https://**/*',r=>r.abort());p=await c.newPage();const errors=[];p.on('pageerror',e=>errors.push(e.message));
 await p.goto(base+'/#/admin');await p.locator('#lg-u').fill('admin');await p.locator('#lg-p').fill('PruebaOriginal-2026!');await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user&&typeof DB!=='undefined'&&DB?.config);
 original=await p.evaluate(()=>structuredClone(DB.programas));
 for(const program of original){
  await p.goto(base+'/#/admin/programas/'+program.id);await p.locator('[name=resumen]').fill('Resumen temporal para comprobar la edición del programa.');
  await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();await p.waitForURL('**/#/admin/programas');
  await p.reload();const row=await p.evaluate(id=>DB.programas.find(p=>p.id===id),program.id);
  assert.equal(row.resumen,'Resumen temporal para comprobar la edición del programa.');assert.deepEqual(row.campoItems,program.campoItems);assert.deepEqual(row.modulos,program.modulos);assert.equal(row.coordinadorId,program.coordinadorId);
 }
 assert.deepEqual(errors,[]);console.log('OK cinco programas editables: mallas, ámbitos laborales y coordinadores se conservan al guardar y recargar');
}finally{if(p&&original)await p.evaluate(original=>{DB.programas=original;if(!saveDB())throw Error('No se restauraron los programas');},original);await b.close();}})().catch(e=>{console.error(e);process.exitCode=1});
