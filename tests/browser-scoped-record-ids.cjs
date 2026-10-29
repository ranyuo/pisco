/* Regresión local: ejecutar después de tests/parity.php sobre una base exclusiva _test. */
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const base = process.env.PISCO_BASE_URL || 'http://127.0.0.1:8086';
if (!['localhost', '127.0.0.1'].includes(new URL(base).hostname)) {
  throw Error('Esta prueba crea datos: utiliza exclusivamente la instalación local de pruebas.');
}
const password = process.env.PISCO_SCOPED_PASSWORD || 'LocalRegression-2026!';

async function login(browser, username, pass) {
  const context = await browser.newContext();
  await context.route('https://**/*', route => route.abort());
  const page = await context.newPage();
  await page.goto(base + '/#/admin');
  await page.locator('#lg-u').fill(username);
  await page.locator('#lg-p').fill(pass);
  await page.locator('[data-login] button[type=submit]').click();
  await page.waitForFunction(() => window.PORTAL_BOOTSTRAP?.user && typeof DB !== 'undefined' && DB);
  return { context, page };
}

(async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || '/usr/bin/chromium', args: ['--no-sandbox'],
  });
  let admin, fixture;
  try {
    admin = await login(browser, 'admin', process.env.PISCO_ADMIN_PASSWORD || 'PruebaOriginal-2026!');
    fixture = await admin.page.evaluate(async () => {
      const a = DB.academico;
      const company = a.empresas.find(row => row.validada);
      const program = a.programas.find(row => row.activo);
      if (!company || !program) throw Error('Prepara las cuentas y catálogos con tests/parity.php.');
      const prefix = 'scoped-' + Date.now();
      // IDs de preparación independientes del generador bajo prueba.
      const start = Date.now() * 1000;
      const existingPostIds = a.postulaciones.map(row => row.id);
      const offer = start;
      a.ofertas.push({ id: offer, emp: company.id, puesto: prefix, desc: 'Oferta de regresión QA',
        req: 'Pruebas sintéticas', modal: 'Remoto', prog: program.id, vac: 4,
        limite: '2099-01-01', estado: 'Publicada', fecha: new Date().toISOString().slice(0, 10) });
      const students = [];
      for (let i = 1; i <= 4; i++) {
        const id = start + i, username = prefix + '-' + i;
        let dni;
        do { dni = String(80000000 + crypto.getRandomValues(new Uint32Array(1))[0] % 10000000); }
        while (a.estudiantes.some(row => row.dni === dni));
        const email = username + '@example.test';
        a.estudiantes.push({ id, dni, nom: username, prog: program.id, periodo: a.cfg.periodo,
          ciclo: 'I', sec: 'A', turno: 'Mañana', estado: 'Regular', correo: email, tel: '999000000' });
        const cv = await portalUpload(new File(['%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF'],
          username + '.pdf', { type: 'application/pdf' }));
        a.perfilesLab.push({ est: id, resumen: 'Perfil sintético QA', competencias: [], exp: [], cv });
        students.push({ id, username, email });
      }
      if (!saveDB()) throw Error('No se pudo guardar la preparación local.');
      return { offer, students, existingPostIds };
    });
    for (const student of fixture.students) {
      await admin.page.evaluate(({ student, password }) => portalRequest('POST', '/api/cuentas', {
        action: 'create', account: { username: student.username, name: student.username,
          email: student.email, password, role: 'estudiante', vin: 'est:' + student.id },
      }), { student, password });
    }

    const ids = [], importIds = [];
    for (const student of fixture.students) {
      const { context, page } = await login(browser, student.username, password);
      assert.equal(await page.evaluate(() => DB.academico.postulaciones.length), 0,
        'Cada alumno empieza sin ver postulaciones ajenas');
      const importId = await page.evaluate(() => IX.nextIdOf('postulaciones'));
      assert.ok(Number.isSafeInteger(importId) && importId > 0);
      importIds.push(importId);
      await page.goto(base + '/#/admin/ix-bolsa');
      await page.locator('[data-tab="bolsa:ofertas"]').click();
      await page.locator('[data-act=postular][data-id="' + fixture.offer + '"]').click();
      const saved = page.waitForResponse(response => response.url().includes('api%2Fportal') &&
        response.request().method() === 'PUT');
      await page.locator('#mform button[type=submit]').click();
      const response = await saved;
      assert.equal(response.status(), 200, 'La primera postulación debe guardarse sin colisionar');
      await page.waitForFunction(() => !document.querySelector('#mform'));
      await page.reload();
      await page.waitForFunction(() => typeof DB !== 'undefined' && DB);
      const posts = await page.evaluate(() => DB.academico.postulaciones);
      assert.equal(posts.length, 1);
      assert.equal(posts[0].est, student.id);
      assert.equal(posts[0].of, fixture.offer);
      assert.ok(Number.isSafeInteger(posts[0].id) && posts[0].id > 0);
      assert.ok(!fixture.existingPostIds.includes(posts[0].id));
      ids.push(posts[0].id);
      await context.close();
    }
    assert.equal(new Set(importIds).size, 4, 'El generador de importaciones no depende del máximo visible');
    assert.equal(new Set(ids).size, 4, 'Cuatro primeras postulaciones tienen identificadores distintos');
    await admin.page.reload();
    await admin.page.waitForFunction(() => typeof DB !== 'undefined' && DB);
    const finalIds = await admin.page.evaluate(() => DB.academico.postulaciones.map(row => row.id));
    assert.ok(fixture.existingPostIds.every(id => finalIds.includes(id)), 'Se conservan las postulaciones anteriores');
    console.log('OK cuatro primeras postulaciones, proyecciones disjuntas, IDs seguros y registros anteriores conservados.');
  } finally {
    try {
      if (admin && fixture) {
        await admin.page.reload();
        await admin.page.waitForFunction(() => typeof DB !== 'undefined' && DB);
        await admin.page.evaluate(fixture => {
          for (const student of fixture.students) {
            const account = DB.usuarios.find(row => row.usuario === student.username);
            if (account) portalRequest('POST', '/api/cuentas', { action: 'update', id: account.server_id,
              account: { username: student.username, name: student.username, email: student.email,
                role: 'imagen', estado: 'Inactivo' } });
          }
          const ids = new Set(fixture.students.map(row => row.id)), a = DB.academico;
          a.postulaciones = a.postulaciones.filter(row => !ids.has(row.est));
          a.perfilesLab = a.perfilesLab.filter(row => !ids.has(row.est));
          a.estudiantes = a.estudiantes.filter(row => !ids.has(row.id));
          a.ofertas = a.ofertas.filter(row => row.id !== fixture.offer);
          if (!saveDB()) throw Error('No se pudo limpiar la preparación de la prueba.');
        }, fixture);
      }
    } finally { await browser.close(); }
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
