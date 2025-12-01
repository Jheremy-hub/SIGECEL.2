# SIGECEL
**Proyecto de Gestión de Trámite Documentario**

## Descripción del Proyecto

SIGECEL es un sistema de gestión de trámite documentario desarrollado con Laravel, diseñado para el Colegio de Economistas de Lima. El sistema permite gestionar documentos, mensajes internos, usuarios y roles.

## Características Principales

- 📄 **Gestión de Documentos**: Creación y administración de oficios, cartas y memorandums
- 💬 **Sistema de Mensajería**: Mensajería interna con sistema de aprobación jerárquica
- 👥 **Gestión de Usuarios y Roles**: Control de acceso basado en roles
- 🎂 **Saludos de Cumpleaños**: Generación automática de tarjetas de felicitación
- 📊 **Reportes y Seguimiento**: Tracking de documentos y mensajes

## Requisitos Técnicos

- PHP >= 8.2
- Laravel 11.x
- Composer
- Node.js & NPM
- Base de datos compatible con Laravel (MySQL, PostgreSQL, etc.)

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Jheremy-hub/SIGECEL.git
cd SIGECEL
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node:
```bash
npm install
```

4. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

5. Generar la clave de la aplicación:
```bash
php artisan key:generate
```

6. Configurar la base de datos en `.env` y ejecutar migraciones:
```bash
php artisan migrate
```

7. Compilar assets:
```bash
npm run dev
```

## Tecnologías Utilizadas

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

Este proyecto está construido con **Laravel**, un framework de PHP con sintaxis expresiva y elegante.

## Licencia

Este proyecto es software de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT).
