# QA de flujos completos: alumnos, docentes y empresas

Fecha: 29 de octubre de 2026. Base revisada: `943b33392071f9646958cf380fc385d6fe6ed121`.

## Hallazgo y corrección

La primera postulación del primer alumno funcionó. La del segundo alumno, que también tenía su CV y una oferta publicada, devolvió HTTP 403 con el mensaje «Identificador perteneciente a otro registro».

El generador académico calculaba el siguiente ID como el máximo de la colección visible más uno. Las cuentas de alumnos reciben únicamente sus propias postulaciones: una cuenta nueva veía una colección vacía y volvía a proponer el mismo ID que otra cuenta. El rechazo del servidor protegía el registro existente, pero impedía la postulación nueva.

La corrección genera enteros positivos seguros de 53 bits mediante `crypto.getRandomValues`, evita reutilizar IDs de la colección visible y conserva las comprobaciones de autorización y colisión del servidor. El generador de importaciones utiliza la misma función. No cambia los identificadores existentes ni requiere migraciones.

## Regresión reproducible local

Requisitos: PHP/MySQL configurados exclusivamente con una base `_test`, Chromium y Playwright. Desde la raíz del repositorio:

```sh
PORTAL_CONFIG_FILE=/ruta/privada/config.test.php php tests/parity.php
PORTAL_CONFIG_FILE=/ruta/privada/config.test.php php -S 127.0.0.1:8086 -t public_html
# En otra terminal:
node tests/browser-scoped-record-ids.cjs
```

La prueba prepara una oferta, cuatro alumnos con CV y sus cuentas. Cada alumno comienza sin postulaciones visibles y presenta su primera solicitud desde un contexto de navegador independiente. Se comprueba que:

- Las cuatro solicitudes se guardan y siguen perteneciendo a su alumno después de recargar.
- Sus identificadores son enteros seguros, positivos, distintos y no reutilizan los anteriores.
- El generador de importaciones tampoco depende del máximo visible.
- Las postulaciones anteriores permanecen en la colección administrativa.

Al terminar, se desactivan las cuentas sintéticas y se retiran los registros de preparación. Como las cuentas no tienen una operación de borrado, sus filas inactivas permanecen en la base local. Pueden quedar archivos de CV y entradas de auditoría de la prueba. El script rechaza destinos distintos de localhost o 127.0.0.1.

Resultado obtenido: cuatro primeras postulaciones aprobadas. También pasaron la validación de sintaxis PHP y `git diff --check`.

## Recorridos funcionales comprobados

Se ejercitaron cuatro casos con datos ficticios y sesiones independientes para cada perfil:

| Paso | Comprobación |
| --- | --- |
| Empresa | Registro mediante el formulario público e ingreso con su cuenta |
| Administración | Aprobación de la empresa y publicación de su oferta |
| Oferta | Visible en la bolsa pública, identificada como simulación sin vacantes reales |
| Alumno | Registro desde el maestro, creación de acceso y consulta de su horario |
| Horario | Seis clases nuevas por alumno, 24 en total, publicadas y exportadas a PDF |
| Perfil profesional | Guardado de datos y subida del CV desde la cuenta del alumno |
| Postulación | Envío, persistencia y consulta desde la cuenta del alumno |
| Empresa | Consulta del postulante, descarga autorizada del CV y cambio de estado |
| Alumno | Consulta de la decisión desde otra sesión: Seleccionado, No seleccionado o En revisión |
| Docente | Consulta de clases propias y actualización persistente de su perfil |
| Privacidad | CV rechazado con HTTP 403 para visitantes y para empresas sin vinculación con el postulante |

Estos recorridos no son una afirmación de despliegue. El primer caso se completó con el código publicado. Para completar las primeras postulaciones de los casos segundo, tercero y cuarto se cargó el parche candidato únicamente en sus navegadores de QA. La corrección no se instaló en el hosting.

Después se verificó, con navegadores sin parche, que los cuatro accesos demostrativos conservaban sus datos, horarios y decisiones, y que las cuatro ofertas estaban visibles. Esto acredita persistencia de los registros ya creados; no elimina la necesidad de actualizar el código para las primeras postulaciones de otros alumnos nuevos.

La autorización posterior limitó el trabajo a GitHub, rama `main`. Se detuvieron las escrituras en el sitio. No se incluyen contraseñas, sesiones ni datos de personas reales en este informe ni en los archivos de prueba del repositorio.
