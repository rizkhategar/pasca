# Project Naming Convention

Use English for all new project folders, view files, controllers, resources, services, routes, and documentation.

## File and folder rules

- View folders: lowercase English, for example `resources/views/profile`.
- Blade files: lowercase English with kebab-case, for example `vision-mission.blade.php`.
- PHP classes: PascalCase English, for example `VisionMissionResource`.
- PHP methods and variables: camelCase English, for example `resolvedWhatsAppAdmins`.
- Route names: lowercase English with dot notation, for example `profile.vision-mission`.
- Database columns and tables: preserve existing schema names unless a dedicated migration and compatibility plan is prepared.

## Migration policy

When renaming an existing file or namespace:

1. Create the English destination path.
2. Update all references.
3. Verify the feature works.
4. Remove the former file only after the destination contains the complete original implementation.
5. Run `composer dump-autoload` and `php artisan optimize:clear`.

This policy avoids duplicate templates and prevents broken routes or Filament resources.
