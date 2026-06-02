# Backend - Restaurante XYZ
Sistema de gestión de restaurante basado en microservicios.

## Microservicios

| Microservicio     | Puerto | Base de datos          |
|-------------------|--------|------------------------|
| ms-autenticacion  | 8001   | db_ms_autenticacion    |
| ms-reservas       | 8002   | db_ms_reservas         |
| ms-productos      | 8003   | db_ms_productos        |
| ms-pedidos        | 8004   | db_ms_pedidos          |

## Requisitos
- PHP 8.0+
- Composer
- MySQL (XAMPP)

## Instalación (por cada microservicio)

```bash
# 1. Entrar a la carpeta del microservicio
cd ms-autenticacion

# 2. Copiar el archivo de variables
cp .env.example .env

# 3. Instalar dependencias
composer install

# 4. Levantar el servidor
php -S localhost:8001 -t public
```

## Base de datos
Importar el archivo `database.sql` desde phpMyAdmin antes de levantar los servicios.

## Tecnologías
- PHP 8.0
- Slim Framework 4
- Eloquent ORM
- MySQL