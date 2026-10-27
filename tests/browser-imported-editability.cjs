const {chromium}=require('playwright');const assert=require('node:assert/strict');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8080';
if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Solo pruebas locales.');
(async()=>{const b=await chromium.launch({executablePath:'/usr/bin/chromium',args:['--no-sandbox']});let p,original;try{
 const c=await b.newContext();await c.route('https://**/*',r=>r.abort());p=await c.newPage();const errors=[];p.on('pageerror',e=>errors.push(e.message));
 await p.goto(base+'/#/admin');await p.locator('#lg-u').fill('admin');await p.locator('#lg-p').fill('PruebaOriginal-2026!');await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user&&typeof DB!=='undefined'&&DB?.config);
 original=await p.evaluate(()=>structuredClone(DB.personas));
 const people=original.filter(x=>x.fuente?.includes('iestppisco.edu.pe'));
 for(const person of people){
  await p.goto(base+'/#/admin/docentes/'+person.id);await p.locator('[name=especialidad]').fill('Revisión temporal de edición');
  await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();
  await p.waitForURL('**/#/admin/docentes');
  const after=await p.evaluate(id=>DB.personas.find(x=>x.id===id),person.id);
  assert.equal(after.especialidad,'Revisión temporal de edición');assert.equal(after.dni,person.dni);assert.equal(after.condicion,person.condicion);assert.equal(after.correo,person.correo);
 }
 await p.reload();assert.equal(await p.evaluate(()=>DB.personas.filter(x=>x.fuente?.includes('iestppisco.edu.pe')&&x.especialidad==='Revisión temporal de edición').length),people.length);
 assert.deepEqual(errors,[]);console.log('OK '+people.length+' fichas oficiales editadas y recargadas sin inventar DNI, correo ni condición laboral');
}finally{if(p&&original)await p.evaluate(original=>{DB.personas=original;if(!saveDB())throw Error('No se restauraron las personas de prueba');},original);await b.close();}})().catch(e=>{console.error(e);process.exitCode=1});
