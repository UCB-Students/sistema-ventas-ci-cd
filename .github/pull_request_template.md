## 🎯 ¿Qué cambia?
<!-- Marca el tipo de cambio -->
- [ ] `feat` - Nueva funcionalidad
- [ ] `fix` - Corrección de bug
- [ ] `refactor` - Refactorización de código
- [ ] `ci` - Cambios en CI/CD
- [ ] `docs` - Documentación
- [ ] `test` - Tests
- [ ] `style` - Formato, estilo

## 📋 Descripción
<!-- Describe brevemente los cambios realizados -->



## ✅ Evidencia

### Checklist Pre-Push
- [ ] **CI en verde** - Todos los checks pasaron
- [ ] **Tests ejecutados localmente** - `composer test` y `npm run test`
- [ ] **Lint ejecutado** - `composer lint` y `npm run lint`
- [ ] **Build frontend** - `npm run build` exitoso
- [ ] **Static analysis** - `composer static` sin errores

### Capturas
<!-- Si aplica, añade capturas de pantalla del CI en verde o de la funcionalidad -->

## 🔍 Testing

### Backend
```bash
# Comandos ejecutados localmente
composer install
composer lint
composer static
php artisan test
```

### Frontend
```bash
# Comandos ejecutados localmente
npm ci
npm run lint
npm run test
npm run build
```

## ⚠️ Riesgo / Rollback

### Nivel de Riesgo
- [ ] **Bajo** - Cambios menores, sin impacto en producción
- [ ] **Medio** - Cambios que requieren atención
- [ ] **Alto** - Cambios críticos, requiere plan de rollback

### Descripción del Riesgo
<!-- Describe los posibles riesgos o efectos secundarios -->



### Plan de Rollback
<!-- ¿Cómo revertir estos cambios si algo sale mal? -->



## 📎 Información Adicional
<!-- Cualquier contexto adicional, enlaces a issues, etc. -->



---

## 🎓 Checklist del Revisor
- [ ] El código sigue las convenciones del proyecto
- [ ] Los tests cubren los cambios realizados
- [ ] La documentación está actualizada (si aplica)
- [ ] No hay código comentado innecesario
- [ ] No hay console.logs o dd() olvidados
- [ ] El CI pasó exitosamente