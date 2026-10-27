const {chromium}=require('playwright');
const {execFileSync}=require('node:child_process');
const assert=require('node:assert/strict');
const fixture=mode=>execFileSync('docker',['--host=unix:///var/run/docker.sock','exec','pisco-web','php','tests/content-upgrade-fixture.php',mode],{encoding:'utf8'});
(async()=>{
 let started=false,browser;
 try{
  const {key}=JSON.parse(fixture('begin'));started=true;
  browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox'],headless:true});
  const page=await browser.newPage();const endpoint='http://127.0.0.1:8080/actualizar-contenido.php';
  assert.equal((await page.goto(endpoint)).status(),200);
  const csrf=await page.locator('[name=_csrf]').inputValue();
  assert.equal((await page.request.post(endpoint,{form:{owner_key:key,confirm_replace:'yes'}})).status(),419);
  assert.equal((await page.request.post(endpoint,{form:{_csrf:csrf,owner_key:'incorrecta',confirm_replace:'yes'}})).status(),403);
  assert.equal((await page.request.post(endpoint,{form:{_csrf:csrf,owner_key:key}})).status(),422);
  fixture('unchanged');console.log('OK CSRF, clave y confirmación: rechazo sin modificar datos');
  await page.locator('[name=owner_key]').fill(key);await page.locator('[name=confirm_replace]').check();
  await Promise.all([page.waitForNavigation(),page.getByRole('button',{name:'Respaldar y actualizar contenido oficial'}).click()]);
  assert.ok((await page.locator('[role=status]').innerText()).includes('actualizado correctamente'));
  assert.ok(!(await page.content()).includes(key));fixture('verify');
  console.log('OK importación, respaldo protegido, cuentas, contraseñas, trámites y relaciones académicas conservadas');
  assert.equal((await page.goto(endpoint)).status(),410);assert.equal(await page.locator('form').count(),0);
  assert.equal((await page.request.post(endpoint,{form:{_csrf:csrf,owner_key:key,confirm_replace:'yes'}})).status(),410);
  fixture('verify');console.log('OK bloqueo de uso posterior');
 }finally{if(browser)await browser.close();if(started)fixture('restore');}
})().catch(e=>{console.error(e);process.exitCode=1});
