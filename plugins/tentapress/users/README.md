# Users

User, role, and capability management for TentaPress.

## Plugin Details

| Field    | Value                                   |
| -------- | --------------------------------------- |
| ID       | `tentapress/users`                      |
| Version  | 0.1.14                                  |
| Provider | `TentaPress\Users\UsersServiceProvider` |

## Features

- User management (create, edit, delete)
- Role-based access control
- Capability/permission system
- Admin authentication
- Password reset

## Dependencies

None.

## Database

| Table                  | Purpose                     |
| ---------------------- | --------------------------- |
| `tp_users`             | User accounts               |
| `tp_roles`             | Role definitions            |
| `tp_capabilities`      | Capability definitions      |
| `tp_role_capabilities` | Role-capability assignments |

## Admin Menu

| Label | Route            | Capability     | Icon  | Position | Parent |
| ----- | ---------------- | -------------- | ----- | -------- | ------ |
| Users | `tp.users.index` | `manage_users` | users | 70       | -      |
| Roles | `tp.roles.index` | `manage_roles` | -     | 20       | Users  |

## Default Roles

- Admin - Full access
- Editor - Content management
- Author - Own content management
- Contributor - Limited content access

## Development

```bash
php artisan tp:plugins sync
php artisan tp:plugins enable tentapress/users

# Seed default permissions
php artisan tp:permissions seed

# Run plugin baseline auth/admin tests
composer test:filter -- AuthAdminAccessTest

# Run users permission/validation edge-case tests
composer test:filter -- UsersPermissionValidationTest
```
