**DOCUMENTO DE DECISIONES DE ARQUITECTURA**

Sistema de Gestión de Turnos — Barbería

*Versión 1.0 — Registro de decisiones técnicas (ADRs)*

# **1. Propósito de este documento**

Este documento registra las decisiones de arquitectura tomadas para el desarrollo del sistema, junto con el contexto que las motivó y sus consecuencias esperadas. Sigue un formato simplificado de ADR (Architecture Decision Record), una práctica estándar en desarrollo de software para dejar trazabilidad de por qué se construyó algo de una manera determinada — útil tanto para el equipo actual como para quien retome el proyecto en el futuro.

Las decisiones aquí registradas conviven con el Documento de Alcance del Sistema: mientras aquel define QUÉ hace el sistema (funcionalidades), este documento define CÓMO se construye técnicamente.

# **2. Resumen ejecutivo**

| **Código** | **Decisión** |
| --- | --- |
| **ADR-01** | Integración de recordatorios vía API de WhatsApp Business, a través de un proveedor intermediario (BSP). |
| **ADR-02** | Horario de atención fijo (hardcodeado) para la v1 del MVP; configurable en una iteración futura. |
| **ADR-03** | Arquitectura preparada para multi-tenancy ("multi-tenant-ready") desde el modelo de datos, sin construir el SaaS completo todavía. |

**ADR-01 Integración de WhatsApp vía WhatsApp Business API**

**Estado: Aceptada**

**Contexto**

El módulo de Turnos (TUR-07) requiere enviar recordatorios automáticos por WhatsApp antes del turno agendado. Matías confirmó que este es el canal elegido, priorizando que la implementación sea simple y de bajo costo operativo.

**Decisión**

Se integrará mediante la API oficial de WhatsApp Business. Dado que la integración directa con la plataforma de Meta (Cloud API) tiene una curva de configuración empresarial (verificación de negocio, número dedicado), se evaluará el uso de un proveedor intermediario (BSP – Business Solution Provider, ej. Twilio o 360dialog) que simplifica la puesta en marcha a cambio de una cuota mensual adicional. La decisión final de qué proveedor usar se toma en el sprint donde se implemente esta funcionalidad.

**Consecuencias**

* Existe un costo recurrente real: Meta cobra por conversación una vez superado el nivel gratuito mensual, y el BSP elegido puede sumar su propio costo mensual de plataforma.
* No es una integración de "una tarde": requiere verificación de negocio ante Meta, que puede tardar días.
* Se recomienda presupuestar este costo como gasto operativo mensual del negocio, no como costo único de desarrollo.
* Alternativa descartada por ahora: librerías no oficiales (ej. whatsapp-web.js) — más baratas pero no soportadas por Meta y con alto riesgo de bloqueo de número; no recomendable para un canal de negocio.

**ADR-02 Horario de atención fijo para la v1**

**Estado: Aceptada**

**Contexto**

El módulo de Turnos (TUR-08) necesita reflejar el horario real de atención (apertura 12:00/12:30hs, cierre 22:00hs). Surgió la pregunta de si el sistema debía permitir configurar este horario desde una pantalla, o si podía quedar fijo en esta primera versión.

**Decisión**

Para la v1 del MVP, el horario de atención queda fijo en la configuración del sistema (no editable desde una pantalla de administración). Se documenta como una funcionalidad candidata para una iteración futura, una vez validado el MVP con uso real.

**Consecuencias**

* Reduce el alcance y el tiempo de desarrollo del MVP sin afectar la operación actual de la barbería.
* Si el horario cambia en el corto plazo, el ajuste lo hace el equipo de desarrollo (no Matías desde una pantalla) — válido mientras el volumen de cambios sea bajo.
* Queda registrado como parte del backlog de mejoras post-MVP, no se pierde de vista.

**ADR-03 Arquitectura multi-tenant-ready (sin construir el SaaS completo)**

**Estado: Aceptada**

**Contexto**

Existe una visión de negocio a futuro (sin plazo definido aún) de convertir este sistema en un producto SaaS: que barberías de otros lugares puedan registrarse y usar el sistema, viendo únicamente sus propios datos (multi-tenancy). Esta intención es de carácter comercial, no solo especulativa. Sin embargo, el proyecto inmediato es un MVP de un solo uso (la barbería de Matías), con baja urgencia de entrega pero con la necesidad de no generar deuda técnica que obligue a reescribir el sistema si el SaaS se concreta más adelante.

**Decisión**

Se construye una única instancia del sistema (single-tenant en uso), pero con el modelo de datos y la capa de autenticación diseñados desde el inicio de forma "multi-tenant-ready": toda tabla cuyo contenido pertenece a una barbería específica (turnos, clientes, servicios, stock) incluye una relación a una entidad Barbería desde el primer día, aunque hoy exista un único registro en esa tabla. La autenticación y las políticas de autorización de Laravel ya consultan esa relación al validar el acceso a los datos, en lugar de asumir un único dueño global del sistema. No se construye en esta etapa: alta de nuevas barberías (onboarding), panel de super-administrador, planes o cobros a terceros, ni optimizaciones de infraestructura para múltiples clientes simultáneos.

**Consecuencias**

* Costo adicional hoy: mínimo. Es una decisión de modelado de datos y de cómo se escriben las consultas y políticas de autorización, no trabajo extra visible para Matías ni una funcionalidad nueva.
* Beneficio futuro: si el SaaS se concreta, el trabajo pendiente es "activar" funcionalidad nueva (registro de tenants, planes de pago, panel de administración) en lugar de migrar datos existentes o reescribir el modelo de autorización.
* Se recomienda mantener este enfoque como estándar de aquí en adelante para toda nueva funcionalidad del sistema: pensar el dato como perteneciente a "una barbería", no a "la barbería".
* Explícitamente fuera de alcance del MVP actual: aislamiento de infraestructura entre clientes, facturación, y cualquier pantalla de gestión de múltiples barberías. Se retomará como una iniciativa propia cuando exista intención concreta de lanzar el producto comercialmente.

# **3. Glosario rápido**

| **Término** | **Significado** |
| --- | --- |
| **SaaS** | Software as a Service: un mismo sistema alojado centralmente que múltiples clientes usan mediante suscripción, en lugar de instalarlo cada uno por separado. |
| **Multi-tenancy** | Arquitectura en la que un mismo sistema sirve a múltiples clientes ("tenants") manteniendo sus datos aislados entre sí. |
| **Multi-tenant-ready** | Sistema construido para un solo cliente hoy, pero cuyo modelo de datos y autorización ya contemplan la existencia de múltiples clientes a futuro, minimizando el costo de una futura migración. |
| **BSP (Business Solution Provider)** | Empresa intermediaria certificada por Meta que simplifica la integración con la API de WhatsApp Business a cambio de una cuota. |
| **ADR (Architecture Decision Record)** | Documento breve que registra una decisión de arquitectura, su contexto y sus consecuencias, para trazabilidad futura. |

# **4. Próximos pasos**

* Incorporar estas decisiones como criterios de aceptación técnicos en las Historias de Usuario correspondientes del Product Backlog.
* Al iniciar el desarrollo de TUR-07, cotizar al menos dos proveedores BSP de WhatsApp Business antes de definir cuál usar.
* Revisar este documento cada vez que se tome una nueva decisión de arquitectura relevante, sumando un nuevo ADR en lugar de modificar los existentes.