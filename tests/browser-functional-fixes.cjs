/* Ejecutar tras parity.php y functional-fixes.php, exclusivamente en una base local _test. */
const {chromium}=require('playwright');
const assert=require('node:assert/strict');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8086';
if(!['localhost','127.0.0.1'].includes(new URL(base).hostname))throw Error('Solo servidor local de pruebas.');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox']});
 try {
  const admin=await browser.newPage(),guest=await browser.newPage();const errors=[];
  for(const p of [admin,guest]){await p.route('https://**/*',r=>r.abort());p.on('pageerror',e=>errors.push(e.message));}
  const login=async(p,user)=>{await p.goto(base+'/#/admin');await p.locator('#lg-u').fill(user);await p.locator('#lg-p').fill('PruebaOriginal-2026!');await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>!!window.PORTAL_BOOTSTRAP?.user&&typeof DB!=='undefined'&&DB);};
  await login(admin,'admin');
  const ranges=await admin.evaluate(()=>({year:documentYears('2026'),range:documentYears('2024 - 2030'),status:documentYears('Publicada en fuente institucional'),invalid:documentYears('2030 - 2024')}));
  assert.deepEqual(ranges,{year:['2026'],range:['2024','2025','2026','2027','2028','2029','2030'],status:[],invalid:[]});
  await admin.goto(base+'/#/admin/documentos');
  const options=await admin.locator('[name=anio] option').allTextContents();assert.ok(!options.includes('Publ'));assert.ok(options.includes('2026'));assert.ok(options.includes('Sin año especificado'));
  await admin.locator('[name=anio]').selectOption('2026');await admin.getByRole('button',{name:'Filtrar',exact:true}).click();assert.match(await admin.locator('main').innerText(),/PROYECTO EDUCATIVO/);
  console.log('OK filtro administrativo acepta años dentro de rangos y excluye textos de estado');
  const tupa=await admin.evaluate(()=>DB.tupa.find(t=>t.codigo==='1.1'));
  await admin.goto(base+'/#/admin/tupa/'+tupa.id);assert.equal(await admin.locator('[name=categoria]').inputValue(),tupa.categoria);
  await admin.locator('[name=requisitos]').fill((tupa.requisitos||'')+' Verificación de edición.');await admin.getByRole('button',{name:'Guardar cambios',exact:true}).click();await admin.waitForURL('**/#/admin/tupa');await admin.reload();assert.equal(await admin.evaluate(id=>DB.tupa.find(t=>t.id===id).categoria,tupa.id),tupa.categoria);
  console.log('OK categoría TUPA conservada al editar, guardar y recargar');
  await guest.goto(base+'/#/transparencia/tupa');assert.match(await guest.locator('[data-td=tupa]').innerText(),/referencia.*Chincha/i);assert.match(await guest.locator('[data-td=resolucion]').innerText(),/referencia/i);assert.ok(!await guest.getByRole('button',{name:'TUPA vigente (PDF)',exact:true}).count());
  await guest.locator('#tf-s').fill('MATRÍCULA');assert.match(await guest.locator('#tres').innerText(),/4\.1 MATRÍCULA REGULAR/);
  await guest.goto(base+'/#/transparencia/documentos-gestion');await guest.locator('#df-y').selectOption('2026');await guest.locator('[data-df] button').first().click();assert.match(await guest.locator('#dres').innerText(),/PROYECTO EDUCATIVO/);assert.match(await guest.locator('#dres').innerText(),/Referencia pendiente/);
  console.log('OK documentos heredados identificados como referencia y búsqueda pública corregida');
  await admin.goto(base+'/#/admin/ix-horarios');await admin.locator('#hg_p').selectOption('1');await admin.locator('#hg_c').selectOption('VI');await admin.locator('#hg_s').selectOption('B');await admin.locator('#hg_t').selectOption('Tarde');
  assert.equal(await admin.evaluate(()=>Array.isArray(IX.db().publicados)),false);
  await admin.locator('[data-act=publish]').click();await admin.locator('[data-act=unpublish]').waitFor();await admin.reload();
  await admin.locator('#hg_c').selectOption('VI');await admin.locator('#hg_s').selectOption('B');await admin.locator('#hg_t').selectOption('Tarde');await admin.locator('[data-act=unpublish]').waitFor();
  await login(guest,'70001001');await guest.goto(base+'/#/admin/ix-horarios');assert.match(await guest.locator('main').innerText(),/Unidad de prueba/);assert.ok(!(await guest.locator('main').innerText()).includes('aún no ha sido publicado'));
  console.log('OK publicación de horario persiste tras recarga y alumno consulta la clase');
  assert.deepEqual(errors,[]);console.log('Regresiones funcionales de navegador aprobadas.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
