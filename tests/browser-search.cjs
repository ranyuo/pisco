/* Regresiones del buscador público; solo requiere una instalación local de pruebas. */
const {chromium} = require('playwright');
const assert = require('node:assert/strict');
const base = process.env.PISCO_BASE_URL || 'http://127.0.0.1:8083';
if (!['127.0.0.1', 'localhost'].includes(new URL(base).hostname)) throw Error('Usa una instalación local de pruebas.');
(async () => {
  const browser = await chromium.launch({executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium', args: ['--no-sandbox'], headless: true});
  try {
    const page = await browser.newPage({viewport: {width: 1365, height: 900}, reducedMotion: 'reduce'});
    await page.route('https://**/*', route => route.abort());
    const errors = []; page.on('pageerror', error => errors.push(error.message));
    let checked = 0;
    const search = async query => {
      await page.goto(base + '/#/buscar?q=' + encodeURIComponent(query));
      await page.locator('[data-bs]').waitFor();
      return page.locator('main article h2 a');
    };
    for (const [query, expected] of [
      ['mesa partes', 'Mesa de Partes Virtual'], ['partes mesa', 'Mesa de Partes Virtual'],
      ['arquitectura ti', 'Arquitectura de Plataformas'], ['APSTI', 'Arquitectura de Plataformas'],
      ['  CONSTRUCCIÓN   CIVIL ', 'Construcción Civil'], ['construccion civil', 'Construcción Civil'],
      ['admision', 'Admisión'], ['matrícula', 'Matrícula'], ['becas', 'Becas'],
      ['licenciamiento', 'Licenciamiento'], ['Doris Gonzales', 'Doris Elizabeth Gonzales Alejos'],
      ['3.1', '3.1 DERECHO DE INSCRIPCIÓN']
    ]) {
      const links = await search(query);
      assert.ok((await links.allTextContents()).some(title => title.includes(expected)), query + ': falta ' + expected);
      assert.ok((await links.evaluateAll(nodes => nodes.map(n => n.getAttribute('href')))).every(url => url && url.startsWith('#/')), 'Enlace inválido');
      checked++;
    }
    let links = await search('admision'); assert.equal(await links.first().innerText(), 'Admisión');
    await links.first().click(); await page.locator('main').waitFor();
    assert.equal(new URL(page.url()).hash, '#/admision'); checked++;
    // Formularios reales: cabecera, página de resultados y menú móvil.
    await page.goto(base + '/'); await page.locator('#q1').fill('mesa partes'); await page.locator('#q1').press('Enter');
    await page.waitForURL('**/#/buscar?q=mesa%20partes'); assert.ok((await page.locator('main').innerText()).includes('Mesa de Partes Virtual')); checked++;
    await page.locator('#bs-q').fill('arquitectura ti'); await page.locator('[data-bs] button').click();
    await page.waitForURL('**/#/buscar?q=arquitectura%20ti'); assert.ok((await page.locator('main article').allTextContents()).some(x => x.includes('Arquitectura de Plataformas'))); checked++;
    await page.setViewportSize({width: 390, height: 844}); await page.goto(base + '/'); await page.locator('[data-menu]').click();
    await page.locator('#q2').fill('matricula'); await page.locator('#q2').press('Enter'); await page.waitForURL('**/#/buscar?q=matricula');
    assert.ok((await page.locator('main').innerText()).includes('Matrícula'));
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)); checked++;
    await search('a'); assert.ok((await page.locator('main').innerText()).includes('al menos dos')); checked++;
    links = await search('zzzinexistentexyz'); assert.equal(await links.count(), 0);
    assert.ok((await page.locator('main').innerText()).includes('No se encontraron resultados')); checked++;
    // Una sesión administrativa tampoco debe indexar borradores, noticias futuras ni datos privados.
    const visibleOnly = await page.evaluate(() => {
      DB.noticias.push({id:'qa-draft',estado:'Borrador',titulo:'SecretoPrueba',fecha:'2020-01-01'}, {id:'qa-future',estado:'Publicada',titulo:'SecretoPrueba',fecha:'2099-01-01'});
      DB.personas.push({id:'qa-hidden',estado:'Publicado',nombres:'SecretoPrueba',apellidos:'Oculto',programas:[],cv:{autoriza:{ok:false,revocado:true}}});
      DB.personas[0].dni='SecretoPrueba'; DB.personas[0].telefono='SecretoPrueba';
      return searchPublicSite('SecretoPrueba').length;
    }); assert.equal(visibleOnly, 0); checked++;
    assert.deepEqual(errors, []); console.log(checked + ' comprobaciones del buscador aprobadas; sin errores JavaScript.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
