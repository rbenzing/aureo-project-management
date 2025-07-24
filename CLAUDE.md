# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Building and Development
- `npm run build` - Compile TailwindCSS from `src/css/input.css` to `public/assets/css/styles.css`
- `composer run start` - Start PHP development server on localhost:8000 (document root: public/)
- `composer install` - Install PHP dependencies via Composer

### Database Management
- Import schema: `mysql -u root -p slimbooks_db < schema.sql`
- Import sample data: `mysql -u root -p slimbooks_db < sample-data.sql`
- Run migrations: Execute files in `database/migrations/` directory manually

## Architecture Overview

### MVC Structure
SlimBooks uses a custom PHP MVC framework with the following key components:

**Core Framework (`src/Core/`)**:
- `Config.php` - Environment-based configuration management using vlucas/phpdotenv
- `Database.php` - PDO wrapper with prepared statements, query logging, and connection management
- `Router.php` - Custom URL routing system with parameter extraction and middleware support
- `Response.php` - HTTP response handling and JSON/redirect utilities

**Request Flow**:
1. `public/index.php` - Application entry point, loads routes and middleware
2. Middleware stack executes (Session → Auth → CSRF → Activity logging)
3. Router dispatches to appropriate Controller
4. Controller coordinates with Models and renders Views

### Data Layer (`src/models/`)
All models extend `BaseModel.php` which provides:
- Database connection management
- Common CRUD operations with prepared statements
- Logging and error handling
- Input validation and sanitization

Key models: `User.php`, `Project.php`, `Task.php`, `Sprint.php`, `Company.php`, `Role.php`

### Security Architecture
**Middleware Stack (`src/middleware/`)**:
- `SessionMiddleware.php` - Session management and security
- `AuthMiddleware.php` - Authentication and role-based authorization
- `CsrfMiddleware.php` - CSRF token validation
- `ActivityMiddleware.php` - User activity logging and audit trails

**Security Services (`src/services/`)**:
- `SecurityService.php` - Centralized security features (rate limiting, headers, validation)
- Password hashing uses Argon2
- All database queries use prepared statements

### Database Schema
Multi-tenant architecture with key tables:
- **Core**: users, companies, roles, permissions, role_permissions
- **Projects**: projects, tasks, milestones, sprints, templates
- **Tracking**: time_entries, task_comments, activity_logs
- **System**: settings, sessions, csrf_tokens

Tasks support hierarchical structure (subtasks) and sprint assignment.

### Frontend Architecture
- **TailwindCSS 3.4** with PostCSS processing
- Views in `src/views/` organized by feature
- Shared layouts in `src/views/layouts/`
- JavaScript utilities in `public/assets/js/`

### Configuration
- Environment variables in `.env` (copy from `.env.example`)
- Database, email, and app settings configured via environment
- Settings table provides runtime configuration management
- Default admin user: `rbenzing@gmail.com` / `password`

### Key Business Logic
- **Sprint Management**: Full Scrum workflow with backlog, planning, and tracking
- **Time Tracking**: Task-based time entries with billable hours
- **Role-Based Access**: Granular permissions system
- **Template System**: Reusable templates for projects, tasks, and sprints
- **Activity Logging**: Comprehensive audit trail for all user actions

### Development Notes
- PSR-4 autoloading with `App\` namespace mapping to `src/`
- No unit testing framework currently configured
- Custom routing in `public/index.php` - add new routes there
- All user input goes through validation in models/controllers
- Email functionality via PHPMailer (configured in SecurityService)